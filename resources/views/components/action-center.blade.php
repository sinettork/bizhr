@props([
    'items' => [],
    'title' => 'Action center',
    'description' => 'Work that needs attention next.',
    'emptyTitle' => 'Nothing needs your attention',
    'emptyMessage' => 'There are no pending actions in this workspace.',
])

<section class="reference-list h-100" aria-labelledby="action-center-title">
    <div class="reference-list-toolbar">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">Next actions</div>
            <div class="fw-semibold text-dark" id="action-center-title">{{ $title }}</div>
            <div class="small text-body-secondary">{{ $description }}</div>
        </div>
        <span class="badge rounded-pill text-bg-primary">{{ collect($items)->sum('count') }}</span>
    </div>

    @forelse($items as $item)
        @php($href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : null)
        @if($href)
            <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ $href }}">
        @else
            <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
        @endif
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:36px;height:36px">
                    <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                </span>
                <span class="flex-grow-1 min-w-0">
                    <span class="d-block fw-semibold text-dark">{{ $item['label'] }}</span>
                    <span class="small text-body-secondary">{{ $item['count'] }} pending</span>
                </span>
                @if($href)
                    <span class="btn btn-sm btn-outline-primary">Open</span>
                @endif
        @if($href)
            </a>
        @else
            </div>
        @endif
    @empty
        <x-empty-state class="py-5 px-3" icon="fa-check-double" tone="success" :title="$emptyTitle" :message="$emptyMessage" />
    @endforelse
</section>
