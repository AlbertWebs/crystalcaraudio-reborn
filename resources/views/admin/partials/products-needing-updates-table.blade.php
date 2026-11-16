@if($excludedProducts->count() > 0)
    @if($excludedProducts->hasPages())
        @include('admin.partials.catalogue-pagination', ['paginator' => $excludedProducts, 'ariaLabel' => 'Products needing updates pages', 'summaryNoun' => 'excluded products'])
    @endif

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th scope="col">Product</th><th scope="col">Current feed details</th><th scope="col">Why it is excluded</th><th scope="col">Action</th></tr>
            </thead>
            <tbody>
                @foreach($excludedProducts as $item)
                    <tr>
                        <td data-label="Product"><strong>{{ $item['product']->name }}</strong><small>Product ID {{ $item['product']->id }}</small></td>
                        <td data-label="Current feed details"><strong>{{ $item['product']->brand ?: 'No brand' }}</strong><small>Product code: {{ $item['product']->sku ?: 'Missing' }}</small><small>Category: {{ $item['product']->category ?: 'Missing' }}</small><small>Price: KES {{ number_format((float) $item['product']->price, 2) }}</small></td>
                        <td data-label="Why it is excluded"><ul class="admin-export-reasons">@foreach($item['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></td>
                        <td data-label="Action"><a class="admin-export-edit-link" href="{{ route('admin.products.edit', $item['product']) }}">Update product <span aria-hidden="true">→</span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($excludedProducts->hasPages())
        @include('admin.partials.catalogue-pagination', ['paginator' => $excludedProducts, 'ariaLabel' => 'Products needing updates pages', 'summaryNoun' => 'excluded products'])
    @endif
@else
    <p class="admin-export-issues-empty">All products currently pass the Merchant feed data checks.</p>
@endif
