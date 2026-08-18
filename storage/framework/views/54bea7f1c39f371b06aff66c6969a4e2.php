<?php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $can = static fn (string $permission): bool => (bool) $user?->can($permission);
    $item = static fn (string $label, string $icon, string $route, string $active, bool $visible = true): ?array => $visible && Route::has($route)
        ? compact('label', 'icon', 'route', 'active')
        : null;

    $checkAttendance = $can('attendance.checkin') || $can('attendance.checkout');
    $groups = array_filter([
        ['Company', [
            $item('Company settings', 'fa-building', 'company.settings', 'company.*', $can('company.view')),
            $item('Branches', 'fa-code-branch', 'branches.index', 'branches.*', $can('branch.view')),
            $item('Departments', 'fa-sitemap', 'departments.index', 'departments.*', $can('department.view')),
            $item('Positions', 'fa-briefcase', 'positions.index', 'positions.*', $can('position.view')),
            $item('Employment types', 'fa-id-card', 'employment-types.index', 'employment-types.*', $can('employment-type.view')),
        ]],
        ['Employees & attendance', [
            $item('Employees', 'fa-users', 'employees.index', 'employees.*', $can('employee.view')),
            $item('Employment contracts', 'fa-file-signature', 'contracts.index', 'contracts.*', $can('contract.view')),
            $item('My contracts', 'fa-file-lines', 'contracts.mine', 'contracts.mine', $can('contract.view-own')),
            $item('Work shifts', 'fa-clock', 'work-shifts.index', 'work-shifts.*', $can('shift.view')),
            $item('Schedules', 'fa-calendar-days', 'schedules.index', 'schedules.*', $can('schedule.view')),
            $item('Check in / out', 'fa-user-clock', 'attendance.checkinout', 'attendance.checkinout', $checkAttendance),
            $item('Attendance QR', 'fa-qrcode', 'attendance.qr.display', 'attendance.qr.*', $can('attendance.approve') || $can('attendance.report')),
            $item('Attendance corrections', 'fa-clipboard-check', 'attendance.corrections.review', 'attendance.corrections.*', $can('attendance.approve')),
            $item('Attendance report', 'fa-chart-column', 'attendance.reports.index', 'attendance.reports.*', $can('attendance.report')),
        ]],
        ['Leave', [
            $item('Leave types', 'fa-calendar-xmark', 'leave.types.index', 'leave.types.*', $can('leave.manage')),
            $item('My leave requests', 'fa-paper-plane', 'leave.requests.index', 'leave.requests.index', $can('leave.request')),
            $item('Review leave requests', 'fa-list-check', 'leave.requests.review', 'leave.requests.review', $can('leave.approve')),
            $item('Leave balances', 'fa-chart-pie', 'leave.balances.index', 'leave.balances.*', $can('leave.report') || $can('leave.manage')),
        ]],
        ['Payroll', [
            $item('My payslips', 'fa-wallet', 'payroll.my-payslips', 'payroll.my-payslips', $can('payroll.view-own')),
            $item('Payroll periods', 'fa-money-check-dollar', 'payroll.periods.index', 'payroll.periods.*', $can('payroll.view')),
            $item('Payroll settings', 'fa-sliders', 'payroll.settings', 'payroll.settings', $can('payroll.view')),
            $item('Payroll review', 'fa-file-circle-check', 'payroll.review', 'payroll.review', $can('payroll.approve')),
            $item('Statutory profiles', 'fa-file-invoice-dollar', 'payroll.statutory-profiles', 'payroll.statutory-profiles', $can('payroll.approve')),
            $item('Payroll reports', 'fa-chart-line', 'payroll.reports', 'payroll.reports', $can('payroll.report')),
        ]],
        ['Performance', [
            $item('My performance reviews', 'fa-star', 'performance.my-reviews', 'performance.my-reviews', $can('performance.view-own')),
            $item('Performance reviews', 'fa-star-half-stroke', 'performance.reviews', 'performance.reviews', $can('performance.view')),
            $item('My goals', 'fa-flag', 'performance.my-goals', 'performance.my-goals', $can('performance.view-own')),
            $item('Employee goals', 'fa-trophy', 'performance.goals', 'performance.goals', $can('performance.view')),
            $item('KPI templates', 'fa-bullseye', 'performance.kpi-templates', 'performance.kpi-templates', $can('performance.manage-goals')),
        ]],
        ['Operations', [
            $item('Announcements', 'fa-bullhorn', 'announcements.feed', 'announcements.feed', $can('announcement.view')),
            $item('Manage announcements', 'fa-bullhorn', 'announcements.index', 'announcements.index', $can('announcement.manage')),
            $item('My tasks', 'fa-square-check', 'tasks.mine', 'tasks.mine', $can('task.view-own')),
            $item('Tasks', 'fa-list-check', 'tasks.index', 'tasks.index', $can('task.view')),
            $item('Recruitment', 'fa-user-plus', 'recruitment.pipeline', 'recruitment.*', $can('recruitment.view')),
            $item('My training', 'fa-book-open', 'training.mine', 'training.mine', $can('training.view-own')),
            $item('Training', 'fa-graduation-cap', 'training.index', 'training.index', $can('training.view')),
            $item('My assets', 'fa-laptop', 'assets.mine', 'assets.mine', $can('asset.view-own')),
            $item('Assets', 'fa-boxes-stacked', 'assets.index', 'assets.index', $can('asset.view')),
            $item('My expenses', 'fa-money-bill-wave', 'expenses.mine', 'expenses.mine', $can('expense.view-own')),
            $item('Expenses', 'fa-receipt', 'expenses.index', 'expenses.index', $can('expense.view')),
        ]],
        ['Administration', [
            $item('Users', 'fa-users-gear', 'users.index', 'users.*', $can('user.manage')),
            $item('Roles & permissions', 'fa-shield-halved', 'roles.index', 'roles.*', $can('role.manage')),
            $item('Audit logs', 'fa-file-shield', 'audit-logs.index', 'audit-logs.*', $can('audit.view')),
            $item('Import data', 'fa-file-import', 'imports.index', 'imports.*', $can('employee.create') || $can('branch.create') || $can('department.create') || $can('position.create') || $can('employment-type.create')),
            $item('Exports', 'fa-file-export', 'exports.index', 'exports.*', $can('employee.view') || $can('attendance.report') || $can('payroll.report')),
        ]],
    ], fn (array $group) => count(array_filter($group[1])) > 0);
?>

<nav class="navbar navbar-expand-xl navbar-dark app-navbar shadow-sm">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?php echo e(route('dashboard')); ?>"><span class="brand-mark"><i class="fa-solid fa-people-group"></i></span><?php echo e(config('app.name', 'BizHR')); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#applicationNavigation" aria-controls="applicationNavigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="applicationNavigation">
            <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>"><i class="fa-solid fa-gauge-high me-1"></i>Dashboard</a></li>
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$heading, $items]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $items = array_values(array_filter($items)); $active = collect($items)->contains(fn ($link) => request()->routeIs($link['active'])); $menuId = 'menu-'.\Illuminate\Support\Str::slug($heading); ?>
                    <li class="nav-item dropdown"><a class="nav-link dropdown-toggle <?php echo e($active ? 'active' : ''); ?>" href="#" id="<?php echo e($menuId); ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php echo e($heading); ?></a><ul class="dropdown-menu shadow border-0" aria-labelledby="<?php echo e($menuId); ?>"><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="dropdown-item <?php echo e(request()->routeIs($link['active']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="fa-solid <?php echo e($link['icon']); ?> me-2"></i><?php echo e($link['label']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="dropdown"><button class="btn btn-link nav-link text-white text-decoration-none dropdown-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false"><?php if($user?->avatar_path): ?><img class="nav-user-avatar me-2" src="<?php echo e(route('profile.avatar')); ?>" alt=""><?php elseif($user?->employee?->profile_photo): ?><img class="nav-user-avatar me-2" src="<?php echo e(route('employees.photo',$user->employee)); ?>" alt=""><?php else: ?><i class="fa-solid fa-circle-user me-1"></i><?php endif; ?><?php echo e($user?->name); ?></button><ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i class="fa-solid fa-user-gear me-2"></i>My profile</a></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign out</button></form></li></ul></div>
        </div>
    </div>
</nav>
<?php /**PATH D:\www\bizhr\resources\views/partials/navigation.blade.php ENDPATH**/ ?>