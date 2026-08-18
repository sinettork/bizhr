<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'My Performance Reviews']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Performance Reviews']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'My Performance Appraisals','icon' => 'fa-star-half-stroke','context' => 'Personal Workspace']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Performance Appraisals','icon' => 'fa-star-half-stroke','context' => 'Personal Workspace']); ?>
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

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Appraisal Period</th>
                        <th>Overall Score</th>
                        <th>Manager Summary</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusBadge = match($review->status) {
                                'closed' => 'success',
                                'hr_approved' => 'primary',
                                'employee_acknowledged' => 'info',
                                'manager_submitted' => 'warning',
                                default => 'secondary',
                            };
                        ?>
                        <tr>
                            <td class="ps-3 fw-medium">
                                <?php echo e($review->period_start->format('d M Y')); ?> – <?php echo e($review->period_end->format('d M Y')); ?>

                            </td>
                            <td>
                                <?php if($review->overall_score): ?>
                                    <span class="badge text-bg-warning"><i class="fa-solid fa-star me-1"></i><?php echo e($review->overall_score); ?> / 5.0</span>
                                <?php else: ?>
                                    <span class="text-body-secondary small">Pending scoring</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="small text-body-secondary"><?php echo e(\Illuminate\Support\Str::limit($review->manager_comment, 80) ?: 'No summary comment.'); ?></span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo e($statusBadge); ?>"><?php echo e(str($review->status)->replace('_', ' ')->title()); ?></span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if($review->status === 'hr_approved'): ?>
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#ackReview<?php echo e($review->id); ?>">
                                        <i class="fa-solid fa-signature"></i><span>Acknowledge</span>
                                    </button>
                                <?php elseif($review->status === 'employee_acknowledged' || $review->status === 'closed'): ?>
                                    <span class="small text-success"><i class="fa-solid fa-circle-check me-1"></i>Acknowledged</span>
                                <?php else: ?>
                                    <span class="small text-body-secondary">In progress</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="5">
                                <i class="fa-solid fa-star-half-stroke fa-xl d-block mb-3 text-primary"></i>You have no performance reviews published yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $reviews]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reviews)]); ?>
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

    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($review->status === 'hr_approved'): ?>
            <div class="modal fade" id="ackReview<?php echo e($review->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="<?php echo e(route('performance.my-reviews.acknowledge', $review)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Acknowledge Performance Appraisal</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 small">
                                <div class="text-body-secondary mb-1">Appraisal Period:</div>
                                <div class="fw-semibold"><?php echo e($review->period_start->format('d M Y')); ?> – <?php echo e($review->period_end->format('d M Y')); ?></div>
                            </div>
                            <?php if($review->overall_score): ?>
                                <div class="mb-3 small">
                                    <div class="text-body-secondary mb-1">Score:</div>
                                    <div class="badge text-bg-warning fs-6"><i class="fa-solid fa-star me-1"></i><?php echo e($review->overall_score); ?> / 5.0</div>
                                </div>
                            <?php endif; ?>
                            <?php if($review->manager_comment): ?>
                                <div class="mb-3 p-2 bg-light rounded border small">
                                    <div class="text-body-secondary fw-semibold mb-1">Manager feedback:</div>
                                    <div><?php echo e($review->manager_comment); ?></div>
                                </div>
                            <?php endif; ?>
                            <div>
                                <label class="form-label">Employee comments (optional)</label>
                                <textarea class="form-control" name="comment" rows="3" placeholder="Add your comments or notes before confirming..."></textarea>
                            </div>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Confirm & Acknowledge']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Confirm & Acknowledge']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
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
<?php /**PATH D:\www\bizhr\resources\views\performance\my-reviews.blade.php ENDPATH**/ ?>