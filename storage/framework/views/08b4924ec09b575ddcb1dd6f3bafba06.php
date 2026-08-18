<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => $employee->getFullName().' ID card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->getFullName().' ID card')]); ?>
    <?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Employee ID card','icon' => 'fa-id-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Employee ID card','icon' => 'fa-id-card']); ?>
         <?php $__env->slot('actions', null, []); ?> <a class="btn btn-action-link btn-sm" href="<?php echo e(route('employees.show', $employee)); ?>"><i class="fa-solid fa-arrow-left"></i><span>Employee profile</span></a><button class="btn btn-action-link btn-sm" type="button" data-action="print"><i class="fa-solid fa-print"></i><span>Print ID card</span></button> <?php $__env->endSlot(); ?>
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

    <div class="id-card-workspace">
        <div class="employee-id-card-stack">
            <article class="employee-id-card" aria-label="Employee identification card front face">
                <header>
                    <div class="employee-id-brand">
                        <?php if($employee->company?->logo_path): ?>
                            <img src="<?php echo e(Storage::disk('public')->url($employee->company->logo_path)); ?>" alt="<?php echo e($employee->company->name); ?> logo" class="employee-id-company-logo">
                        <?php else: ?>
                            <strong><?php echo e($employee->company?->name ?? config('app.name')); ?></strong>
                        <?php endif; ?>
                        <small>EMPLOYEE IDENTIFICATION</small>
                    </div>
                    <i class="fa-solid fa-shield-check"></i>
                </header>
                <div class="employee-id-card-body">
                    <div class="employee-id-photo"><?php if($employee->profile_photo): ?><img src="<?php echo e(route('employees.photo',$employee)); ?>" alt="<?php echo e($employee->getFullName()); ?>"><?php else: ?><i class="fa-solid fa-user"></i><?php endif; ?></div>
                    <div class="employee-id-details"><h1><?php echo e($employee->getFullName()); ?></h1><?php if($employee->full_name_km): ?><h2><?php echo e($employee->full_name_km); ?></h2><?php endif; ?><p><?php echo e($employee->position?->title ?? 'Employee'); ?></p><dl><div><dt>ID</dt><dd><?php echo e($employee->employee_code); ?></dd></div><div><dt>Department</dt><dd><?php echo e($employee->department?->name ?? '—'); ?></dd></div><div><dt>Branch</dt><dd><?php echo e($employee->branch?->name ?? '—'); ?></dd></div><div><dt>Joined</dt><dd><?php echo e($employee->hire_date?->format('d M Y') ?? '—'); ?></dd></div></dl></div>
                </div>
                <footer><span>Expires: <?php echo e($employee->id_card_expiry_date?->format('d M Y') ?? 'No expiry set'); ?></span><small>Property of <?php echo e($employee->company?->name ?? config('app.name')); ?></small></footer>
            </article>

            <article class="employee-id-card employee-id-card-back" aria-label="Employee identification card back face">
                <header>
                    <div><strong>Emergency contact</strong><small>VALIDATION BACK</small></div>
                    <i class="fa-solid fa-id-card-clip"></i>
                </header>
                <div class="employee-id-card-back-body">
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Emergency contact</div>
                        <div class="employee-id-back-value"><?php echo e($employee->emergency_contact_name ?: 'Not provided'); ?></div>
                    </div>
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Phone</div>
                        <div class="employee-id-back-value"><?php echo e($employee->emergency_contact_phone ?: 'Not provided'); ?></div>
                    </div>
                    <div class="employee-id-back-section">
                        <div class="employee-id-back-label">Expiry date</div>
                        <div class="employee-id-back-value"><?php echo e($employee->id_card_expiry_date?->format('d M Y') ?? 'Not set'); ?></div>
                    </div>
                    <div class="employee-id-verification-box">
                        <img src="<?php echo e(route('employees.id-card.qr', $employee)); ?>" alt="Verification QR code" class="employee-id-qr-code">
                        <div class="employee-id-verification-meta">
                            <span>Verify</span>
                            <small>Secure QR endpoint</small>
                        </div>
                    </div>
                </div>
                <footer><span><?php echo e($employee->employee_code); ?></span><small>Verified via BizHR</small></footer>
            </article>
        </div>
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
<?php /**PATH D:\www\bizhr\resources\views/employees/id-card.blade.php ENDPATH**/ ?>