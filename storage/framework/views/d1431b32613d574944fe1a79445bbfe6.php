<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Leave types']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Leave types']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Leave types','icon' => 'fa-calendar-xmark','context' => 'Leave management']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Leave types','icon' => 'fa-calendar-xmark','context' => 'Leave management']); ?> <?php $__env->slot('filters', null, []); ?> <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true"><div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search name or code" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><select class="form-select reference-status" name="status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option><option value="1" <?php if(request('status') === '1'): echo 'selected'; endif; ?>>Active</option><option value="0" <?php if(request('status') === '0'): echo 'selected'; endif; ?>>Inactive</option></select><button class="btn btn-primary">Search</button></form> <?php $__env->endSlot(); ?> <?php $__env->slot('actions', null, []); ?> <?php if (isset($component)) { $__componentOriginal32c85afa77cc4fec54802bf6365d2208 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32c85afa77cc4fec54802bf6365d2208 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.list-actions','data' => ['addModal' => auth()->user()->can('leave.manage') ? 'createLeaveType' : null,'addLabel' => 'Add leave type']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('list-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['add-modal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->can('leave.manage') ? 'createLeaveType' : null),'add-label' => 'Add leave type']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $attributes = $__attributesOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__attributesOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32c85afa77cc4fec54802bf6365d2208)): ?>
<?php $component = $__componentOriginal32c85afa77cc4fec54802bf6365d2208; ?>
<?php unset($__componentOriginal32c85afa77cc4fec54802bf6365d2208); ?>
<?php endif; ?> <?php $__env->endSlot(); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $attributes = $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890)): ?>
<?php $component = $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890; ?>
<?php unset($__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890); ?>
<?php endif; ?>

    <?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Leave type</th><th>Entitlement</th><th>Policy</th><th>Usage</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-3"><div class="fw-semibold"><?php echo e($type->name); ?></div><small class="text-body-secondary"><?php echo e($type->code); ?></small></td>
                            <td><?php echo e(number_format((float) $type->days_per_year, 1)); ?> days / year<br><small class="text-body-secondary"><?php echo e($type->is_paid ? 'Paid leave' : 'Unpaid leave'); ?></small></td>
                            <td><small><?php echo e($type->carry_forward_allowed ? 'Carry forward: up to '.number_format((float) $type->maximum_carry_forward_days, 1).' days' : 'No carry forward'); ?></small><br><?php if($type->requires_attachment): ?><span class="badge text-bg-info">Document required</span><?php endif; ?></td>
                            <td><?php echo e($type->requests_count); ?> requests<br><small class="text-body-secondary"><?php echo e($type->balances_count); ?> balances</small></td>
                            <td><span class="badge text-bg-<?php echo e($type->is_active ? 'success' : 'secondary'); ?>"><?php echo e($type->is_active ? 'Active' : 'Inactive'); ?></span></td>
                            <td class="text-end pe-3">
                                <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editLeaveType<?php echo e($type->id); ?>"><i class="fa-solid fa-pen"></i><span>Edit</span></button>
                                <?php if($type->requests_count === 0 && $type->balances_count === 0): ?><form class="d-inline" method="POST" action="<?php echo e(route('leave.types.destroy', $type)); ?>" data-confirm="Delete this leave type?"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-action-link btn-sm text-danger" type="submit"><i class="fa-solid fa-trash"></i><span>Delete</span></button></form><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-body-secondary py-5">No leave types found. Create a policy before employees submit requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $types]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($types)]); ?>
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

    <div class="modal fade" id="createLeaveType" tabindex="-1" aria-labelledby="createLeaveTypeTitle" aria-hidden="true"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('leave.types.store')); ?>"><?php echo csrf_field(); ?>
        <div class="modal-header"><h2 class="modal-title fs-5" id="createLeaveTypeTitle">Add leave type</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><?php echo $__env->make('leave.types._form', ['type' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
        <?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['allowSaveNew' => true,'saveLabel' => 'Save & close','newLabel' => 'Save & new']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['allow-save-new' => true,'save-label' => 'Save & close','new-label' => 'Save & new']); ?>
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
    </form></div></div>

    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="editLeaveType<?php echo e($type->id); ?>" tabindex="-1" aria-labelledby="editLeaveTypeTitle<?php echo e($type->id); ?>" aria-hidden="true"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('leave.types.update', $type)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="modal-header"><h2 class="modal-title fs-5" id="editLeaveTypeTitle<?php echo e($type->id); ?>">Edit leave type</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><?php echo $__env->make('leave.types._form', ['type' => $type], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save changes</button></div>
        </form></div></div>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views/leave/types/index.blade.php ENDPATH**/ ?>