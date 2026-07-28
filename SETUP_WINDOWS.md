# BizHR — Windows setup

This archive keeps the original BizHR UX/UI source and compiled frontend assets, but removes dependencies and runtime caches.

## First run (PowerShell)

```powershell
cd D:\www\bizhr
composer install
npm ci
php artisan optimize:clear
php artisan storage:link
php artisan migrate
npm run build
php artisan serve
```

Open: http://127.0.0.1:8000

If `public\storage` already exists, the `storage:link` command may say it already exists; that is safe.

## Development mode

Use two terminals:

```powershell
php artisan serve
```

```powershell
npm run dev
```

## Important

Do not copy these folders from an older project directory:

- `storage/framework/views`
- `storage/framework/cache/data`
- `storage/framework/sessions`
- `bootstrap/cache`
- `public/hot`

They are generated automatically and can contain absolute paths from the old folder.
