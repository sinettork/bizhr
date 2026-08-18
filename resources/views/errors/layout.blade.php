<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $code }} — BizHR</title>
    <style>
        :root {
            color-scheme: light;
            --page: #f4f4f5; --card: #fff; --border: #e4e4e7;
            --text: #18181b; --muted: #71717a;
        }
        :root.dark {
            color-scheme: dark;
            --page: #09090b; --card: #111113; --border: #27272a;
            --text: #fafafa; --muted: #a1a1aa;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            padding: 24px; background: var(--page); color: var(--text);
            font-family: system-ui, "Segoe UI", sans-serif;
        }
        .card {
            width: min(100%, 560px); padding: 42px 32px; text-align: center;
            border: 1px solid var(--border); border-radius: 24px; background: var(--card);
            box-shadow: 0 24px 70px rgba(0,0,0,.35);
        }
        .logo {
            width: 58px; height: 58px; margin: 0 auto 22px; border-radius: 16px;
            object-fit: cover;
        }
        .code { margin: 0; color: #2563eb; font: 800 64px/1 system-ui, sans-serif; }
        h1 { margin: 18px 0 8px; font-size: 25px; }
        p { margin: 0 auto 28px; color: var(--muted); line-height: 1.8; }
        a {
            display: inline-flex; min-height: 44px; align-items: center; justify-content: center;
            padding: 0 22px; border-radius: 12px; color: white; background: #2563eb;
            text-decoration: none; font-weight: 700;
        }
        a:hover { background: #1d4ed8; }
    </style>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const savedTheme = localStorage.getItem('bizhr.appearance');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', savedTheme === 'dark' || (!savedTheme && prefersDark));
    </script>
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
