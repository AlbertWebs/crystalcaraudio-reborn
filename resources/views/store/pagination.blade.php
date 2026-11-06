@if ($paginator->hasPages())
    <nav class="catalog-pagination" role="navigation" aria-label="Product results pages">
        <p class="catalog-pagination-summary">Showing <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong> of <strong>{{ $paginator->total() }}</strong> products</p>
        <div class="catalog-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="catalog-page-step is-disabled" aria-disabled="true" aria-label="Previous page"><span aria-hidden="true">←</span><span class="catalog-page-step-label">Previous</span></span>
            @else
                <a class="catalog-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><span aria-hidden="true">←</span><span class="catalog-page-step-label">Previous</span></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="catalog-page-ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="catalog-page-number is-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="catalog-page-number" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="catalog-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><span class="catalog-page-step-label">Next</span><span aria-hidden="true">→</span></a>
            @else
                <span class="catalog-page-step is-disabled" aria-disabled="true" aria-label="Next page"><span class="catalog-page-step-label">Next</span><span aria-hidden="true">→</span></span>
            @endif
        </div>
    </nav>
@endif
