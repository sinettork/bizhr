<x-layouts::auth title="Confirm password">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Confirm your password</h1><p class="text-body-secondary mb-0">This action needs an extra security check.</p></div>
    <form method="POST" action="{{ route('password.confirm.store') }}">@csrf
        <div class="mb-4"><label class="form-label" for="password">Password <span class="text-danger">*</span></label><input class="form-control" id="password" name="password" type="password" required autofocus autocomplete="current-password"></div>
        <button class="btn btn-primary w-100" type="submit">Confirm</button>
    </form>
</x-layouts::auth>
