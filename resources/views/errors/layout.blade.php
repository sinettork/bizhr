<!DOCTYPE html>
<html lang="km" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $code }} — BizHR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Siemreap&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            padding: 24px; background: #09090b; color: #fafafa;
            font-family: Siemreap, system-ui, sans-serif;
        }
        .card {
            width: min(100%, 560px); padding: 42px 32px; text-align: center;
            border: 1px solid #27272a; border-radius: 24px; background: #111113;
            box-shadow: 0 24px 70px rgba(0,0,0,.35);
        }
        .logo {
            width: 58px; height: 58px; margin: 0 auto 22px; border-radius: 16px;
            object-fit: cover;
        }
        .code { margin: 0; color: #2563eb; font: 800 64px/1 system-ui, sans-serif; }
        h1 { margin: 18px 0 8px; font-size: 25px; }
        p { margin: 0 auto 28px; color: #a1a1aa; line-height: 1.8; }
        a {
            display: inline-flex; min-height: 44px; align-items: center; justify-content: center;
            padding: 0 22px; border-radius: 12px; color: white; background: #2563eb;
            text-decoration: none; font-weight: 700;
        }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <main class="card">
        <img class="logo" src="{{ asset('images/Artboard 5 copy.png') }}" alt="BizHR">
        <p class="code">{{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">
            {{ auth()->check() ? 'ត្រឡប់ទៅផ្ទាំងគ្រប់គ្រង' : 'ត្រឡប់ទៅចូលប្រើប្រាស់' }}
        </a>
    </main>
</body>
</html>
