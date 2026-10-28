@if ($paginator->hasPages())
    <div class="adm-pager">
        @if ($paginator->onFirstPage())
            <span class="adm-page is-disabled"><i class="ri-arrow-left-s-line"></i></span>
        @else
            <a class="adm-page" href="{{ $paginator->previousPageUrl() }}"><i class="ri-arrow-left-s-line"></i></a>
        @endif
        <span class="adm-page-info">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="adm-page" href="{{ $paginator->nextPageUrl() }}"><i class="ri-arrow-right-s-line"></i></a>
        @else
            <span class="adm-page is-disabled"><i class="ri-arrow-right-s-line"></i></span>
        @endif
    </div>
@endif
