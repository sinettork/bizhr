<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Training & Learning Catalogue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Training & Learning Catalogue']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Training Catalogue & Programs','icon' => 'fa-graduation-cap','context' => 'Learning & Development']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Training Catalogue & Programs','icon' => 'fa-graduation-cap','context' => 'Learning & Development']); ?>
         <?php $__env->slot('filters', null, []); ?> 
            <form class="reference-filter-form" method="GET" data-live-search-form hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search course by title..." data-live-search hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
            </form>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => auth()->user()->can('training.manage') ? 'createCourse' : null,'addLabel' => 'Add new course']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('training.manage') ? 'createCourse' : null),'add-label' => 'Add new course']); ?>
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
                        <th class="ps-3">Course Title</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark"><?php echo e($course->title); ?></div>
                                <?php if($course->description): ?><small class="text-body-secondary"><?php echo e(\Illuminate\Support\Str::limit($course->description, 60)); ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?php if($course->is_mandatory): ?>
                                    <span class="badge text-bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Mandatory</span>
                                <?php else: ?>
                                    <span class="badge text-bg-light border text-dark">Optional</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(number_format($course->duration_minutes/60, 1)); ?> hrs</td>
                            <td><?php echo e($course->enrollments_count); ?> employee(s)</td>
                            <td>
                                <span class="badge text-bg-<?php echo e($course->is_active ? 'success' : 'secondary'); ?>"><?php echo e($course->is_active ? 'Active' : 'Inactive'); ?></span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('training.manage')): ?>
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#enroll<?php echo e($course->id); ?>">
                                        <i class="fa-solid fa-user-plus"></i><span>Assign</span>
                                    </button>
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#editCourse<?php echo e($course->id); ?>">
                                        <i class="fa-solid fa-pen"></i><span>Edit</span>
                                    </button>
                                    <form class="d-inline" method="POST" action="<?php echo e(route('training.destroy', $course)); ?>" data-confirm="Archive this course? Active enrollments must be completed first.">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                            <i class="fa-solid fa-trash"></i><span>Delete</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-graduation-cap fa-xl d-block mb-3 text-primary"></i>No training courses found in the catalogue.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $courses]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($courses)]); ?>
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

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('training.manage')): ?>
        <div class="modal fade" id="createCourse" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="<?php echo e(route('training.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Add course to catalogue</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($component)) { $__componentOriginal63747cdeeb10b275ce38c56763947738 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal63747cdeeb10b275ce38c56763947738 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.training-course-fields','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('training-course-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal63747cdeeb10b275ce38c56763947738)): ?>
<?php $attributes = $__attributesOriginal63747cdeeb10b275ce38c56763947738; ?>
<?php unset($__attributesOriginal63747cdeeb10b275ce38c56763947738); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal63747cdeeb10b275ce38c56763947738)): ?>
<?php $component = $__componentOriginal63747cdeeb10b275ce38c56763947738; ?>
<?php unset($__componentOriginal63747cdeeb10b275ce38c56763947738); ?>
<?php endif; ?>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['allowSaveNew' => true,'saveLabel' => 'Create course']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['allow-save-new' => true,'save-label' => 'Create course']); ?>
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

        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="modal fade" id="editCourse<?php echo e($course->id); ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="<?php echo e(route('training.update', $course)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit course</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (isset($component)) { $__componentOriginal63747cdeeb10b275ce38c56763947738 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal63747cdeeb10b275ce38c56763947738 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.training-course-fields','data' => ['course' => $course]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('training-course-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['course' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($course)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal63747cdeeb10b275ce38c56763947738)): ?>
<?php $attributes = $__attributesOriginal63747cdeeb10b275ce38c56763947738; ?>
<?php unset($__attributesOriginal63747cdeeb10b275ce38c56763947738); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal63747cdeeb10b275ce38c56763947738)): ?>
<?php $component = $__componentOriginal63747cdeeb10b275ce38c56763947738; ?>
<?php unset($__componentOriginal63747cdeeb10b275ce38c56763947738); ?>
<?php endif; ?>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Save changes']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Save changes']); ?>
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

            <div class="modal fade" id="enroll<?php echo e($course->id); ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="<?php echo e(route('training.enroll', $course)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Assign <?php echo e($course->title); ?></h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" name="employee_id" required>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->getFullName()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <label class="form-label mt-3">Target completion deadline</label>
                            <input class="form-control" type="date" name="due_date" min="<?php echo e(today()->toDateString()); ?>">
                        </div>
                        <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Assign training']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Assign training']); ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views/training/index.blade.php ENDPATH**/ ?>