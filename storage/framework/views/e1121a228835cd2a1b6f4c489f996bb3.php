<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Users']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Users','icon' => 'fa-users-gear']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Users','icon' => 'fa-users-gear']); ?>
         <?php $__env->slot('filters', null, []); ?> <form class="reference-filter-form" method="GET" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true"><div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search name, email, or employee ID" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div><select class="form-select reference-status" name="status" hx-get="<?php echo e(request()->url()); ?>" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option><option value="1" <?php if(request('status') === '1'): echo 'selected'; endif; ?>>Active</option><option value="0" <?php if(request('status') === '0'): echo 'selected'; endif; ?>>Inactive</option></select><button class="btn btn-primary reference-search-button">Search</button></form> <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addUser"><i class="fa-solid fa-circle-plus"></i><span>Add user</span></button> <?php $__env->endSlot(); ?>
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
    <?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <div class="access-review-strip" aria-label="Access review summary"><span><strong><?php echo e($accessReview['privileged']); ?></strong> privileged users</span><span><strong><?php echo e($accessReview['dormant']); ?></strong> dormant active accounts</span><span><strong><?php echo e($accessReview['employees_without_accounts']); ?></strong> employees without accounts</span><span class="<?php echo e($accessReview['separated_with_access'] ? 'text-danger' : ''); ?>"><strong><?php echo e($accessReview['separated_with_access']); ?></strong> separated employees with access</span></div>
    <div class="reference-list"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">User</th><th>Employee</th><th>Roles</th><th>Verification</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr><td class="ps-3"><div class="fw-medium"><?php echo e($user->name); ?></div><small class="text-body-secondary"><?php echo e($user->email); ?></small></td><td><?php echo e($user->employee?->employee_code ? $user->employee->employee_code.' · '.($user->employee->full_name_en ?: $user->employee->first_name.' '.$user->employee->last_name) : 'Not linked'); ?></td><td><?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="badge text-bg-light border me-1"><?php echo e($role->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></td><td><?php echo e($user->email_verified_at ? 'Verified' : 'Pending'); ?></td><td><span class="badge text-bg-<?php echo e($user->is_active ? 'success' : 'secondary'); ?>"><?php echo e($user->is_active ? 'Active' : 'Inactive'); ?></span></td><td class="text-end pe-3"><?php ($protectedAccount = $user->hasAnyRole(['Super Admin', 'Owner']) && !auth()->user()->hasRole('Super Admin')); ?> <?php if(!$protectedAccount): ?><button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editUser<?php echo e($user->id); ?>"><i class="fa-solid fa-pen"></i><span>Edit access</span></button><form class="d-inline" method="POST" action="<?php echo e(route('users.password-reset', $user)); ?>"><?php echo csrf_field(); ?><button class="btn btn-action-link btn-sm" type="submit"><i class="fa-solid fa-key"></i><span>Reset password</span></button></form><?php if(!$user->is(auth()->user())): ?><form class="d-inline" method="POST" action="<?php echo e(route('users.status', $user)); ?>" data-confirm="<?php echo e($user->is_active ? 'Deactivate this account and revoke its sessions?' : 'Reactivate this account?'); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><input type="hidden" name="is_active" value="<?php echo e($user->is_active ? 0 : 1); ?>"><button class="btn btn-action-link btn-sm <?php echo e($user->is_active ? 'text-danger' : ''); ?>" type="submit"><i class="fa-solid fa-<?php echo e($user->is_active ? 'user-lock' : 'user-check'); ?>"></i><span><?php echo e($user->is_active ? 'Deactivate' : 'Reactivate'); ?></span></button></form><?php endif; ?> <?php else: ?><span class="small text-body-secondary">Super Admin approval required</span><?php endif; ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td class="text-center text-body-secondary py-5" colspan="6">No users.</td></tr><?php endif; ?>
    </tbody></table></div><?php if (isset($component)) { $__componentOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3f2e4ea659416fa6fb408144bf1d9a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination-footer','data' => ['paginator' => $users]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($users)]); ?>
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

    <div class="modal fade" id="addUser" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('users.store')); ?>"><?php echo csrf_field(); ?><div class="modal-header"><h2 class="modal-title fs-5">Provision user account</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if (isset($component)) { $__componentOriginalc421379cca5cd196a1511948fa40f304 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc421379cca5cd196a1511948fa40f304 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-access-fields','data' => ['roles' => $roles,'employees' => $employees]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-access-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roles' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roles),'employees' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employees)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc421379cca5cd196a1511948fa40f304)): ?>
<?php $attributes = $__attributesOriginalc421379cca5cd196a1511948fa40f304; ?>
<?php unset($__attributesOriginalc421379cca5cd196a1511948fa40f304); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc421379cca5cd196a1511948fa40f304)): ?>
<?php $component = $__componentOriginalc421379cca5cd196a1511948fa40f304; ?>
<?php unset($__componentOriginalc421379cca5cd196a1511948fa40f304); ?>
<?php endif; ?><div class="form-text mt-3">A password setup link will be sent to the user. No shared default password is created.</div></div><?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Provision & close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Provision & close']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?></form></div></div>
    <?php $__currentLoopData = $users->filter(fn($user) => !$user->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->hasRole('Super Admin')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="editUser<?php echo e($user->id); ?>" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="<?php echo e(route('users.update', $user)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><div class="modal-header"><h2 class="modal-title fs-5">Edit user access</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php if (isset($component)) { $__componentOriginalc421379cca5cd196a1511948fa40f304 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc421379cca5cd196a1511948fa40f304 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-access-fields','data' => ['roles' => $roles,'employees' => $employees,'user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-access-fields'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roles' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roles),'employees' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employees),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc421379cca5cd196a1511948fa40f304)): ?>
<?php $attributes = $__attributesOriginalc421379cca5cd196a1511948fa40f304; ?>
<?php unset($__attributesOriginalc421379cca5cd196a1511948fa40f304); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc421379cca5cd196a1511948fa40f304)): ?>
<?php $component = $__componentOriginalc421379cca5cd196a1511948fa40f304; ?>
<?php unset($__componentOriginalc421379cca5cd196a1511948fa40f304); ?>
<?php endif; ?><div class="alert alert-warning mt-3 mb-0">Saving access changes revokes this user's existing sessions.</div></div><?php if (isset($component)) { $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-save-actions','data' => ['saveLabel' => 'Save & close']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-save-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['save-label' => 'Save & close']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $attributes = $__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__attributesOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc)): ?>
<?php $component = $__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc; ?>
<?php unset($__componentOriginal3c5ee56ee360de8cefecdc77a4b1dddc); ?>
<?php endif; ?></form></div></div>
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
<?php endif; ?><?php /**PATH D:\www\bizhr\resources\views/access/users.blade.php ENDPATH**/ ?>