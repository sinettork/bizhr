<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head', ['title' => $title ?? null])</head>
<body class="auth-body">
    <main class="container d-flex align-items-center min-vh-100"><div class="row justify-content-center w-100"><div class="col-sm-10 col-md-7 col-lg-5"><div class="card shadow-lg border-0"><div class="card-body p-4 p-md-5"><div class="auth-brand text-center mb-4"><span class="brand-mark"><i class="fa-solid fa-people-group"></i></span><span class="fs-4 fw-bold ms-2">{{ config('app.name', 'BizHR') }}</span></div>{{ $slot }}</div></div></div></div></main>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
