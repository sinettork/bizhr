<?php if (isset($component)) { $__componentOriginal08b8a564843783787e0bee3357e24f38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal08b8a564843783787e0bee3357e24f38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.auth','data' => ['title' => 'Two-factor authentication']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Two-factor authentication']); ?>
    <div class="text-center mb-4"><h1 class="h3 mb-2">Two-factor authentication</h1><p class="text-body-secondary mb-0">Enter the code from your authenticator app or a recovery code.</p></div>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <form method="POST" action="<?php echo e(route('two-factor.login')); ?>"><?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label" for="code">Authentication code</label><input class="form-control" id="code" name="code" inputmode="numeric" autocomplete="one-time-code"></div>
        <div class="mb-4"><label class="form-label" for="recovery_code">Recovery code <span class="text-body-secondary">(optional)</span></label><input class="form-control" id="recovery_code" name="recovery_code" autocomplete="one-time-code"></div>
        <button class="btn btn-primary w-100" type="submit">Continue</button>
    </form>
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
<?php /**PATH D:\www\bizhr\resources\views\pages\auth\two-factor-challenge.blade.php ENDPATH**/ ?>