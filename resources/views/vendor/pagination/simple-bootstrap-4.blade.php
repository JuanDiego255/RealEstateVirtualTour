@if ($paginator->hasPages())
<nav>
    <ul class="pagination pagination-sm mb-0">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo; Anterior</span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Anterior</a></li>
        @endif

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente &raquo;</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">Siguiente &raquo;</span></li>
        @endif
    </ul>
</nav>
@endif
