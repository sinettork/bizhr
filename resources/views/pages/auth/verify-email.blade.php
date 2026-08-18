<x-layouts::auth title="Verify email">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Verify your email</h1><p class="text-body-secondary mb-0">Check your inbox and follow the verification link.</p></div>
    @if (session('status') === 'verification-link-sent')<div class="alert alert-success">A new verification link has been sent.</div>@endif
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary w-100" type="submit">Resend verification email</button></form>
    <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">@csrf<button class="btn btn-link text-danger" type="submit">Sign out</button></form>
</x-layouts::auth>
