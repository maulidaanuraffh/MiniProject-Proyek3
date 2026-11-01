@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="pager-btn disabled">Sebelumnya</span>
        @else
            <a class="pager-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="pager-btn active" aria-current="page">{{ $page }}</span>
            @else
                <a class="pager-btn" href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pager-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="pager-btn disabled">Berikutnya</span>
        @endif
    </nav>

    <p class="pager-info">
        Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }} laporan
    </p>
@endif
