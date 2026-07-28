$ErrorActionPreference = 'Stop'

Write-Host 'Installing PHP dependencies...'
composer install

Write-Host 'Installing frontend dependencies...'
npm ci

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    php artisan key:generate
}

Write-Host 'Clearing stale Laravel, Blade, Flux, and Livewire caches...'
php artisan optimize:clear

if (-not (Test-Path 'public\storage')) {
    php artisan storage:link
}

Write-Host 'Running database migrations...'
php artisan migrate

Write-Host 'Building the original BizHR frontend...'
npm run build

Write-Host ''
Write-Host 'Setup complete. Start with: php artisan serve'
Write-Host 'Then open: http://127.0.0.1:8000'
