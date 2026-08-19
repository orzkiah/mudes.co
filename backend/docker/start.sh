#!/bin/sh
set -e

echo "==> Starting Mudes.co Backend..."
echo "==> APP_ENV: ${APP_ENV:-not set}"
echo "==> DB_CONNECTION: ${DB_CONNECTION:-not set}"
echo "==> DB_HOST: ${DB_HOST:-not set}"
echo "==> SESSION_DRIVER: ${SESSION_DRIVER:-not set}"
echo "==> CACHE_STORE: ${CACHE_STORE:-not set}"

# Clear any stale caches from build step
php artisan config:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true

# Now cache with runtime env vars
php artisan config:cache
php artisan route:cache

# Run migrations
echo "==> Running migrations..."
php artisan migrate --force

# Seed roles and admin user (idempotent - uses firstOrCreate)
echo "==> Seeding roles and admin user..."
php artisan db:seed --class=RoleSeeder --force 2>/dev/null || echo "==> RoleSeeder skipped"
php artisan db:seed --class=UserSeeder --force 2>/dev/null || echo "==> UserSeeder skipped"

echo "==> Starting PHP server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
