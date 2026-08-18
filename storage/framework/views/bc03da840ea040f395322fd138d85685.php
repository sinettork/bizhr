<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Attendance report']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance report']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Attendance report','icon' => 'fa-chart-column','context' => 'Reporting']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance report','icon' => 'fa-chart-column','context' => 'Reporting']); ?>
         <?php $__env->slot('actions', null, []); ?> <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['exportUrl' => route('exports.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['export-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('exports.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $attributes = $__attributesOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $component = $__componentOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__componentOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
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
    <div class="row g-3 mb-4"><?php $__currentLoopData = [['Total records', $statistics['total'], 'primary'], ['Present', $statistics['present'], 'success'], ['Late', $statistics['late'], 'warning'], ['Absent', $statistics['absent'], 'danger'], ['Attendance rate', $statistics['attendance_rate'].'%', 'info']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $tone]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="col-sm-6 col-lg"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-body-secondary"><?php echo e($label); ?></div><div class="fs-4 fw-semibold text-<?php echo e($tone); ?>"><?php echo e($value); ?></div></div></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><form class="row g-3" method="GET" action="<?php echo e(route('attendance.reports.index')); ?>"><div class="col-md-4"><label class="form-label">Search employee</label><input class="form-control" name="search" value="<?php echo e($search); ?>" placeholder="Name, code, or phone"></div><div class="col-md-2"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($branch->id); ?>" <?php if((int) $branchId === $branch->id): echo 'selected'; endif; ?>><?php echo e($branch->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All statuses</option><?php $__currentLoopData = ['present', 'late', 'absent', 'on_leave', 'half_day', 'holiday', 'rest_day', 'remote_work', 'business_trip']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if($status === $option): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $option))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-2"><label class="form-label">From</label><input class="form-control" type="date" name="date_from" value="<?php echo e($dateFrom); ?>"></div><div class="col-md-2"><label class="form-label">To</label><input class="form-control" type="date" name="date_to" value="<?php echo e($dateTo); ?>"></div><div class="col-12 d-flex justify-content-end gap-2"><a class="btn btn-light" href="<?php echo e(route('attendance.reports.index')); ?>">Reset</a><button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter me-2"></i>Apply filters</button></div></form></div></div>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Employee</th><th>Date</th><th>Check in / out</th><th>Status</th><th>Late</th><th>Worked</th><th class="pe-3">Overtime</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td class="ps-3"><div class="fw-medium"><?php echo e($attendance->employee?->full_name_km ?: $attendance->employee?->full_name_en ?: $attendance->employee?->employee_code); ?></div><small class="text-body-secondary"><?php echo e($attendance->employee?->employee_code); ?><?php if($attendance->employee?->department): ?> · <?php echo e($attendance->employee->department->name); ?><?php endif; ?></small></td><td><?php echo e($attendance->work_date->format('d M Y')); ?></td><td><?php echo e($attendance->check_in_at?->format('H:i') ?? '—'); ?> / <?php echo e($attendance->check_out_at?->format('H:i') ?? '—'); ?></td><td><span class="badge text-bg-<?php echo e(in_array($attendance->status, ['present', 'remote_work', 'business_trip']) ? 'success' : ($attendance->status === 'late' ? 'warning' : 'secondary')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $attendance->status))); ?></span></td><td><?php echo e($attendance->late_minutes ? $attendance->late_minutes.' min' : '—'); ?></td><td><?php echo e(intdiv((int) $attendance->worked_minutes, 60)); ?>h <?php echo e((int) $attendance->worked_minutes % 60); ?>m</td><td class="pe-3"><?php echo e($attendance->overtime_minutes ? intdiv((int) $attendance->overtime_minutes, 60).'h '.((int) $attendance->overtime_minutes % 60).'m' : '—'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td class="py-5 text-center text-body-secondary" colspan="7">No attendance records match these filters.</td></tr><?php endif; ?></tbody></table></div><?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $attendances]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attendances)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $attributes = $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1)): ?>
<?php $component = $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1; ?>
<?php unset($__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1); ?>
<?php endif; ?></div>
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
<?php /**PATH D:\www\bizhr\resources\views\attendance\reports\index.blade.php ENDPATH**/ ?>