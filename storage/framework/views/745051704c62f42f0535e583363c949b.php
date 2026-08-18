<?php if (isset($component)) { $__componentOriginal08b8a564843783787e0bee3357e24f38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal08b8a564843783787e0bee3357e24f38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.auth','data' => ['title' => 'Verify email']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verify email']); ?>
    <div class="text-center mb-4"><h1 class="h3 mb-2">Verify your email</h1><p class="text-body-secondary mb-0">Check your inbox and follow the verification link.</p></div>
    <?php if(session('status') === 'verification-link-sent'): ?><div class="alert alert-success">A new verification link has been sent.</div><?php endif; ?>
    <form method="POST" action="<?php echo e(route('verification.send')); ?>"><?php echo csrf_field(); ?><button class="btn btn-primary w-100" type="submit">Resend verification email</button></form>
    <form method="POST" action="<?php echo e(route('logout')); ?>" class="text-center mt-3"><?php echo csrf_field(); ?><button class="btn btn-link text-danger" type="submit">Sign out</button></form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal08b8a564843783787e0bee3357e24f38)): ?>
<?php $attributes = $__attributesOriginal08b8a564843783787e0bee3357e24f38; ?>
<?php unset($__attributesOriginal08b8a564843783787e0bee3357e24f38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal08b8a564843783787e0bee3357e24f38)): ?>
<?php $component = $__componentOriginal08b8a564843783787e0bee3357e24f38; ?>
<?php unset($__componentOriginal08b8a564843783787e0bee3357e24f38); ?>
<?php endif; ?>
<?php /**PATH D:\www\bizhr\resources\views\pages\auth\verify-email.blade.php ENDPATH**/ ?>