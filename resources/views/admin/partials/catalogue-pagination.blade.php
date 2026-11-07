@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $startPage = max(1, $currentPage - 1);
    $endPage = min($lastPage, $currentPage + 1);
@endphp
<div class="catalogue-pagination" role="navigation" aria-label="Product catalogue pages">
    <p class="catalogue-pagination-summary">Showing <strong>{{ number_format($paginator->firstItem() ?? 0) }}–{{ number_format($paginator->lastItem() ?? 0) }}</strong> of <strong>{{ number_format($paginator->total()) }}</strong> products <span>· Page {{ number_format($currentPage) }} of {{ number_format($lastPage) }}</span></p>
    <div class="catalogue-pagination-controls">
        @if($paginator->onFirstPage())<span class="catalogue-page-step is-disabled" aria-disabled="true"><span aria-hidden="true">←</span><span>Previous</span></span>@else<a class="catalogue-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev"><span aria-hidden="true">←</span><span>Previous</span></a>@endif
        <div class="catalogue-page-numbers" aria-label="Choose a page">
            @if($startPage > 1)<a class="catalogue-page-number" href="{{ $paginator->url(1) }}" aria-label="Page 1">1</a>@if($startPage > 2)<span class="catalogue-page-ellipsis" aria-hidden="true">…</span>@endif @endif
            @for($page = $startPage; $page <= $endPage; $page++)
                @if($page === $currentPage)<span class="catalogue-page-number is-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>@else<a class="catalogue-page-number" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>@endif
            @endfor
            @if($endPage < $lastPage)@if($endPage < $lastPage - 1)<span class="catalogue-page-ellipsis" aria-hidden="true">…</span>@endif<a class="catalogue-page-number" href="{{ $paginator->url($lastPage) }}" aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>@endif
        </div>
        @if($paginator->hasMorePages())<a class="catalogue-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next"><span>Next</span><span aria-hidden="true">→</span></a>@else<span class="catalogue-page-step is-disabled" aria-disabled="true"><span>Next</span><span aria-hidden="true">→</span></span>@endif
    </div>
</div>
