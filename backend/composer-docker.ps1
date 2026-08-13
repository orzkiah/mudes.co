<#
PowerShell helper: Run composer install inside a PHP 8.4 Docker container.
Usage:
  From backend folder (PowerShell):
    .\composer-docker.ps1
  Or pass composer args:
    .\composer-docker.ps1 "install --no-dev --optimize-autoloader"

This avoids changing host PHP version. Requires Docker Desktop / Docker Engine installed and running.
#>

param(
    [string]$ComposerArgs = "install --no-interaction --prefer-dist"
)

function Abort([string]$msg){
    Write-Host "ERROR: $msg" -ForegroundColor Red
    exit 1
}

# Ensure script is executed from repository backend folder
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
Set-Location $scriptDir

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Abort "Docker is not installed or not in PATH. Please install Docker Desktop and ensure it's running."
}

Write-Host "Running composer inside php:8.4 container using Docker..." -ForegroundColor Cyan

# Use current directory path for volume mount (Docker will translate Windows paths)
$pwdPath = (Get-Location).Path

# Build docker command. Use php:8.4-cli image, install composer and run composer with provided args.
$dockerCmd = @"
set -euo pipefail
apt-get update -y >/dev/null
apt-get install -y unzip git curl >/dev/null
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer $ComposerArgs
"@

# Run container (will run as root inside container)
# Use Start-Process so output streams through to host console
$proc = Start-Process -FilePath docker -ArgumentList @("run","--rm","-v","${pwdPath}:/app","-w","/app","php:8.4-cli","bash","-lc",$dockerCmd) -NoNewWindow -Wait -PassThru

if ($proc.ExitCode -ne 0) {
    Abort "Composer inside container failed. Check the output above for error details."
}

Write-Host "Composer finished successfully inside Docker container." -ForegroundColor Green
Write-Host "You can now run 'php artisan serve --host=127.0.0.1 --port=8000' inside your environment (or use Sail/docker-compose if configured)." -ForegroundColor Yellow
