<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.app','data' => ['title' => 'Security']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Security']); ?><?php if (isset($component)) { $__componentOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6e937d9b9f4e06b362f470f2f7d890 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.workspace-command-bar','data' => ['title' => 'Security','icon' => 'fa-shield-halved']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('workspace-command-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Security','icon' => 'fa-shield-halved']); ?>
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
<?php if($errors->any()): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
<div class="card border-0 shadow-sm"><form method="POST" action="<?php echo e(route('security.password.update')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><div class="card-body row g-3"><div class="col-12"><label class="form-label">Current password <span class="text-danger">*</span></label><input class="form-control" type="password" name="current_password" autocomplete="current-password" required></div><div class="col-md-6"><label class="form-label">New password <span class="text-danger">*</span></label><input class="form-control" type="password" name="password" autocomplete="new-password" required><div class="form-text">Use at least 12 characters.</div></div><div class="col-md-6"><label class="form-label">Confirm new password <span class="text-danger">*</span></label><input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required></div></div><div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="fa-solid fa-key me-1"></i>Update password</button></div></form></div>

<section class="card border-0 shadow-sm mt-3" aria-labelledby="passkeysHeading">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2"><div><h2 class="h6 mb-1" id="passkeysHeading">Passkeys</h2><p class="small text-body-secondary mb-0">Use a device fingerprint, face, PIN, or security key for phishing-resistant sign-in.</p></div><button class="btn btn-outline-primary btn-sm d-none" id="addPasskeyButton" type="button"><i class="fa-solid fa-plus me-1"></i>Add passkey</button></div>
    <div class="card-body">
        <div class="alert alert-warning d-none" id="passkeyUnsupported">Passkeys are not supported in this browser or secure context.</div>
        <div class="alert alert-danger d-none" id="passkeyError" role="alert"></div>
        <div class="input-group mb-3 d-none" id="passkeyNameGroup"><label class="input-group-text" for="passkeyName">Name</label><input class="form-control" id="passkeyName" maxlength="255" placeholder="Example: Office laptop"><button class="btn btn-primary" id="registerPasskeyButton" type="button">Register</button><button class="btn btn-outline-secondary" id="cancelPasskeyButton" type="button">Cancel</button></div>
        <?php if($passkeys->isEmpty()): ?><p class="text-body-secondary mb-0">No passkeys registered.</p><?php else: ?>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Name</th><th>Authenticator</th><th>Last used</th><th>Added</th><th class="text-end">Action</th></tr></thead><tbody><?php $__currentLoopData = $passkeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $passkey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($passkey->name); ?></td><td><?php echo e($passkey->authenticator ?: 'Passkey device'); ?></td><td><?php echo e($passkey->last_used_at?->diffForHumans() ?: 'Never'); ?></td><td><?php echo e($passkey->created_at?->toDateString()); ?></td><td class="text-end"><form method="POST" action="<?php echo e(route('passkey.destroy', $passkey)); ?>" onsubmit="return confirm('Remove this passkey?');"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-link btn-sm text-danger text-decoration-none" type="submit"><i class="fa-solid fa-trash-can me-1"></i>Remove</button></form></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div>
        <?php endif; ?>
    </div>
</section>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/passkeys.js'); ?>
<script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">
    window.addEventListener('passkeys:ready', () => {
        const supported = Boolean(window.Passkeys?.isSupported());
        const add = document.getElementById('addPasskeyButton');
        const unsupported = document.getElementById('passkeyUnsupported');
        const group = document.getElementById('passkeyNameGroup');
        const input = document.getElementById('passkeyName');
        const register = document.getElementById('registerPasskeyButton');
        const cancel = document.getElementById('cancelPasskeyButton');
        const error = document.getElementById('passkeyError');
        if (!supported) { unsupported.classList.remove('d-none'); return; }
        add.classList.remove('d-none');
        add.addEventListener('click', () => { group.classList.remove('d-none'); input.focus(); });
        cancel.addEventListener('click', () => { group.classList.add('d-none'); input.value = ''; error.classList.add('d-none'); });
        register.addEventListener('click', async () => {
            const name = input.value.trim();
            if (!name) { input.focus(); return; }
            register.disabled = true;
            error.classList.add('d-none');
            try {
                await window.Passkeys.register({name});
                window.location.reload();
            } catch (exception) {
                if (exception.constructor?.name !== 'UserCancelledError') {
                    error.textContent = exception.message || 'Passkey registration failed.';
                    error.classList.remove('d-none');
                }
            } finally {
                register.disabled = false;
            }
        });
    });
</script>
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
<?php /**PATH D:\www\bizhr\resources\views/settings/security.blade.php ENDPATH**/ ?>