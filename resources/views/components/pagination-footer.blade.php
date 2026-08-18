@props(['paginator'])

<div class="reference-list-footer pagination-footer" aria-label="Table pagination">
    <div class="pagination-navigation">
        @if ($paginator->hasPages())
            {{ $paginator->onEachSide(1)->links() }}
        @endif
    </div>
    <div class="pagination-controls">
        <span class="pagination-summary">Showing {{ number_format($paginator->firstItem() ?? 0) }}-{{ number_format($paginator->lastItem() ?? 0) }} of {{ number_format($paginator->total()) }} &middot; Page {{ number_format($paginator->currentPage()) }} of {{ number_format($paginator->lastPage()) }}</span>
        <form class="pagination-size-form" method="GET">
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <label class="visually-hidden" for="per-page-{{ spl_object_id($paginator) }}">Rows per page</label>
            <select id="per-page-{{ spl_object_id($paginator) }}" class="form-select form-select-sm" name="per_page" onchange="this.form.submit()" aria-label="Rows per page">
                @foreach([10, 20, 30, 50, 100] as $size)
                    <option value="{{ $size }}" @selected((int) request('per_page', $paginator->perPage()) === $size)>{{ $size }}</option>
                @endforeach
            </select>
            <span>rows</span>
        </form>
    </div>
</div>
