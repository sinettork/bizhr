<x-layouts::app title="Departments">
    @php
        $getDeptStyle = function ($name) {
            $n = strtolower($name);
            if (str_contains($n, 'hr') || str_contains($n, 'human') || str_contains($n, 'people') || str_contains($n, 'talent')) {
                return ['color' => '#4338ca', 'bg' => '#eef2ff', 'border' => '#c7d2fe', 'icon' => 'fa-users-gear', 'badge' => 'People'];
            } elseif (str_contains($n, 'tech') || str_contains($n, 'it') || str_contains($n, 'engineer') || str_contains($n, 'develop') || str_contains($n, 'software') || str_contains($n, 'system')) {
                return ['color' => '#0284c7', 'bg' => '#f0f9ff', 'border' => '#bae6fd', 'icon' => 'fa-laptop-code', 'badge' => 'Technology'];
            } elseif (str_contains($n, 'finan') || str_contains($n, 'account') || str_contains($n, 'audit') || str_contains($n, 'tax') || str_contains($n, 'payroll')) {
                return ['color' => '#059669', 'bg' => '#ecfdf5', 'border' => '#a7f3d0', 'icon' => 'fa-coins', 'badge' => 'Finance'];
            } elseif (str_contains($n, 'sale') || str_contains($n, 'market') || str_contains($n, 'commercial') || str_contains($n, 'growth') || str_contains($n, 'brand')) {
                return ['color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fde68a', 'icon' => 'fa-chart-line', 'badge' => 'Commercial'];
            } elseif (str_contains($n, 'operat') || str_contains($n, 'logistic') || str_contains($n, 'supply') || str_contains($n, 'warehouse') || str_contains($n, 'procure')) {
                return ['color' => '#0d9488', 'bg' => '#f0fdfa', 'border' => '#99f6e4', 'icon' => 'fa-gears', 'badge' => 'Operations'];
            } elseif (str_contains($n, 'legal') || str_contains($n, 'complian') || str_contains($n, 'risk')) {
                return ['color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'icon' => 'fa-scale-balanced', 'badge' => 'Legal & Risk'];
            } elseif (str_contains($n, 'support') || str_contains($n, 'service') || str_contains($n, 'care') || str_contains($n, 'help')) {
                return ['color' => '#e11d48', 'bg' => '#fff1f2', 'border' => '#fecdd3', 'icon' => 'fa-headset', 'badge' => 'Customer Care'];
            }
            return ['color' => '#4f46e5', 'bg' => '#eef2ff', 'border' => '#c7d2fe', 'icon' => 'fa-sitemap', 'badge' => 'Department'];
        };
    @endphp

    <x-workspace-command-bar title="Departments" icon="fa-sitemap" context="Organization">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input 
                        class="form-control" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search department, code or manager" 
                        hx-get="{{ request()->url() }}" 
                        hx-target="[data-list-container]" 
                        hx-swap="outerHTML" 
                        hx-push-url="false" 
                        hx-trigger="keyup changed delay:500ms"
                        autocomplete="off"
                    >
                </div>
                <select class="form-select reference-status" name="branch_id" aria-label="Filter by branch" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select class="form-select reference-status" name="status" aria-label="Filter by status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button class="btn btn-primary reference-search-button" type="submit">Search</button>

                @if(request()->filled('search') || request()->filled('branch_id') || request()->filled('status'))
                    <a class="btn btn-action-link btn-sm" href="{{ route('departments.index') }}" hx-get="{{ route('departments.index') }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                        <i class="fa-solid fa-rotate-left"></i><span>Clear</span>
                    </a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions 
                :add-target="auth()->user()->can('department.create') ? '#departmentForm' : null" 
                add-label="Add department" 
            />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check text-success flex-shrink-0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-triangle-exclamation text-danger flex-shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="reference-list" data-list-container>
        {{-- Workspace Summary Metric Cards --}}
        @if(isset($summaryMetrics))
            <div class="p-2 p-md-3 border-bottom bg-body-tertiary">
                <div class="row g-2 g-md-3">
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border bg-white p-2 p-md-3 shadow-none">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="text-body-secondary small fw-semibold text-truncate">Total Departments</span>
                                <span class="department-icon-badge" style="width:28px;height:28px;font-size:.75rem;background:#eef2ff;color:#4f46e5;">
                                    <i class="fa-solid fa-sitemap"></i>
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summaryMetrics['total_departments'] ?? $departments->total()) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border bg-white p-2 p-md-3 shadow-none">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="text-body-secondary small fw-semibold text-truncate">Active Units</span>
                                <span class="department-icon-badge" style="width:28px;height:28px;font-size:.75rem;background:#ecfdf5;color:#059669;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ number_format($summaryMetrics['active_departments'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border bg-white p-2 p-md-3 shadow-none">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="text-body-secondary small fw-semibold text-truncate">Assigned Workforce</span>
                                <span class="department-icon-badge" style="width:28px;height:28px;font-size:.75rem;background:#eef2ff;color:#4338ca;">
                                    <i class="fa-solid fa-users"></i>
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summaryMetrics['total_employees'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border bg-white p-2 p-md-3 shadow-none">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="text-body-secondary small fw-semibold text-truncate">Branches Covered</span>
                                <span class="department-icon-badge" style="width:28px;height:28px;font-size:.75rem;background:#f0f9ff;color:#0284c7;">
                                    <i class="fa-solid fa-building"></i>
                                </span>
                            </div>
                            <div class="fs-4 fw-bold text-info-emphasis mt-1">{{ number_format($summaryMetrics['branches_count'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Branch Filter Chips & View Switcher Bar --}}
        <div class="px-3 py-2 border-bottom bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="dept-quick-chips-wrapper flex-grow-1 min-w-0">
                <span class="text-body-secondary small fw-semibold me-1 flex-shrink-0">
                    <i class="fa-solid fa-building me-1 text-indigo"></i>Branch:
                </span>
                <a 
                    href="{{ route('departments.index', array_filter(array_merge(request()->query(), ['branch_id' => null]))) }}" 
                    hx-get="{{ route('departments.index', array_filter(array_merge(request()->query(), ['branch_id' => null]))) }}"
                    hx-target="[data-list-container]" 
                    hx-swap="outerHTML" 
                    hx-push-url="true"
                    class="dept-chip {{ !request()->filled('branch_id') ? 'active' : '' }}"
                >
                    <span>All</span>
                    <span class="dept-chip-count">{{ $summaryMetrics['total_departments'] ?? $departments->total() }}</span>
                </a>
                @foreach($branches as $b)
                    @php $cnt = $branchDepartmentCounts[$b->id] ?? 0; @endphp
                    <a 
                        href="{{ route('departments.index', array_merge(request()->query(), ['branch_id' => $b->id])) }}"
                        hx-get="{{ route('departments.index', array_merge(request()->query(), ['branch_id' => $b->id])) }}"
                        hx-target="[data-list-container]" 
                        hx-swap="outerHTML" 
                        hx-push-url="true"
                        class="dept-chip {{ request('branch_id') == $b->id ? 'active' : '' }}"
                    >
                        <span>{{ $b->name }}</span>
                        <span class="dept-chip-count">{{ $cnt }}</span>
                    </a>
                @endforeach
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge status-counter">{{ number_format($departments->total()) }} {{ str('department')->plural($departments->total()) }}</span>
                {{-- View Mode Toggle --}}
                <div class="btn-group btn-group-sm dept-view-toggle" role="group" aria-label="View switcher">
                    <button type="button" class="btn btn-outline-secondary active" data-view-btn="cards" title="Cards view">
                        <i class="fa-solid fa-grip me-1"></i><span class="d-none d-sm-inline">Cards</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view-btn="table" title="Table view">
                        <i class="fa-solid fa-table-list me-1"></i><span class="d-none d-sm-inline">Table</span>
                    </button>
                </div>
            </div>
        </div>

        @if($departments->count())
            @php
                $departmentGroups = $departments->getCollection()->groupBy(fn ($department) => $department->branch?->name ?: 'Company-wide');
            @endphp

            {{-- 1. CARDS VIEW --}}
            <div id="dept-cards-view" class="p-3">
                @foreach($departmentGroups as $branchName => $group)
                    <section class="mb-4 last-child-mb-0">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-1 border-bottom">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="page-icon flex-shrink-0" style="width:28px;height:28px;font-size:.7rem;"><i class="fa-solid fa-building"></i></span>
                                <div class="min-w-0 d-flex align-items-baseline gap-2">
                                    <h2 class="h6 mb-0 text-dark fw-bold text-truncate">{{ $branchName }}</h2>
                                    <span class="text-body-secondary small">({{ $group->count() }} {{ str('department')->plural($group->count()) }})</span>
                                </div>
                            </div>
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                            @foreach($group as $department)
                                @php
                                    $style = $getDeptStyle($department->name);
                                    $headcount = $department->employees_count ?? 0;
                                    $capacity = $department->headcount_capacity;
                                    $fillPct = $capacity > 0 ? min(100, round($headcount / $capacity * 100)) : null;
                                    $fillColor = $fillPct === null ? null : ($fillPct >= 100 ? 'danger' : ($fillPct >= 85 ? 'warning' : 'success'));
                                    $telegramHref = $department->telegram_username
                                        ? (str_starts_with($department->telegram_username, 'http') ? $department->telegram_username : 'https://t.me/' . ltrim($department->telegram_username, '@'))
                                        : null;
                                @endphp
                                <div class="col">
                                    <article class="card h-100 department-card shadow-none">
                                        <div class="card-body p-3 d-flex flex-column">

                                            {{-- Card Header: Icon + Name + Badges + Actions --}}
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="d-flex align-items-start gap-2 min-w-0">
                                                    <span class="department-icon-badge flex-shrink-0" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }}; border: 1px solid {{ $style['border'] }};">
                                                        <i class="fa-solid {{ $style['icon'] }}"></i>
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="d-flex align-items-center gap-1 mb-1">
                                                            <span class="badge" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }}; font-size: .62rem; padding: .15rem .4rem;">{{ $style['badge'] }}</span>
                                                            <span class="status-text text-bg-{{ $department->is_active ? 'success' : 'secondary' }}" style="font-size: .62rem;">
                                                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </div>
                                                        {{-- Bilingual Name --}}
                                                        <h3 class="h6 mb-0 text-dark fw-bold text-truncate" title="{{ $department->name }}">
                                                            {{ $department->name }}
                                                        </h3>
                                                        @if($department->local_name)
                                                            <div class="text-body-secondary text-truncate" style="font-size: .72rem;" title="{{ $department->local_name }}">
                                                                {{ $department->local_name }}
                                                            </div>
                                                        @endif
                                                        @if($department->code)
                                                            <small class="text-body-secondary"><i class="fa-solid fa-code me-1"></i>{{ $department->code }}</small>
                                                        @endif
                                                    </div>
                                                </div>

                                                <x-entity-action-menu
                                                    :can-edit="auth()->user()->can('department.edit')"
                                                    :can-delete="auth()->user()->can('department.delete')"
                                                    edit-target="#editDepartment{{ $department->id }}"
                                                    :delete-url="route('departments.destroy', $department)"
                                                    delete-confirm="Delete this department? Referenced departments cannot be deleted."
                                                    aria-label="Actions for {{ $department->name }}"
                                                />
                                            </div>

                                            {{-- Headcount Capacity Bar --}}
                                            @if($capacity !== null && $capacity > 0)
                                                <div class="mb-2">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="text-body-secondary" style="font-size: .68rem; font-weight: 600;">
                                                            <i class="fa-solid fa-users-viewfinder me-1"></i>HEADCOUNT
                                                        </span>
                                                        <span style="font-size: .68rem; font-weight: 700;" class="text-{{ $fillColor }}">
                                                            {{ $headcount }} / {{ $capacity }}
                                                            @if($fillPct >= 100)
                                                                <span class="ms-1 badge text-bg-danger" style="font-size: .58rem;">FULL</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 4px; border-radius: .2rem;">
                                                        <div class="progress-bar bg-{{ $fillColor }}" style="width: {{ $fillPct }}%; border-radius: .2rem;"></div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Description --}}
                                            @if($department->description)
                                                <p class="small text-body-secondary mb-2 text-truncate" title="{{ $department->description }}">
                                                    {{ $department->description }}
                                                </p>
                                            @else
                                                <div class="mb-1"></div>
                                            @endif

                                            {{-- Stats Grid: Employees + Positions --}}
                                            <div class="row g-2 mt-auto mb-2">
                                                <div class="col-6">
                                                    <a href="{{ route('employees.index', ['department' => $department->id]) }}"
                                                        class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none dept-stat-block"
                                                        title="View employees in this department"
                                                        style="background:#f4f6fa; border: 1px solid #e8ecf4;">
                                                        <span style="width:26px;height:26px;border-radius:.2rem;background:#eef2ff;color:#4f46e5;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;">
                                                            <i class="fa-solid fa-users"></i>
                                                        </span>
                                                        <div class="min-w-0">
                                                            <div class="fw-bold text-dark lh-1" style="font-size:.88rem;">{{ number_format($headcount) }}@if($capacity)<span class="text-body-secondary fw-normal" style="font-size:.7rem;">/{{ $capacity }}</span>@endif</div>
                                                            <div class="text-body-secondary lh-1 mt-1" style="font-size:.65rem;font-weight:600;">STAFF</div>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-6">
                                                    <a href="{{ route('positions.index', ['department_id' => $department->id]) }}"
                                                        class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none dept-stat-block"
                                                        title="View positions in this department"
                                                        style="background:#f4f6fa; border: 1px solid #e8ecf4;">
                                                        <span style="width:26px;height:26px;border-radius:.2rem;background:#eff6ff;color:#2563eb;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;">
                                                            <i class="fa-solid fa-briefcase"></i>
                                                        </span>
                                                        <div class="min-w-0">
                                                            <div class="fw-bold text-dark lh-1" style="font-size:.88rem;">{{ number_format($department->positions_count ?? 0) }}</div>
                                                            <div class="text-body-secondary lh-1 mt-1" style="font-size:.65rem;font-weight:600;">POSITIONS</div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- Manager & Contact Footer (with Attendance + Leave icon actions) --}}
                                            <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2 small text-body-secondary">
                                                <div class="d-flex align-items-center gap-2 min-w-0" title="Manager: {{ $department->manager_name ?: 'Not assigned' }}">
                                                    <span class="department-manager-avatar">
                                                        <i class="fa-solid fa-user-tie"></i>
                                                    </span>
                                                    <span class="text-truncate fw-medium text-dark">
                                                        {{ $department->manager_name ?: 'No manager' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    @can('attendance.report')
                                                        <a href="{{ route('attendance.reports.index', ['department_id' => $department->id]) }}"
                                                            class="text-info text-decoration-none"
                                                            title="Attendance report for this department">
                                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                                        </a>
                                                    @endcan
                                                    @can('leave.approve')
                                                        <a href="{{ route('leave.requests.review', ['department_id' => $department->id]) }}"
                                                            class="text-decoration-none" style="color: #d97706;"
                                                            title="Leave review for this department">
                                                            <i class="fa-solid fa-calendar-xmark"></i>
                                                        </a>
                                                    @endcan
                                                    @if($telegramHref)
                                                        <a href="{{ $telegramHref }}" target="_blank" rel="noopener" class="text-info text-decoration-none" title="Telegram: {{ $department->telegram_username }}">
                                                            <i class="fa-brands fa-telegram"></i>
                                                        </a>
                                                    @endif
                                                    @if($department->phone_extension)
                                                        <span class="badge border text-body-secondary" style="font-size:.62rem;background:#f4f6fa;" title="Ext {{ $department->phone_extension }}">
                                                            <i class="fa-solid fa-phone-volume me-1"></i>{{ $department->phone_extension }}
                                                        </span>
                                                    @endif
                                                    @if($department->email)
                                                        <a href="mailto:{{ $department->email }}" class="text-body-secondary text-decoration-none" title="{{ $department->email }}">
                                                            <i class="fa-solid fa-envelope"></i>
                                                        </a>
                                                    @endif
                                                    @if($department->phone && !$department->phone_extension)
                                                        <a href="tel:{{ $department->phone }}" class="text-body-secondary text-decoration-none" title="{{ $department->phone }}">
                                                            <i class="fa-solid fa-phone"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- 2. TABLE VIEW --}}
            <div id="dept-table-view" class="table-responsive d-none">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Department</th>
                            <th>Branch</th>
                            <th>Manager</th>
                            <th>Headcount</th>
                            <th>Quick Actions</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                            @php
                                $style = $getDeptStyle($department->name);
                                $headcount = $department->employees_count ?? 0;
                                $capacity = $department->headcount_capacity;
                                $fillPct = $capacity > 0 ? min(100, round($headcount / $capacity * 100)) : null;
                                $fillColor = $fillPct === null ? null : ($fillPct >= 100 ? 'danger' : ($fillPct >= 85 ? 'warning' : 'success'));
                                $telegramHref = $department->telegram_username
                                    ? (str_starts_with($department->telegram_username, 'http') ? $department->telegram_username : 'https://t.me/' . ltrim($department->telegram_username, '@'))
                                    : null;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="department-icon-badge" style="width:30px;height:30px;font-size:.75rem;background-color: {{ $style['bg'] }}; color: {{ $style['color'] }}; border: 1px solid {{ $style['border'] }};">
                                            <i class="fa-solid {{ $style['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $department->name }}</div>
                                            @if($department->local_name)
                                                <div class="text-body-secondary" style="font-size: .7rem;">{{ $department->local_name }}</div>
                                            @endif
                                            <small class="text-body-secondary">
                                                <span class="badge me-1" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }}; font-size: .62rem;">{{ $style['badge'] }}</span>
                                                @if($department->code) {{ $department->code }} @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-body-secondary">
                                        <i class="fa-solid fa-building me-1"></i>{{ $department->branch?->name ?: 'Company-wide' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="department-manager-avatar" style="width:22px;height:22px;font-size:.65rem;">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </span>
                                        <span class="fw-medium text-dark">{{ $department->manager_name ?: '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('employees.index', ['department' => $department->id]) }}"
                                        class="dept-direct-link"
                                        title="View employees">
                                        <i class="fa-solid fa-users text-indigo"></i>
                                        <span>{{ $headcount }}@if($capacity) / {{ $capacity }} @endif</span>
                                    </a>
                                    @if($fillPct !== null)
                                        <div class="progress mt-1" style="height: 3px; width: 60px; border-radius: .15rem;">
                                            <div class="progress-bar bg-{{ $fillColor }}" style="width: {{ $fillPct }}%;"></div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <a href="{{ route('positions.index', ['department_id' => $department->id]) }}"
                                            class="dept-direct-link" title="{{ $department->positions_count }} positions">
                                            <i class="fa-solid fa-briefcase text-primary"></i>
                                            <span>{{ number_format($department->positions_count ?? 0) }}</span>
                                        </a>
                                        @can('attendance.report')
                                            <a href="{{ route('attendance.reports.index', ['department_id' => $department->id]) }}"
                                                class="dept-direct-link" title="Attendance">
                                                <i class="fa-solid fa-clock-rotate-left text-info"></i>
                                            </a>
                                        @endcan
                                        @can('leave.approve')
                                            <a href="{{ route('leave.requests.review', ['department_id' => $department->id]) }}"
                                                class="dept-direct-link" title="Leave review">
                                                <i class="fa-solid fa-calendar-xmark" style="color:#e07b0a;"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-body-secondary d-flex align-items-center gap-2 flex-wrap">
                                        @if($telegramHref)
                                            <a href="{{ $telegramHref }}" target="_blank" rel="noopener" class="text-info text-decoration-none" title="Telegram: {{ $department->telegram_username }}">
                                                <i class="fa-brands fa-telegram"></i>
                                            </a>
                                        @endif
                                        @if($department->phone_extension)
                                            <span title="Extension {{ $department->phone_extension }}"><i class="fa-solid fa-phone-volume me-1"></i>{{ $department->phone_extension }}</span>
                                        @endif
                                        @if($department->phone)
                                            <span>{{ $department->phone }}</span>
                                        @endif
                                        @if($department->email)
                                            <a href="mailto:{{ $department->email }}" class="text-body-secondary text-decoration-none" title="{{ $department->email }}">
                                                <i class="fa-solid fa-envelope"></i>
                                            </a>
                                        @endif
                                        @if(!$department->phone && !$department->email && !$telegramHref)
                                            <span>—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="status-text text-bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <x-entity-action-menu
                                        :can-edit="auth()->user()->can('department.edit')"
                                        :can-delete="auth()->user()->can('department.delete')"
                                        edit-target="#editDepartment{{ $department->id }}"
                                        :delete-url="route('departments.destroy', $department)"
                                        delete-confirm="Delete this department? Referenced departments cannot be deleted."
                                        aria-label="Actions for {{ $department->name }}"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination-footer :paginator="$departments" />
        @else
            <x-empty-state 
                class="py-5 px-3" 
                icon="fa-sitemap" 
                title="No departments found" 
                message="Adjust the branch or status filters, or add a new department to build your organization structure." 
            />
        @endif
    </div>

    @can('department.create')
        <div class="modal fade" id="departmentForm" tabindex="-1" aria-labelledby="departmentFormTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('departments.store') }}">
                    @csrf
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="page-icon" style="width:32px;height:32px;font-size:.78rem;"><i class="fa-solid fa-sitemap"></i></span>
                            <h2 class="modal-title fs-5" id="departmentFormTitle">Add department</h2>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <x-department-fields :branches="$branches" />
                        <div class="mt-3 pt-3 border-top">
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <span class="form-check-label fw-semibold">Active operational department</span>
                            </label>
                        </div>
                    </div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
                </form>
            </div>
        </div>
    @endcan

    @can('department.edit')
        @foreach($departments as $department)
            <div class="modal fade" id="editDepartment{{ $department->id }}" tabindex="-1" aria-labelledby="editDepartmentTitle{{ $department->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('departments.update', $department) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="page-icon" style="width:32px;height:32px;font-size:.78rem;"><i class="fa-solid fa-pen-to-square"></i></span>
                                <h2 class="modal-title fs-5" id="editDepartmentTitle{{ $department->id }}">Edit department</h2>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <x-department-fields :branches="$branches" :department="$department" />
                            <div class="mt-3 pt-3 border-top">
                                <label class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($department->is_active)>
                                    <span class="form-check-label fw-semibold">Active operational department</span>
                                </label>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Save & close" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            (() => {
                function applyDeptView(mode) {
                    const cardsEl = document.getElementById('dept-cards-view');
                    const tableEl = document.getElementById('dept-table-view');
                    const cardBtns = document.querySelectorAll('[data-view-btn="cards"]');
                    const tableBtns = document.querySelectorAll('[data-view-btn="table"]');

                    if (!cardsEl || !tableEl) return;

                    if (mode === 'table') {
                        cardsEl.classList.add('d-none');
                        tableEl.classList.remove('d-none');
                        cardBtns.forEach(b => b.classList.remove('active'));
                        tableBtns.forEach(b => b.classList.add('active'));
                        try { localStorage.setItem('bizhr_dept_view', 'table'); } catch(e) {}
                    } else {
                        tableEl.classList.add('d-none');
                        cardsEl.classList.remove('d-none');
                        tableBtns.forEach(b => b.classList.remove('active'));
                        cardBtns.forEach(b => b.classList.add('active'));
                        try { localStorage.setItem('bizhr_dept_view', 'cards'); } catch(e) {}
                    }
                }

                window.switchDeptView = applyDeptView;

                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-view-btn]');
                    if (!btn) return;
                    e.preventDefault();
                    const mode = btn.getAttribute('data-view-btn');
                    applyDeptView(mode);
                });

                function syncDeptView() {
                    try {
                        const saved = localStorage.getItem('bizhr_dept_view');
                        if (saved === 'table') {
                            applyDeptView('table');
                        } else if (saved === 'cards') {
                            applyDeptView('cards');
                        }
                    } catch(e) {}
                }

                document.addEventListener('DOMContentLoaded', syncDeptView);
                document.addEventListener('htmx:afterSwap', syncDeptView);
                document.addEventListener('htmx:load', syncDeptView);
            })();
        </script>
    @endpush
</x-layouts::app>
