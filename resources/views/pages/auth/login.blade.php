<x-layouts::auth title="Sign in">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Welcome back</h1><p class="text-body-secondary mb-0">Sign in to your BizHR account.</p></div>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if (session('inactive_account'))<div class="alert alert-danger">{{ session('inactive_account') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('login.store') }}">@csrf
        <div class="mb-3"><label class="form-label" for="email">Email address <span class="text-danger">*</span></label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" type="email" autocomplete="email webauthn" required autofocus></div>
        <div class="mb-3"><div class="d-flex justify-content-between"><label class="form-label" for="password">Password</label>@if (Route::has('password.request'))<a class="small" href="{{ route('password.request') }}">Forgot password?</a>@endif</div><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
        <div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-right-to-bracket me-2"></i>Sign in</button>
    </form>
    <div id="passkeyLogin" class="d-none">
        <div class="d-flex align-items-center gap-2 my-4"><hr class="flex-grow-1"><span class="small text-body-secondary">or</span><hr class="flex-grow-1"></div>
        <button class="btn btn-outline-primary w-100" id="passkeyLoginButton" type="button"><i class="fa-solid fa-fingerprint me-2"></i>Sign in with passkey</button>
        <div class="alert alert-danger mt-3 mb-0 d-none" id="passkeyLoginError" role="alert"></div>
    </div>
    @vite('resources/js/passkeys.js')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
                    window.location.assign(response.redirect || @json(route('dashboard')));
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
</x-layouts::auth>
