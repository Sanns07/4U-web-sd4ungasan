@if ($paginator->hasPages())
    <nav class="mt-5" aria-label="{{ $label }}">
        <ul class="pagination justify-content-center flex-wrap">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link" aria-disabled="true">Sebelumnya</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a></li>
            @endif

            @php
                $firstPage = max(1, $paginator->currentPage() - 2);
                $lastPage = min($paginator->lastPage(), $paginator->currentPage() + 2);
            @endphp
            @for ($page = $firstPage; $page <= $lastPage; $page++)
                @if ($page === $paginator->currentPage())
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a></li>
            @else
                <li class="page-item disabled"><span class="page-link" aria-disabled="true">Berikutnya</span></li>
            @endif
        </ul>
    </nav>
@endif
