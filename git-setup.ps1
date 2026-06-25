Set-Location "C:\Users\jomea\Restaurant Pos Kit"

Write-Host "Killing any leftover git processes..." -ForegroundColor Cyan
Get-Process -Name git -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

Write-Host "Removing old .git directory (if any)..." -ForegroundColor Cyan
Remove-Item -Recurse -Force ".git" -ErrorAction SilentlyContinue
Remove-Item -Recurse -Force "backend\.git" -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1

Write-Host "Initializing fresh git repo..." -ForegroundColor Cyan
git init
git add --all
git commit -m "Initial commit: Restaurant POS Kit"

Write-Host "Creating GitHub repo..." -ForegroundColor Cyan
$repoUrl = gh repo create restaurant-pos-kit --public --push --source="." --remote=origin 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "Done! Repo: $repoUrl" -ForegroundColor Green
} else {
    Write-Host "gh CLI not available. Push manually:" -ForegroundColor Yellow
    Write-Host "  git remote add origin https://github.com/YOUR_USERNAME/restaurant-pos-kit.git"
    Write-Host "  git branch -M main"
    Write-Host "  git push -u origin main"
}
