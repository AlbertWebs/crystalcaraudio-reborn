<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminInstallationBookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $bookings = DB::table('installation_bookings')
            ->when(in_array($status, ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.installations', [
            'bookings' => $bookings,
            'selectedStatus' => $status,
            'pendingCount' => DB::table('installation_bookings')->where('status', 'pending')->count(),
            'confirmedCount' => DB::table('installation_bookings')->where('status', 'confirmed')->count(),
            'completedCount' => DB::table('installation_bookings')->where('status', 'completed')->count(),
            'scheduledRevenue' => (float) DB::table('installation_bookings')->whereIn('status', ['confirmed', 'in_progress', 'completed'])->sum('service_charge'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'vehicle_make' => ['required', 'string', 'max:100'],
            'vehicle_model' => ['required', 'string', 'max:120'],
            'vehicle_year' => ['nullable', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'service' => ['required', 'string', 'max:190'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'location' => ['nullable', 'string', 'max:190'],
            'technician' => ['nullable', 'string', 'max:190'],
            'service_charge' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->ensureTechnicianSlotIsAvailable($data['scheduled_at'] ?? null, $data['technician'] ?? null);
        $id = DB::table('installation_bookings')->insertGetId($data + [
            'created_by' => $request->user()->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->recordActivity($request, 'installation.created', (string) $id, 'Created an installation booking for '.$data['customer_name'].'.');

        return back()->with('success', 'Installation booking created.');
    }

    public function update(Request $request, int $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,in_progress,completed,cancelled'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'technician' => ['nullable', 'string', 'max:190'],
            'service_charge' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $existing = DB::table('installation_bookings')->where('id', $booking)->first();
        abort_if($existing === null, 404);
        $this->ensureTechnicianSlotIsAvailable($data['scheduled_at'] ?? null, $data['technician'] ?? null, $booking);
        DB::table('installation_bookings')->where('id', $booking)->update($data + ['updated_at' => now()]);
        $this->recordActivity($request, 'installation.updated', (string) $booking, 'Updated installation booking for '.$existing->customer_name.' to '.$data['status'].'.');

        return back()->with('success', 'Installation booking updated.');
    }

    private function ensureTechnicianSlotIsAvailable(?string $scheduledAt, ?string $technician, ?int $exceptId = null): void
    {
        if ($scheduledAt === null || trim((string) $technician) === '') {
            return;
        }

        $start = now()->parse($scheduledAt);
        $conflict = DB::table('installation_bookings')
            ->where('technician', trim($technician))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereBetween('scheduled_at', [$start->copy()->subHour(), $start->copy()->addHour()])
            ->when($exceptId !== null, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($conflict) {
            abort(422, 'This technician already has a booking within one hour of the selected time.');
        }
    }

    private function recordActivity(Request $request, string $action, string $subjectId, string $summary): void
    {
        DB::table('admin_activity_logs')->insert([
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => 'installation_booking',
            'subject_id' => $subjectId,
            'summary' => $summary,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
