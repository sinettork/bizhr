<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Payroll Review & Approvals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll Review & Approvals']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Payroll Review & Exception Queue','icon' => 'fa-file-circle-check','context' => 'Payroll & Auditing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payroll Review & Exception Queue','icon' => 'fa-file-circle-check','context' => 'Payroll & Auditing']); ?>
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

    <!-- Overtime Approvals Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Overtime Approval Queue</h2>
        <form method="GET" class="reference-filter-form" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
            <select class="form-select reference-status" name="overtime_status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                <option value="pending">Pending only</option>
                <option value="approved" <?php if(request('overtime_status')==='approved'): echo 'selected'; endif; ?>>Approved</option>
                <option value="rejected" <?php if(request('overtime_status')==='rejected'): echo 'selected'; endif; ?>>Rejected</option>
                <option value="" <?php if(request()->missing('overtime_status')): echo 'selected'; endif; ?>>All statuses</option>
            </select>
            <button class="btn btn-primary reference-search-button">Filter</button>
        </form>
    </div>

    <div class="reference-list mb-4" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Work Date</th>
                        <th>Overtime Hours</th>
                        <th>Status</th>
                        <th>Review Note</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $overtime; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isPending = $row->overtime_review_status === 'pending';
                            $statusTone = match($row->overtime_review_status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            };
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark"><?php echo e($row->employee?->getFullName()); ?></div>
                                <small class="text-body-secondary"><?php echo e($row->employee?->department?->name ?? '—'); ?></small>
                            </td>
                            <td><?php echo e($row->work_date->format('d M Y')); ?></td>
                            <td><strong class="text-dark"><?php echo e(number_format($row->overtime_minutes/60, 2)); ?> hrs</strong></td>
                            <td><span class="badge text-bg-<?php echo e($statusTone); ?>"><?php echo e(ucfirst($row->overtime_review_status)); ?></span></td>
                            <td><span class="small text-body-secondary"><?php echo e($row->overtime_review_note ?: '—'); ?></span></td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if($isPending): ?>
                                    <form method="POST" action="<?php echo e(route('payroll.overtime.review', [$row, 'approve'])); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-action-link btn-sm text-success" type="submit">
                                            <i class="fa-solid fa-check"></i><span>Approve</span>
                                        </button>
                                    </form>
                                    <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectOt<?php echo e($row->id); ?>">
                                        <i class="fa-solid fa-xmark"></i><span>Reject</span>
                                    </button>
                                <?php else: ?>
                                    <span class="small text-body-secondary">Reviewed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-4 text-center text-body-secondary">
                                <i class="fa-solid fa-circle-check text-success me-1"></i>No overtime records waiting for approval in this queue.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payroll Calculation Exceptions Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Payroll Calculation Exceptions</h2>
            <span class="small text-body-secondary">Resolve exception source before approving a period, then re-generate its draft.</span>
        </div>
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Period</th>
                        <th>Employee</th>
                        <th>Exception Count</th>
                        <th class="pe-3">Calculated Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $exceptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3 fw-semibold text-dark"><?php echo e($item->period?->name); ?></td>
                            <td><?php echo e($item->employee?->getFullName()); ?></td>
                            <td><span class="badge text-bg-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i><?php echo e($item->exception_count); ?> exception(s)</span></td>
                            <td class="pe-3 fw-bold text-dark">$<?php echo e(number_format($item->net_salary, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="py-4 text-center text-body-secondary">
                                <i class="fa-solid fa-check-double text-success me-1"></i>No payroll calculation exceptions. All payroll entries are balanced!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $__currentLoopData = $overtime; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($row->overtime_review_status === 'pending'): ?>
            <div class="modal fade" id="rejectOt<?php echo e($row->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="<?php echo e(route('payroll.overtime.review', [$row, 'reject'])); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Reject Overtime Record</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-body-secondary mb-3">Rejecting overtime for <strong><?php echo e($row->employee?->getFullName()); ?></strong> on <?php echo e($row->work_date->format('d M Y')); ?> (<?php echo e(number_format($row->overtime_minutes/60, 2)); ?> hrs).</p>
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="note" required minlength="3" rows="3" placeholder="Reason for rejection..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH D:\www\bizhr\resources\views\payroll\review\index.blade.php ENDPATH**/ ?>