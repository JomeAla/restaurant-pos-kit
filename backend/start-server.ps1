Start-Process -FilePath "php" -ArgumentList "artisan serve --port=8000" -WindowStyle Normal -WorkingDirectory "$PSScriptRoot"
Write-Host "Server starting on http://localhost:8000"
