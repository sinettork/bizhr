<x-layouts::auth title="Reset password">
    <div class="text-center mb-4"><h1 class="h3 mb-2">Choose a new password</h1></div>
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus></div>
        <div class="mb-3"><label class="form-label" for="password">New password <span class="text-danger">*</span></label><input class="form-control" id="password" name="password" type="password" required autocomplete="new-password"></div>
        <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm password <span class="text-danger">*</span></label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
        <button class="btn btn-primary w-100" type="submit">Reset password</button>
    </form>
</x-layouts::auth>
