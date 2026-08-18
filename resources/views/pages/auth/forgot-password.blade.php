<x-layouts::auth title="Forgot password">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Reset your password</h1><p class="text-body-secondary mb-0">Enter your email and we’ll send a reset link.</p></div>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <div class="mb-4"><label class="form-label" for="email">Email address <span class="text-danger">*</span></label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus></div>
        <button class="btn btn-primary w-100" type="submit">Email password reset link</button>
    </form>
    <p class="text-center mt-3 mb-0"><a href="{{ route('login') }}">Back to sign in</a></p>
</x-layouts::auth>
