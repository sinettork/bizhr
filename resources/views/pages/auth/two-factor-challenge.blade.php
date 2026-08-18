<x-layouts::auth title="Two-factor authentication">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Two-factor authentication</h1><p class="text-body-secondary mb-0">Enter the code from your authenticator app or a recovery code.</p></div>
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('two-factor.login') }}">@csrf
        <div class="mb-3"><label class="form-label" for="code">Authentication code</label><input class="form-control" id="code" name="code" inputmode="numeric" autocomplete="one-time-code"></div>
        <div class="mb-4"><label class="form-label" for="recovery_code">Recovery code <span class="text-body-secondary">(optional)</span></label><input class="form-control" id="recovery_code" name="recovery_code" autocomplete="one-time-code"></div>
        <button class="btn btn-primary w-100" type="submit">Continue</button>
    </form>
</x-layouts::auth>
