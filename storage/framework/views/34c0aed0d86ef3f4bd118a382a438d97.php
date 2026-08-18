<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Performance Reviews']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Performance Reviews']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Employee Performance Reviews','icon' => 'fa-star','context' => 'Performance & Appraisals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employee Performance Reviews','icon' => 'fa-star','context' => 'Performance & Appraisals']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => auth()->user()->can('performance.create') ? 'addReview' : null,'addLabel' => 'Create review cycle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('performance.create') ? 'addReview' : null),'add-label' => 'Create review cycle']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $attributes = $__attributesOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $component = $__componentOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__componentOriginal32c85afa77cc4fec54802bf6365d2208); ?>
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
    <?php if($errors->any()): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo e($errors->first()); ?></span></div>
    <?php endif; ?>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Reviewer</th>
                        <th>Appraisal Period</th>
                        <th>Score</th>
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
                            <td class="ps-3">
                                <div class="fw-medium text-dark"><?php echo e($review->employee?->getFullName()); ?></div>
                                <small class="text-body-secondary"><?php echo e($review->employee?->department?->name ?? '—'); ?></small>
                            </td>
                            <td>
                                <div><?php echo e($review->reviewer?->name ?? 'Unassigned'); ?></div>
                                <small class="text-body-secondary">v<?php echo e($review->version); ?></small>
                            </td>
                            <td>
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
                                <span class="badge text-bg-<?php echo e($statusBadge); ?>"><?php echo e(str($review->status)->replace('_',' ')->title()); ?></span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if($review->status === 'draft' && $review->reviewer_id === auth()->id()): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.review')): ?>
                                        <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#submitReview<?php echo e($review->id); ?>">
                                            <i class="fa-solid fa-pen-to-square"></i><span>Score & submit</span>
                                        </button>
                                    <?php endif; ?>
                                <?php elseif($review->status === 'manager_submitted'): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.approve')): ?>
                                        <form class="d-inline" method="POST" action="<?php echo e(route('performance.reviews.transition', [$review, 'approve'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-action-link btn-sm text-success" type="submit">
                                                <i class="fa-solid fa-check"></i><span>HR approve</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif($review->status === 'employee_acknowledged'): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.approve')): ?>
                                        <form class="d-inline" method="POST" action="<?php echo e(route('performance.reviews.transition', [$review, 'close'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-action-link btn-sm" type="submit">
                                                <i class="fa-solid fa-lock"></i><span>Close review</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.reopen')): ?>
                                    <?php if(in_array($review->status, ['manager_submitted','hr_approved','employee_acknowledged','closed'], true)): ?>
                                        <button class="btn btn-action-link btn-sm text-secondary" data-bs-toggle="modal" data-bs-target="#reopenReview<?php echo e($review->id); ?>">
                                            <i class="fa-solid fa-lock-open"></i><span>Reopen</span>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-star fa-xl d-block mb-3 text-primary"></i>No performance reviews found.
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.review')): ?>
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($review->status === 'draft' && $review->reviewer_id === auth()->id()): ?>
                <div class="modal fade" id="submitReview<?php echo e($review->id); ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <form class="modal-content" method="POST" action="<?php echo e(route('performance.reviews.submit', $review)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-star me-2 text-primary"></i>Score performance · <?php echo e($review->employee?->getFullName()); ?></h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php $__currentLoopData = $review->scores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="row g-2 border-bottom pb-3 mb-3">
                                        <div class="col-md-5">
                                            <div class="fw-semibold"><?php echo e($criterion->criterion_name); ?></div>
                                            <small class="text-body-secondary">Target: <?php echo e($criterion->target_value); ?> <?php echo e($criterion->measurement_unit); ?> · Weight <?php echo e($criterion->weight); ?>%</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Score (1–5) <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" min="1" max="5" name="scores[<?php echo e($criterion->id); ?>]" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Manager comment</label>
                                            <input class="form-control" name="comments[<?php echo e($criterion->id); ?>]" placeholder="Required if score is 1–2">
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Key Strengths & Wins</label>
                                        <textarea class="form-control" name="strengths" rows="2" placeholder="Highlight notable achievements..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Areas for Improvement</label>
                                        <textarea class="form-control" name="areas_for_improvement" rows="2" placeholder="Development goals..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Overall Manager Summary</label>
                                        <textarea class="form-control" name="manager_comment" rows="2" placeholder="Overall appraisal summary..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Submit for HR approval']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Submit for HR approval']); ?>
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
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.reopen')): ?>
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(in_array($review->status, ['manager_submitted','hr_approved','employee_acknowledged','closed'], true)): ?>
                <div class="modal fade" id="reopenReview<?php echo e($review->id); ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="<?php echo e(route('performance.reviews.transition', [$review, 'reopen'])); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-lock-open me-2 text-warning"></i>Reopen performance review</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Reopen Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" minlength="15" required placeholder="Specify reason for reopening this closed review..."></textarea>
                                <small class="text-body-secondary d-block mt-1">A detailed reason is retained with the review version history.</small>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Reopen review']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Reopen review']); ?>
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
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('performance.create')): ?>
        <div class="modal fade" id="addReview" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="<?php echo e(route('performance.reviews.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-star me-2 text-primary"></i>Initiate review cycle</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select class="form-select mb-3" name="employee_id" required>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->getFullName()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label class="form-label">Period start date <span class="text-danger">*</span></label>
                        <input class="form-control mb-3" type="date" name="period_start" required value="<?php echo e(today()->startOfQuarter()->toDateString()); ?>">
                        <label class="form-label">Period end date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="period_end" required value="<?php echo e(today()->endOfQuarter()->toDateString()); ?>">
                    </div>
                    <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Create review']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Create review']); ?>
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
<?php /**PATH D:\www\bizhr\resources\views\performance\reviews.blade.php ENDPATH**/ ?>