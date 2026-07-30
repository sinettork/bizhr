<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#2b68f6" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'BizHR') : config('app.name', 'BizHR') }}
</title>

<link
    rel="icon"
    href="{{ asset('images/Artboard 5 copy.png') }}"
    type="image/png"
>
<link
    rel="apple-touch-icon"
    href="{{ asset('images/Artboard 5 copy.png') }}"
>

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
