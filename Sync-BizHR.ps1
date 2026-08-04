# $localProject = "D:\www\bizhr"
# $driveProject = "H:\My Drive\bizhr"

# $excludedDirectories = @(
#     ".git",
#     "vendor",
#     "node_modules",
#     "storage",
#     "bootstrap\cache"
# )

# $excludedFiles = @(
#     ".env",
#     "database.sqlite",
#     "hot"
# )

# # Vite's public\hot is machine-specific and must never be synchronized.
# Remove-Item "$localProject\public\hot" -Force -ErrorAction SilentlyContinue
# Remove-Item "$driveProject\public\hot" -Force -ErrorAction SilentlyContinue

# Write-Host "1. Google Drive -> Local" -ForegroundColor Cyan

# robocopy $driveProject $localProject /E /XO /FFT /R:2 /W:2 `
#     /XD $excludedDirectories `
#     /XF $excludedFiles

# Write-Host "2. Local -> Google Drive" -ForegroundColor Cyan

# robocopy $localProject $driveProject /E /XO /FFT /R:2 /W:2 `
#     /XD $excludedDirectories `
#     /XF $excludedFiles

# Write-Host ""
# Write-Host "BizHR synchronization completed." -ForegroundColor Green
