<?php if (isset($component)) { $__componentOriginal08b8a564843783787e0bee3357e24f38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal08b8a564843783787e0bee3357e24f38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'layouts.auth','data' => ['title' => 'Sign in']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sign in']); ?>
    <div class="text-center mb-4"><h1 class="h3 mb-2">Welcome back</h1><p class="text-body-secondary mb-0">Sign in to your BizHR account.</p></div>
    <?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if(session('inactive_account')): ?><div class="alert alert-danger"><?php echo e(session('inactive_account')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
    <form method="POST" action="<?php echo e(route('login.store')); ?>"><?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label" for="email">Email address <span class="text-danger">*</span></label><input class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" value="<?php echo e(old('email')); ?>" type="email" autocomplete="email webauthn" required autofocus></div>
        <div class="mb-3"><div class="d-flex justify-content-between"><label class="form-label" for="password">Password</label><?php if(Route::has('password.request')): ?><a class="small" href="<?php echo e(route('password.request')); ?>">Forgot password?</a><?php endif; ?></div><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
        <div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign in</button>
    </form>
    <div id="passkeyLogin" class="d-none">
        <div class="d-flex align-items-center gap-2 my-4"><hr class="flex-grow-1"><span class="small text-body-secondary">or</span><hr class="flex-grow-1"></div>
        <button class="btn btn-outline-primary w-100" id="passkeyLoginButton" type="button"><i class="fa-solid fa-fingerprint me-2"></i>Sign in with passkey</button>
        <div class="alert alert-danger mt-3 mb-0 d-none" id="passkeyLoginError" role="alert"></div>
    </div>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/passkeys.js'); ?>
    <script nonce="<?php echo e(request()->attributes->get('csp_nonce')); ?>">
        window.addEventListener('passkeys:ready', () => {
            if (!window.Passkeys?.isSupported()) return;
            const container = document.getElementById('passkeyLogin');
            const button = document.getElementById('passkeyLoginButton');
            const error = document.getElementById('passkeyLoginError');
            container.classList.remove('d-none');
            button.addEventListener('click', async () => {
                button.disabled = true;
                error.classList.add('d-none');
                try {
                    const response = await window.Passkeys.verify();
                    window.location.assign(response.redirect || <?php echo json_encode(route('dashboard'), 15, 512) ?>);
                } catch (exception) {
                    if (exception.constructor?.name !== 'UserCancelledError') {
                        error.textContent = exception.message || 'Passkey sign-in failed.';
                        error.classList.remove('d-none');
                    }
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
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
<?php /**PATH D:\www\bizhr\resources\views\pages\auth\login.blade.php ENDPATH**/ ?>