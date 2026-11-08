@props(['paginator'])

@if($paginator->hasPages())
    <nav aria-label="Pagination" class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm tabular-nums text-text-muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>
        <div class="flex flex-wrap gap-2">
            @if($paginator->previousPageUrl())
                <a class="btn-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="arrow-left" class="size-4" />Previous</a>
            @endif
            @if($paginator->nextPageUrl())
                <a class="btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Next<x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>
    </nav>
@endif
