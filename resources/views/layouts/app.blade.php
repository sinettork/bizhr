<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head', ['title' => $title ?? null])</head>
    <body class="app-body" hx-history="false">
    @include('partials.navigation')
    <main class="app-workspace px-4 px-lg-5 pt-3 pb-4">{{ $slot }}</main>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.appTablePreferences = @json(auth()->user()?->table_preferences ?? []); window.tablePreferenceUrl = @json(route('preferences.table-columns.update'));</script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/htmx/htmx.min.js') }}"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">htmx.config.historyCacheSize = 0; htmx.config.allowScriptTags = false; htmx.config.selfRequestsOnly = true; htmx.config.timeout = 15000;</script>
    <script src="{{ asset('js/app.js') }}"></script>
    @if(session('open_modal'))<script nonce="{{ request()->attributes->get('csp_nonce') }}">document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById(@json(session('open_modal')))).show());</script>@endif
    @stack('scripts')
</body>
</html>
