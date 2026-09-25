<div class="reference-list" data-list-container id="list-content">
    {{-- Branch Quick-Filter Chips Bar --}}
    @if(isset($branches) && $branches->isNotEmpty())
        <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-3 flex-wrap" style="background: #fafbfe;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="small fw-semibold text-body-secondary"><i class="fa-solid fa-building me-1"></i>Branch:</span>
                <a 
                    href="{{ route('employees.index', array_merge(request()->query(), ['branch' => null])) }}"
                    hx-get="{{ route('employees.index', array_merge(request()->query(), ['branch' => null])) }}"
                    hx-target="#list-content" 
                    hx-swap="outerHTML" 
                    hx-push-url="true"
                    class="dept-chip {{ !request()->filled('branch') ? 'active' : '' }}"
                >
                    <span>All</span>
                    <span class="dept-chip-count">{{ $kpiMetrics['total_active'] ?? $employees->total() }}</span>
                </a>
                @foreach($branches as $b)
                    @php $cnt = $branchEmployeeCounts[$b->id] ?? 0; @endphp
                    <a 
                        href="{{ route('employees.index', array_merge(request()->query(), ['branch' => $b->id])) }}"
                        hx-get="{{ route('employees.index', array_merge(request()->query(), ['branch' => $b->id])) }}"
                        hx-target="#list-content" 
                        hx-swap="outerHTML" 
                        hx-push-url="true"
                        class="dept-chip {{ request('branch') == $b->id ? 'active' : '' }}"
                    >
                        <span>{{ $b->name }}</span>
                        <span class="dept-chip-count">{{ $cnt }}</span>
                    </a>
                @endforeach
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge status-counter">{{ number_format($employees->total()) }} {{ str('employee')->plural($employees->total()) }}</span>
            </div>
        </div>
    @endif

    <div class="d-none align-items-center justify-content-between gap-2 px-3 py-2 border-bottom bg-body-tertiary" data-employee-selection-bar>
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-check-square text-primary"></i>
            <span class="fw-semibold"><span data-employee-selected-count>0</span> selected</span>
            <span class="small text-body-secondary d-none d-md-inline">Current page selection</span>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button class="btn btn-action-link btn-sm" type="button" data-employee-export-selected>
                <i class="fa-solid fa-file-export"></i><span>Export selected</span>
            </button>
            <button class="btn btn-action-link btn-sm" type="button" data-employee-clear-selection>
                <i class="fa-solid fa-xmark"></i><span>Clear</span>
            </button>
        </div>
    </div>

    @include('employees._table', ['employees' => $employees])
</div>
