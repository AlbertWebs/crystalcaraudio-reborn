<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBusinessIntelligenceController extends Controller
{
    /** @return array{period:string,from:string,to:string,category:string,brand:string,payment_method:string,order_status:string} */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:7d,30d,this_month,last_month,this_year,custom'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'category' => ['nullable', 'string', 'max:190'],
            'brand' => ['nullable', 'string', 'max:190'],
            'payment_method' => ['nullable', 'string', 'max:80'],
            'order_status' => ['nullable', 'string', 'max:80'],
        ]);

        $period = $validated['period'] ?? '30d';
        $today = CarbonImmutable::today();
        [$from, $to] = match ($period) {
            '7d' => [$today->subDays(6), $today],
            'this_month' => [$today->startOfMonth(), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->startOfMonth()->subDay()],
            'this_year' => [$today->startOfYear(), $today],
            'custom' => [CarbonImmutable::parse($validated['from'] ?? $today->subDays(29)->toDateString()), CarbonImmutable::parse($validated['to'] ?? $today->toDateString())],
            default => [$today->subDays(29), $today],
        };

        return [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'category' => $validated['category'] ?? '',
            'brand' => $validated['brand'] ?? '',
            'payment_method' => $validated['payment_method'] ?? '',
            'order_status' => $validated['order_status'] ?? '',
        ];
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->buildReport($filters);
        $categories = DB::table('catalog_categories')->orderBy('name')->pluck('name');
        $brands = DB::table('catalog_products')->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');
        $paymentMethods = DB::table('store_orders')->whereNotNull('payment_method')->distinct()->orderBy('payment_method')->pluck('payment_method');

        if ($request->query('export') === 'csv') {
            return $this->downloadCsv($report, $filters);
        }

        return view('admin.bi', compact('filters', 'report', 'categories', 'brands', 'paymentMethods'));
    }

    /** @param array{period:string,from:string,to:string,category:string,brand:string,payment_method:string,order_status:string} $filters
     * @return array<string, mixed>
     */
    private function buildReport(array $filters): array
    {
        $from = CarbonImmutable::parse($filters['from'])->startOfDay();
        $to = CarbonImmutable::parse($filters['to'])->endOfDay();
        $orderQuery = DB::table('store_orders')
            ->select(['id', 'created_at', 'total', 'discount', 'payment_status', 'order_status', 'payment_method', 'email', 'items'])
            ->whereBetween('created_at', [$from, $to])
            ->when($filters['payment_method'] !== '', fn ($query) => $query->where('payment_method', $filters['payment_method']))
            ->when($filters['order_status'] !== '', fn ($query) => $query->where('order_status', $filters['order_status']))
            ->orderBy('id');

        $metrics = [
            'gross_sales' => 0.0, 'net_sales' => 0.0, 'discounts' => 0.0, 'refunds' => 0.0, 'cost_of_goods' => 0.0,
            'orders' => 0, 'paid_orders' => 0, 'pending_orders' => 0, 'completed_orders' => 0,
            'refunded_orders' => 0, 'customers' => [], 'customer_order_counts' => [],
            'payment_methods' => [], 'revenue_by_day' => [], 'product_sales' => [],
            'category_sales' => [], 'brand_sales' => [], 'categorized_lines' => 0, 'all_lines' => 0,
            'paid_lines' => 0, 'costed_paid_lines' => 0,
        ];

        $orderQuery->chunkById(400, function ($orders) use (&$metrics, $filters): void {
            foreach ($orders as $order) {
                $items = json_decode((string) $order->items, true);
                $items = is_array($items) ? $items : [];
                $matchingItems = [];
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $metrics['all_lines']++;
                    $category = (string) ($item['category'] ?? '');
                    $brand = (string) ($item['brand'] ?? '');
                    if ($category !== '') {
                        $metrics['categorized_lines']++;
                    }
                    if (($filters['category'] !== '' && $category !== $filters['category'])
                        || ($filters['brand'] !== '' && $brand !== $filters['brand'])) {
                        continue;
                    }
                    $matchingItems[] = $item;
                }

                if (($filters['category'] !== '' || $filters['brand'] !== '') && $matchingItems === []) {
                    continue;
                }

                $metrics['orders']++;
                if ($order->order_status === 'pending') {
                    $metrics['pending_orders']++;
                }
                if ($order->order_status === 'completed') {
                    $metrics['completed_orders']++;
                }
                if ($order->payment_status === 'refunded') {
                    $metrics['refunded_orders']++;
                    $metrics['refunds'] += (float) $order->total;
                }

                if (! in_array($order->order_status, ['cancelled', 'refunded'], true)
                    && in_array($order->payment_status, ['paid', 'escrow_held'], true)) {
                    $metrics['paid_orders']++;
                    $metrics['customers'][$order->email] = true;
                    $metrics['customer_order_counts'][$order->email] = ($metrics['customer_order_counts'][$order->email] ?? 0) + 1;
                    $metrics['payment_methods'][$order->payment_method] = ($metrics['payment_methods'][$order->payment_method] ?? 0) + 1;

                    $itemsSubtotal = array_sum(array_map(fn ($item) => (float) ($item['price'] ?? 0) * max(0, (int) ($item['quantity'] ?? 0)), $items));
                    $orderDiscount = (float) $order->discount;
                    $matchedGross = array_sum(array_map(fn ($item) => (float) ($item['price'] ?? 0) * max(0, (int) ($item['quantity'] ?? 0)), $matchingItems));
                    $matchedDiscount = $itemsSubtotal > 0 ? min($orderDiscount * ($matchedGross / $itemsSubtotal), $matchedGross) : 0;
                    $matchedNet = max(0, $matchedGross - $matchedDiscount);
                    $gross = ($filters['category'] === '' && $filters['brand'] === '') ? (float) $order->total + $orderDiscount : $matchedGross;
                    $net = ($filters['category'] === '' && $filters['brand'] === '') ? (float) $order->total : $matchedNet;
                    $metrics['gross_sales'] += $gross;
                    $metrics['net_sales'] += $net;
                    $metrics['discounts'] += ($filters['category'] === '' && $filters['brand'] === '') ? $orderDiscount : $matchedDiscount;

                    $day = CarbonImmutable::parse($order->created_at)->toDateString();
                    $metrics['revenue_by_day'][$day] = ($metrics['revenue_by_day'][$day] ?? 0) + $net;
                    foreach ($matchingItems as $item) {
                        $quantity = max(0, (int) ($item['quantity'] ?? 0));
                        $lineGross = (float) ($item['price'] ?? 0) * $quantity;
                        $lineNet = max(0, $lineGross - ($itemsSubtotal > 0 ? min($orderDiscount * ($lineGross / $itemsSubtotal), $lineGross) : 0));
                        $name = (string) ($item['name'] ?? 'Unlabelled product');
                        $metrics['product_sales'][$name]['quantity'] = ($metrics['product_sales'][$name]['quantity'] ?? 0) + $quantity;
                        $metrics['product_sales'][$name]['revenue'] = ($metrics['product_sales'][$name]['revenue'] ?? 0) + $lineNet;
                        $category = (string) ($item['category'] ?? '');
                        $brand = (string) ($item['brand'] ?? '');
                        if ($category !== '') {
                            $metrics['category_sales'][$category] = ($metrics['category_sales'][$category] ?? 0) + $lineNet;
                        }
                        if ($brand !== '') {
                            $metrics['brand_sales'][$brand] = ($metrics['brand_sales'][$brand] ?? 0) + $lineNet;
                        }
                        $metrics['paid_lines']++;
                        if (array_key_exists('unit_cost', $item) && $item['unit_cost'] !== null) {
                            $metrics['costed_paid_lines']++;
                            $metrics['cost_of_goods'] += (float) $item['unit_cost'] * $quantity;
                        }
                    }
                }
            }
        });

        $activeCustomerCount = count($metrics['customer_order_counts']);
        $repeatCustomerCount = count(array_filter($metrics['customer_order_counts'], fn ($count) => $count > 1));
        $start = CarbonImmutable::parse($filters['from']);
        $end = CarbonImmutable::parse($filters['to']);
        $days = max(1, $start->diffInDays($end) + 1);
        $granularity = $days <= 45 ? 'day' : 'month';
        $series = [];
        $cursor = $granularity === 'day' ? $start : $start->startOfMonth();
        $last = $granularity === 'day' ? $end : $end->startOfMonth();
        while ($cursor <= $last) {
            $key = $cursor->toDateString();
            if ($granularity === 'day') {
                $series[] = ['label' => $cursor->format('M j'), 'value' => round($metrics['revenue_by_day'][$key] ?? 0, 2)];
                $cursor = $cursor->addDay();
            } else {
                $monthRevenue = array_sum(array_filter($metrics['revenue_by_day'], fn ($amount, $date) => str_starts_with($date, $cursor->format('Y-m')), ARRAY_FILTER_USE_BOTH));
                $series[] = ['label' => $cursor->format('M Y'), 'value' => round($monthRevenue, 2)];
                $cursor = $cursor->addMonth();
            }
        }

        arsort($metrics['category_sales']);
        arsort($metrics['brand_sales']);
        uasort($metrics['product_sales'], fn ($left, $right) => $right['revenue'] <=> $left['revenue']);
        $topProducts = array_slice($metrics['product_sales'], 0, 8, true);

        return [
            'gross_sales' => $metrics['gross_sales'],
            'net_sales' => $metrics['net_sales'],
            'discounts' => $metrics['discounts'],
            'refunds' => $metrics['refunds'],
            'orders' => $metrics['orders'],
            'paid_orders' => $metrics['paid_orders'],
            'pending_orders' => $metrics['pending_orders'],
            'completed_orders' => $metrics['completed_orders'],
            'refunded_orders' => $metrics['refunded_orders'],
            'average_order_value' => $metrics['paid_orders'] > 0 ? $metrics['net_sales'] / $metrics['paid_orders'] : 0,
            'repeat_customer_rate' => $activeCustomerCount > 0 ? ($repeatCustomerCount / $activeCustomerCount) * 100 : null,
            'distinct_customers' => $activeCustomerCount,
            'payment_methods' => $metrics['payment_methods'],
            'series' => $series,
            'top_products' => $topProducts,
            'category_sales' => $metrics['category_sales'],
            'brand_sales' => $metrics['brand_sales'],
            'attribution_coverage' => $metrics['all_lines'] > 0 ? ($metrics['categorized_lines'] / $metrics['all_lines']) * 100 : null,
            'profit_available' => $metrics['paid_lines'] > 0 && $metrics['paid_lines'] === $metrics['costed_paid_lines'],
            'cost_coverage' => $metrics['paid_lines'] > 0 ? ($metrics['costed_paid_lines'] / $metrics['paid_lines']) * 100 : null,
            'cost_of_goods' => $metrics['cost_of_goods'],
            'gross_profit' => $metrics['net_sales'] - $metrics['cost_of_goods'],
            'gross_margin' => $metrics['net_sales'] > 0 && $metrics['paid_lines'] === $metrics['costed_paid_lines'] ? (($metrics['net_sales'] - $metrics['cost_of_goods']) / $metrics['net_sales']) * 100 : null,
            'previous_period' => $this->previousPeriod($filters, $days),
        ];
    }

    /** @param array{period:string,from:string,to:string,category:string,brand:string,payment_method:string,order_status:string} $filters
     * @return array{from:string,to:string,gross_sales:float,net_sales:float}
     */
    private function previousPeriod(array $filters, int $days): array
    {
        $to = CarbonImmutable::parse($filters['from'])->subDay();
        $from = $to->subDays($days - 1);
        $query = DB::table('store_orders')->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->whereIn('payment_status', ['paid', 'escrow_held'])->whereNotIn('order_status', ['cancelled', 'refunded']);
        if ($filters['payment_method'] !== '') {
            $query->where('payment_method', $filters['payment_method']);
        }
        if ($filters['order_status'] !== '') {
            $query->where('order_status', $filters['order_status']);
        }
        if ($filters['category'] !== '' || $filters['brand'] !== '') {
            return ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'gross_sales' => 0.0, 'net_sales' => 0.0];
        }

        $row = $query->selectRaw('COALESCE(SUM(total + discount), 0) as gross_sales, COALESCE(SUM(total), 0) as net_sales')->first();

        return ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'gross_sales' => (float) $row->gross_sales, 'net_sales' => (float) $row->net_sales];
    }

    /** @param array<string, mixed> $report
     * @param  array{period:string,from:string,to:string,category:string,brand:string,payment_method:string,order_status:string}  $filters
     */
    private function downloadCsv(array $report, array $filters): StreamedResponse
    {
        $filename = 'crystal-sales-'.$filters['from'].'-to-'.$filters['to'].'.csv';

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Metric', 'Value']);
            foreach ([
                'Gross sales (KES)' => $report['gross_sales'], 'Net sales (KES)' => $report['net_sales'],
                'Discounts (KES)' => $report['discounts'], 'Refunds (KES)' => $report['refunds'],
                'Orders' => $report['orders'], 'Paid orders' => $report['paid_orders'],
                'Average order value (KES)' => $report['average_order_value'],
                'Repeat customer rate (%)' => $report['repeat_customer_rate'] ?? 'Unavailable',
            ] as $metric => $value) {
                fputcsv($output, [$metric, $value]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Sales by period', 'Net revenue (KES)']);
            foreach ($report['series'] as $point) {
                fputcsv($output, [$point['label'], $point['value']]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
