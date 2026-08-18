$ErrorActionPreference = 'Stop'

php artisan optimize:clear

$paths = @(
    'storage\framework\views\*',
    'storage\framework\cache\data\*',
    'storage\framework\sessions\*',
    'storage\logs\*.log',
    'public\hot'
)

foreach ($path in $paths) {
    Remove-Item $path -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Host 'Runtime caches cleared. Your UX/UI source files were not changed.'
