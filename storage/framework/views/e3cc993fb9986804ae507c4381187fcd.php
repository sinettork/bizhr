<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Attendance Corrections']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance Corrections']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Attendance Corrections','icon' => 'fa-clipboard-check','context' => 'Attendance Requests']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attendance Corrections','icon' => 'fa-clipboard-check','context' => 'Attendance Requests']); ?>
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
    <?php if($errors->any()): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($errors->first()); ?></span></div>
    <?php endif; ?>

    <?php $__currentLoopData = $requests->where('status', 'rejected'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $correction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="alert alert-warning d-flex flex-column flex-md-row gap-2 align-items-md-center">
            <span><strong>Correction rejected:</strong> <?php echo e($correction->review_note); ?></span>
            <form method="POST" action="<?php echo e(route('attendance.corrections.reopen', $correction)); ?>" class="ms-md-auto d-flex gap-2">
                <?php echo csrf_field(); ?>
                <label class="visually-hidden" for="reopen-reason-<?php echo e($correction->id); ?>">Reason for reopening</label>
                <input id="reopen-reason-<?php echo e($correction->id); ?>" class="form-control form-control-sm" name="reason" required minlength="5" maxlength="2000" placeholder="Reason for reopening">
                <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">Reopen</button>
            </form>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <h2 class="h6 mb-0 text-dark fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Submit Correction Request</h2>
        </div>
        <form method="POST" action="<?php echo e(route('attendance.corrections.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Attendance record <span class="text-danger">*</span></label>
                    <select class="form-select" name="attendance_id" required>
                        <option value="">Choose a record</option>
                        <?php $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($attendance->id); ?>"><?php echo e($attendance->work_date->format('d M Y')); ?> · In <?php echo e($attendance->check_in_at?->format('H:i') ?? '—'); ?> · Out <?php echo e($attendance->check_out_at?->format('H:i') ?? '—'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Correct check-in</label>
                    <input class="form-control" type="datetime-local" name="requested_check_in">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Correct check-out</label>
                    <input class="form-control" type="datetime-local" name="requested_check_out">
                </div>
                <div class="col-12">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" rows="3" required minlength="5" maxlength="2000" placeholder="Explain the reason for time adjustment..."></textarea>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane me-1"></i>Submit request</button>
            </div>
        </form>
    </div>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Requested Change</th>
                        <th>Reason</th>
                        <th>Review Note</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $correction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3 fw-medium text-dark"><?php echo e($correction->attendance?->work_date?->format('d M Y')); ?></td>
                            <td>
                                <div>In: <?php echo e($correction->requested_check_in?->format('d M H:i') ?? '—'); ?></div>
                                <div>Out: <?php echo e($correction->requested_check_out?->format('d M H:i') ?? '—'); ?></div>
                            </td>
                            <td><?php echo e($correction->reason); ?></td>
                            <td><?php echo e($correction->review_note ?: '—'); ?></td>
                            <td>
                                <span class="badge text-bg-<?php echo e($correction->status === 'approved' ? 'success' : ($correction->status === 'rejected' ? 'danger' : 'warning')); ?>">
                                    <?php echo e(ucfirst($correction->status)); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-5 text-center text-body-secondary">
                                <i class="fa-solid fa-clipboard-check fa-xl d-block mb-3 text-primary"></i>No correction requests submitted.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $requests]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requests)]); ?>
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
<?php /**PATH D:\www\bizhr\resources\views/attendance/corrections/index.blade.php ENDPATH**/ ?>