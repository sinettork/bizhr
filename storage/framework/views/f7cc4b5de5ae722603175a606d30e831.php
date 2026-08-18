<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Attendance & Timecards']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance & Timecards']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Daily Attendance & Time Tracking','icon' => 'fa-user-clock','context' => 'Attendance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Daily Attendance & Time Tracking','icon' => 'fa-user-clock','context' => 'Attendance']); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input class="form-control" type="date" name="date" value="<?php echo e($date->toDateString()); ?>" aria-label="Attendance date" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                </div>
                <button class="btn btn-primary reference-search-button">Filter date</button>
            </form>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.report')): ?>
                <a class="btn btn-action-link btn-sm" href="<?php echo e(route('attendance.reports.index')); ?>">
                    <i class="fa-solid fa-chart-column"></i><span>Reports</span>
                </a>
            <?php endif; ?>
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

    <?php if(session('status')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span><?php echo e(session('status')); ?></span></div>
    <?php endif; ?>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch & Department</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Worked Time</th>
                        <th>Late Variance</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusTone = in_array($record->status, ['present','remote_work','business_trip']) ? 'success' : ($record->status === 'late' ? 'warning' : 'secondary');
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark"><?php echo e($record->employee?->getFullName()); ?></div>
                                <small class="text-body-secondary"><?php echo e($record->employee?->employee_code); ?></small>
                            </td>
                            <td>
                                <div><?php echo e($record->employee?->branch?->name ?: 'All branches'); ?></div>
                                <small class="text-body-secondary"><?php echo e($record->employee?->department?->name); ?></small>
                            </td>
                            <td>
                                <?php if($record->check_in_at): ?>
                                    <span class="badge text-bg-light border text-dark fw-bold px-2 py-1"><i class="fa-regular fa-clock text-success me-1"></i><?php echo e($record->check_in_at->format('H:i')); ?></span>
                                <?php else: ?>
                                    <span class="text-body-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($record->check_out_at): ?>
                                    <span class="badge text-bg-light border text-dark fw-bold px-2 py-1"><i class="fa-regular fa-clock text-primary me-1"></i><?php echo e($record->check_out_at->format('H:i')); ?></span>
                                <?php else: ?>
                                    <span class="text-body-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-dark"><?php echo e(intdiv((int)$record->worked_minutes, 60)); ?>h <?php echo e((int)$record->worked_minutes % 60); ?>m</strong>
                            </td>
                            <td>
                                <?php if($record->late_minutes > 0): ?>
                                    <span class="badge text-bg-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>+<?php echo e($record->late_minutes); ?> min</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">On time</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-3">
                                <span class="badge text-bg-<?php echo e($statusTone); ?>"><?php echo e(str($record->status)->replace('_',' ')->title()); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-5 text-center text-body-secondary">
                                <i class="fa-regular fa-calendar-xmark fa-xl d-block mb-3 text-primary"></i>
                                No attendance records logged for <?php echo e($date->format('d M Y')); ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $records]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($records)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $attributes = $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $component = $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
    </div>
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
<?php /**PATH D:\www\bizhr\resources\views\attendance\index.blade.php ENDPATH**/ ?>