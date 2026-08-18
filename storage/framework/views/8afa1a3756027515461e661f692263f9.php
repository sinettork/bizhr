<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'My profile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My profile']); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'My profile','icon' => 'fa-user-gear']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My profile','icon' => 'fa-user-gear']); ?>
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

    <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100"><div class="card-header">Profile image</div><div class="card-body">
                    <label class="image-upload-zone" for="avatar">
                        <?php if($user->avatar_path): ?>
                            <img id="avatar-preview" src="<?php echo e(route('profile.avatar')); ?>" alt="<?php echo e($user->name); ?>">
                        <?php else: ?>
                            <img id="avatar-preview" src="" alt="" hidden>
                            <span class="image-upload-placeholder"><i class="fa-solid fa-cloud-arrow-up"></i><strong>Upload profile image</strong><small>JPG, PNG or WebP · max 2 MB<br>Square image recommended</small></span>
                        <?php endif; ?>
                    </label>
                    <input class="visually-hidden" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="avatar-preview">
                    <?php if($user->avatar_path): ?><label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="remove_avatar" value="1"><span class="form-check-label">Remove current image</span></label><?php endif; ?>
                </div></div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100"><div class="card-header">Account information</div><div class="card-body row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?php echo e(old('name', $user->name)); ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required><div class="form-text">Changing email requires verification again.</div></div>
                    <?php if($user->employee): ?><div class="col-12"><div class="alert alert-light border mb-0"><i class="fa-solid fa-id-badge me-2 text-primary"></i>Linked employee: <a href="<?php echo e(route('employees.show', $user->employee)); ?>"><?php echo e($user->employee->getFullName()); ?> · <?php echo e($user->employee->employee_code); ?></a></div></div><?php endif; ?>
                </div></div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3"><a class="btn btn-light" href="<?php echo e(route('dashboard')); ?>">Close</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save profile</button></div>
    </form>
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
<?php /**PATH D:\www\bizhr\resources\views\settings\profile.blade.php ENDPATH**/ ?>