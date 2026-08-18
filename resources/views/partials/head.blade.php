<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#243a8f">
<title>{{ filled($title ?? null) ? $title.' · '.config('app.name', 'BizHR') : config('app.name', 'BizHR') }}</title>
<link rel="icon" href="{{ asset('images/Artboard 5 copy.png') }}" type="image/png">
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{ asset('css/status.css') }}" rel="stylesheet">
<link href="{{ asset('css/app-shell.css') }}" rel="stylesheet">
