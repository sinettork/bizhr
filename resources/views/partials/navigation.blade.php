@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $can = static fn (string $permission): bool => (bool) $user?->can($permission);
    $item = static fn (string $label, string $icon, string $route, string $active, bool $visible = true): ?array => $visible && Route::has($route)
        ? compact('label', 'icon', 'route', 'active')
        : null;

    $checkAttendance = $can('attendance.checkin') || $can('attendance.checkout');
    $manageAttendance = $can('attendance.approve') || $can('attendance.report');

    $isPureSuperAdmin = (bool) $user?->hasRole('Super Admin')
        && $user?->roles()->where('name', '!=', 'Super Admin')->doesntExist();
    $hasEmployeeContext = $user?->companyId() !== null
        && $user?->employee()
            ->where('company_id', $user->companyId())
            ->where('is_active', true)
            ->whereNotIn('employment_status', ['Resigned', 'Terminated', 'Retired'])
            ->exists();
    $showPersonalWorkspace = ! $isPureSuperAdmin && $hasEmployeeContext;

    $groups = array_filter([
        ['People', 'fa-users', [
            $item('Employees', 'fa-users', 'employees.index', 'employees.*', $can('employee.view')),
            $item('Departments', 'fa-sitemap', 'departments.index', 'departments.*', $can('department.view')),
            $item('Positions', 'fa-briefcase', 'positions.index', 'positions.*', $can('position.view')),
            $item('Employment types', 'fa-id-card', 'employment-types.index', 'employment-types.*', $can('employment-type.view')),
            $item('Employment contracts', 'fa-file-signature', 'contracts.index', 'contracts.*', $can('contract.view')),
        ]],
        ['Time & Attendance', 'fa-clock', [
            $item('Attendance overview', 'fa-user-clock', 'attendance.checkinout', 'attendance.checkinout', $manageAttendance),
            $item('Schedules', 'fa-calendar-days', 'schedules.index', 'schedules.*', $can('schedule.view')),
            $item('Work shifts', 'fa-clock-rotate-left', 'work-shifts.index', 'work-shifts.*', $can('shift.view')),
            $item('Attendance QR', 'fa-qrcode', 'attendance.qr.display', 'attendance.qr.*', $manageAttendance),
            $item('Corrections', 'fa-clipboard-check', 'attendance.corrections.review', 'attendance.corrections.*', $can('attendance.approve')),
            $item('Reports', 'fa-chart-column', 'attendance.reports.index', 'attendance.reports.*', $can('attendance.report')),
        ]],
        ['Leave', 'fa-calendar-check', [
            $item('Approvals', 'fa-list-check', 'leave.requests.review', 'leave.requests.review', $can('leave.approve')),
            $item('Balances', 'fa-chart-pie', 'leave.balances.index', 'leave.balances.*', $can('leave.report') || $can('leave.manage')),
            $item('Leave types', 'fa-calendar-xmark', 'leave.types.index', 'leave.types.*', $can('leave.manage')),
        ]],
        ['Payroll', 'fa-money-check-dollar', [
            $item('Periods', 'fa-calendar', 'payroll.periods.index', 'payroll.periods.*', $can('payroll.view')),
            $item('Review', 'fa-file-circle-check', 'payroll.review', 'payroll.review', $can('payroll.approve')),
            $item('Reports', 'fa-chart-line', 'payroll.reports', 'payroll.reports', $can('payroll.report')),
            $item('Statutory profiles', 'fa-file-invoice-dollar', 'payroll.statutory-profiles', 'payroll.statutory-profiles', $can('payroll.approve')),
            $item('Settings', 'fa-sliders', 'payroll.settings', 'payroll.settings', $can('payroll.view')),
        ]],
        ['Performance', 'fa-chart-simple', [
            $item('Reviews', 'fa-star-half-stroke', 'performance.reviews', 'performance.reviews', $can('performance.view')),
            $item('Employee goals', 'fa-trophy', 'performance.goals', 'performance.goals', $can('performance.view')),
            $item('KPI templates', 'fa-bullseye', 'performance.kpi-templates', 'performance.kpi-templates', $can('performance.manage-goals')),
        ]],
        ['Operations', 'fa-layer-group', [
            $item('Tasks', 'fa-list-check', 'tasks.index', 'tasks.index', $can('task.view')),
            $item('Recruitment', 'fa-user-plus', 'recruitment.pipeline', 'recruitment.*', $can('recruitment.view')),
            $item('Training', 'fa-graduation-cap', 'training.index', 'training.index', $can('training.view')),
            $item('Assets', 'fa-boxes-stacked', 'assets.index', 'assets.index', $can('asset.view')),
            $item('Expenses', 'fa-receipt', 'expenses.index', 'expenses.index', $can('expense.view')),
            $item('Manage announcements', 'fa-pen-to-square', 'announcements.index', 'announcements.index', $can('announcement.manage')),
        ]],
        ['Company', 'fa-building', [
            $item('Company settings', 'fa-building-gear', 'company.settings', 'company.*', $can('company.view')),
            $item('Branches', 'fa-code-branch', 'branches.index', 'branches.*', $can('branch.view')),
        ]],
        ['Administration', 'fa-shield-halved', [
            $item('Users', 'fa-users-gear', 'users.index', 'users.*', $can('user.manage')),
            $item('Roles & permissions', 'fa-shield-halved', 'roles.index', 'roles.*', $can('role.manage')),
            $item('Audit logs', 'fa-file-shield', 'audit-logs.index', 'audit-logs.*', $can('audit.view')),
            $item('Import data', 'fa-file-import', 'imports.index', 'imports.*', $can('employee.create') || $can('branch.create') || $can('department.create') || $can('position.create') || $can('employment-type.create')),
            $item('Exports', 'fa-file-export', 'exports.index', 'exports.*', $can('employee.view') || $can('attendance.report') || $can('payroll.report')),
        ]],
    ], fn (array $group) => count(array_filter($group[2])) > 0);

    $personalItems = $showPersonalWorkspace ? array_values(array_filter([
        $item('My attendance', 'fa-user-clock', 'attendance.checkinout', 'attendance.checkinout', $checkAttendance),
        $item('My leave', 'fa-calendar-check', 'leave.requests.index', 'leave.requests.index', $can('leave.request')),
        $item('My payslips', 'fa-wallet', 'payroll.my-payslips', 'payroll.my-payslips', $can('payroll.view-own')),
        $item('My goals', 'fa-flag', 'performance.my-goals', 'performance.my-goals', $can('performance.view-own')),
        $item('My reviews', 'fa-star', 'performance.my-reviews', 'performance.my-reviews', $can('performance.view-own')),
        $item('My tasks', 'fa-square-check', 'tasks.mine', 'tasks.mine', $can('task.view-own')),
        $item('My training', 'fa-book-open', 'training.mine', 'training.mine', $can('training.view-own')),
        $item('My assets', 'fa-laptop', 'assets.mine', 'assets.mine', $can('asset.view-own')),
        $item('My expenses', 'fa-money-bill-wave', 'expenses.mine', 'expenses.mine', $can('expense.view-own')),
        $item('My contracts', 'fa-file-lines', 'contracts.mine', 'contracts.mine', $can('contract.view-own')),
        $item('Announcements', 'fa-bullhorn', 'announcements.feed', 'announcements.feed', $can('announcement.view')),
    ])) : [];
@endphp

<header class="navbar app-navbar app-topbar sticky-top">
    <div class="container-fluid px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn app-sidebar-toggle d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold m-0" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="fa-solid fa-people-group"></i></span>
                <span>{{ config('app.name', 'BizHR') }}</span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-1 ms-auto">
            <a class="btn app-topbar-action d-none d-sm-inline-flex" href="{{ route('dashboard') }}" title="Dashboard">
                <i class="fa-solid fa-gauge-high"></i><span class="d-none d-lg-inline">Dashboard</span>
            </a>

            @if(count($personalItems))
                <div class="dropdown d-none d-md-block">
                    <button class="btn app-topbar-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-check"></i><span class="d-none d-lg-inline">My workspace</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        @foreach($personalItems as $link)
                            <li><a class="dropdown-item {{ request()->routeIs($link['active']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="fa-solid {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dropdown">
                <button class="btn app-user-menu dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    @if($user?->avatar_path)
                        <img class="nav-user-avatar" src="{{ route('profile.avatar') }}" alt="">
                    @elseif($user?->employee?->profile_photo)
                        <img class="nav-user-avatar" src="{{ route('employees.photo',$user->employee) }}" alt="">
                    @else
                        <span class="app-user-fallback"><i class="fa-solid fa-user"></i></span>
                    @endif
                    <span class="d-none d-lg-inline ms-2">{{ $user?->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user-gear me-2"></i>My profile</a></li>
                    @if(count($personalItems))
                        <li class="d-md-none"><hr class="dropdown-divider"></li>
                        @foreach($personalItems as $link)
                            <li class="d-md-none"><a class="dropdown-item" href="{{ route($link['route']) }}"><i class="fa-solid {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</a></li>
                        @endforeach
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign out</button></form></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<aside class="offcanvas-xl offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
    <div class="offcanvas-header d-xl-none">
        <h2 class="offcanvas-title fs-6" id="appSidebarLabel">Navigation</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <nav class="app-sidebar-nav flex-grow-1" id="appSidebarMenu" aria-label="Primary navigation">
            <a class="app-sidebar-link app-sidebar-dashboard {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <span class="app-sidebar-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span>Dashboard</span>
            </a>

            @if(count($personalItems))
                @php
                    $personalActive = collect($personalItems)->contains(fn ($link) => request()->routeIs($link['active']));
                @endphp
                <div class="app-sidebar-group">
                    <button class="app-sidebar-group-toggle {{ $personalActive ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-my-workspace" aria-expanded="{{ $personalActive ? 'true' : 'false' }}" aria-controls="sidebar-my-workspace">
                        <span class="app-sidebar-icon"><i class="fa-solid fa-user-check"></i></span>
                        <span class="flex-grow-1 text-start">My workspace</span>
                        <i class="fa-solid fa-chevron-down app-sidebar-chevron"></i>
                    </button>
                    <div class="collapse {{ $personalActive ? 'show' : '' }}" id="sidebar-my-workspace" data-bs-parent="#appSidebarMenu">
                        <div class="app-sidebar-submenu">
                            @foreach($personalItems as $link)
                                <a class="app-sidebar-link app-sidebar-sublink {{ request()->routeIs($link['active']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                                    <span class="app-sidebar-subdot"></span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @foreach($groups as [$heading, $groupIcon, $items])
                @php
                    $items = array_values(array_filter($items));
                    $active = collect($items)->contains(fn ($link) => request()->routeIs($link['active']));
                    $menuId = 'sidebar-'.Str::slug($heading);
                @endphp
                <div class="app-sidebar-group">
                    <button class="app-sidebar-group-toggle {{ $active ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $menuId }}" aria-expanded="{{ $active ? 'true' : 'false' }}" aria-controls="{{ $menuId }}">
                        <span class="app-sidebar-icon"><i class="fa-solid {{ $groupIcon }}"></i></span>
                        <span class="flex-grow-1 text-start">{{ $heading }}</span>
                        <i class="fa-solid fa-chevron-down app-sidebar-chevron"></i>
                    </button>
                    <div class="collapse {{ $active ? 'show' : '' }}" id="{{ $menuId }}" data-bs-parent="#appSidebarMenu">
                        <div class="app-sidebar-submenu">
                            @foreach($items as $link)
                                <a class="app-sidebar-link app-sidebar-sublink {{ request()->routeIs($link['active']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                                    <span class="app-sidebar-subdot"></span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="app-sidebar-footer">
            <div class="small text-body-secondary">{{ config('app.name', 'BizHR') }}</div>
            <div class="small text-body-secondary">People operations workspace</div>
        </div>
    </div>
</aside>
