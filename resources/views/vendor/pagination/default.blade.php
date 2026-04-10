@if ($paginator->hasPages())
<nav class="pagination-nav" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="page-btn page-btn--disabled">&laquo; Prev</span>
    @else
        <a class="page-btn" href="{{ $paginator->previousPageUrl() }}">&laquo; Prev</a>
    @endif

    {{-- Page Numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-btn page-btn--dots">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-btn page-btn--active">{{ $page }}</span>
                @else
                    <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a class="page-btn" href="{{ $paginator->nextPageUrl() }}">Next &raquo;</a>
    @else
        <span class="page-btn page-btn--disabled">Next &raquo;</span>
    @endif
</nav>
@endif
