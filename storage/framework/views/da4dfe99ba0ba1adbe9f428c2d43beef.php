<?php
    $metricWidgets = [
        'active_employees' => ['Active employees', $metrics['employees'], 'fa-users', 'primary', 'Current workforce'],
        'scheduled_today' => ['Scheduled today', $metrics['scheduled'] ?? '—', 'fa-calendar-day', 'info', 'Excludes rest days'],
        'checked_in' => ['Checked in', $metrics['present'], 'fa-user-check', 'success', 'Present, late, remote and trip'],
        'late_arrivals' => ['Late arrivals', $metrics['late'], 'fa-clock', 'warning', 'After the configured grace period'],
        'approved_leave' => ['On approved leave', $metrics['leave'], 'fa-calendar-xmark', 'secondary', 'Approved leave covering today'],
        'no_checkout' => ['No check-out', $metrics['openCheckouts'] ?? '—', 'fa-right-from-bracket', 'warning', 'Needs attendance follow-up'],
        'uncovered_schedule' => ['Uncovered schedule', $metrics['absent'] ?? '—', 'fa-user-xmark', 'danger', 'Scheduled but no check-in or leave'],
        'open_tasks' => ['My open tasks', $metrics['openTasks'], 'fa-list-check', 'primary', 'Assigned work not closed'],
    ];
    $widgetLabels = collect($metricWidgets)->mapWithKeys(fn ($widget, $key) => [$key => $widget[0]])->merge([
        'action_queue' => 'Action queue', 'my_work' => 'My work summary', 'recent_attendance' => 'Recent attendance',
    ]);
    $dashboardPreferences = auth()->user()->dashboard_preferences ?? ['order' => [], 'hidden' => []];
?>

<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Dashboard','icon' => 'fa-chart-line']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','icon' => 'fa-chart-line']); ?>
         <?php $__env->slot('filters', null, []); ?> <span class="small text-body-secondary">Operational overview · <?php echo e(now()->format('d M Y')); ?></span> <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="dashboard-view-actions"><button class="btn btn-action-link btn-sm" type="button" data-dashboard-edit><i class="fa-solid fa-pen"></i><span>Edit dashboard</span></button></div>
            <div class="dashboard-edit-actions" hidden>
                <div class="dropdown"><button class="btn btn-action-link btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-circle-plus"></i><span>Add widget</span></button><div class="dropdown-menu dropdown-menu-end dashboard-widget-menu p-2"><?php $__currentLoopData = $widgetLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if($key !== 'recent_attendance' || $isManagerOrAdmin): ?><label class="dropdown-item-text form-check"><input class="form-check-input me-2" type="checkbox" value="<?php echo e($key); ?>" data-dashboard-widget-toggle><span><?php echo e($label); ?></span></label><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></div>
                <button class="btn btn-action-link btn-sm" type="button" data-dashboard-auto><i class="fa-solid fa-wand-magic-sparkles"></i><span>Auto organize</span></button>
                <button class="btn btn-action-link btn-sm" type="button" data-dashboard-save><i class="fa-solid fa-floppy-disk"></i><span>Save</span></button>
                <button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-reset><i class="fa-solid fa-rotate-left"></i><span>Reset</span></button>
                <button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-cancel><i class="fa-solid fa-xmark"></i><span>Cancel</span></button>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $attributes = $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $component = $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>

    <div class="dashboard-grid row g-3" data-dashboard-grid>
        <?php $__currentLoopData = $metricWidgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $value, $icon, $tone, $hint]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!$isManagerOrAdmin && $key !== 'open_tasks') continue; ?>
            <section class="col-sm-6 col-xl-3 dashboard-widget" data-widget="<?php echo e($key); ?>" data-default-order="<?php echo e($loop->index); ?>" draggable="false">
                <div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide <?php echo e($label); ?>" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-body d-flex align-items-center gap-3"><span class="metric-icon bg-<?php echo e($tone); ?>-subtle text-<?php echo e($tone); ?>"><i class="fa-solid <?php echo e($icon); ?>"></i></span><div><div class="text-body-secondary small"><?php echo e($label); ?></div><div class="fs-3 fw-semibold"><?php echo e($value); ?></div><small class="text-body-secondary"><?php echo e($hint); ?></small></div></div></div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <section class="col-lg-7 dashboard-widget" data-widget="action_queue" data-default-order="8" draggable="false"><div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide action queue" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white"><h2 class="h5 mb-0">Action queue</h2></div><div class="list-group list-group-flush"><?php $__empty_1 = true; $__currentLoopData = $actionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="<?php echo e(route($item['route'])); ?>"><span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid <?php echo e($item['icon']); ?>"></i></span><span class="flex-grow-1"><?php echo e($item['label']); ?></span><span class="badge text-bg-<?php echo e($item['count'] ? 'warning' : 'success'); ?>"><?php echo e($item['count']); ?></span><i class="fa-solid fa-chevron-right text-body-secondary small"></i></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="list-group-item text-body-secondary py-4">No action queues are assigned to your role.</div><?php endif; ?></div></div></section>

        <section class="col-lg-5 dashboard-widget" data-widget="my_work" data-default-order="9" draggable="false"><div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide my work summary" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white"><h2 class="h5 mb-0">My work summary</h2></div><div class="card-body"><div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-body-secondary">Open tasks</span><strong><?php echo e($metrics['openTasks']); ?></strong></div><div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-body-secondary">Leave balance</span><strong><?php echo e(number_format($metrics['leaveBalance'], 1)); ?> days</strong></div><div class="d-flex justify-content-between"><span class="text-body-secondary">Exports processing</span><strong><?php echo e($metrics['pendingExports']); ?></strong></div></div></div></section>

        <?php if($isManagerOrAdmin): ?><section class="col-12 dashboard-widget" data-widget="recent_attendance" data-default-order="10" draggable="false"><div class="card shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide recent attendance" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h5 mb-0">Recent attendance</h2><?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.report')): ?><a href="<?php echo e(route('attendance.reports.index')); ?>" class="btn btn-sm btn-outline-primary">View report</a><?php endif; ?></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Employee</th><th>Check in</th><th class="pe-3">Status</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $recentAttendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td class="ps-3 fw-medium"><?php echo e($attendance->employee?->getFullName()); ?></td><td><?php echo e($attendance->check_in_at?->format('H:i') ?? '—'); ?></td><td class="pe-3"><span class="badge text-bg-success"><?php echo e(ucfirst(str_replace('_', ' ', $attendance->status))); ?></span></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="py-5 text-center text-body-secondary">No attendance records today.</td></tr><?php endif; ?></tbody></table></div></div></section><?php endif; ?>
    </div>

    <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>" type="application/json" id="dashboard-preferences"><?php echo json_encode($dashboardPreferences, 15, 512) ?></script>
    <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">window.dashboardPreferenceUrl = <?php echo json_encode(route('preferences.dashboard.update'), 15, 512) ?>; window.dashboardResetUrl = <?php echo json_encode(route('preferences.dashboard.destroy'), 15, 512) ?>;</script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\dashboard\index.blade.php ENDPATH**/ ?>