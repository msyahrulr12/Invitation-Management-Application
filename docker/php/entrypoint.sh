#!/bin/bash
set -e

# ==============================================================================
# Docker Entrypoint — Invitation Management Application
# Handles Laravel initialization before starting PHP-FPM
# ==============================================================================

echo "============================================="
echo "  Starting Invitation Management Application"
echo "============================================="

# ── Wait for PostgreSQL ──────────────────────────────────────────────────────
echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."

MAX_RETRIES=30
RETRY_COUNT=0

while ! php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            [PDO::ATTR_TIMEOUT => 3]
        );
        echo 'connected';
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ "$RETRY_COUNT" -ge "$MAX_RETRIES" ]; then
        echo "[entrypoint] ERROR: Could not connect to PostgreSQL after ${MAX_RETRIES} attempts."
        echo "[entrypoint] Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD."
        exit 1
    fi
    echo "[entrypoint] PostgreSQL not ready yet... (attempt ${RETRY_COUNT}/${MAX_RETRIES})"
    sleep 2
done

echo "[entrypoint] PostgreSQL is ready."

# ── Ensure Storage Directories Exist ─────────────────────────────────────────
echo "[entrypoint] Ensuring storage directories exist..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p storage/app/private
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ── Set Permissions ──────────────────────────────────────────────────────────
echo "[entrypoint] Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ── Generate App Key (if not set) ────────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "[entrypoint] Generating application key..."
    php artisan key:generate --force --no-interaction
fi

# ── Run Database Migrations ──────────────────────────────────────────────────
echo "[entrypoint] Running database migrations..."
php artisan migrate --force --no-interaction

# ── Create Storage Link ──────────────────────────────────────────────────────
if [ ! -L "public/storage" ]; then
    echo "[entrypoint] Creating storage symlink..."
    php artisan storage:link --no-interaction
fi

# ── Optimize Application ────────────────────────────────────────────────────
echo "[entrypoint] Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "============================================="
echo "  Application Ready — Starting PHP-FPM"
echo "============================================="

# ── Start PHP-FPM ────────────────────────────────────────────────────────────
exec php-fpm
