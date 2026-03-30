#!/usr/bin/env bash
# =============================================================================
# IntelliHatch (IHS) — Production Deployment Script
# Usage: bash deployment/deploy.sh
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/intellihatch"
PHP="php8.4"
COMPOSER="composer"

echo "==> [1/8] Pulling latest code..."
git -C "$APP_DIR" pull origin main

echo "==> [2/8] Installing PHP dependencies (production)..."
$COMPOSER install --no-dev --optimize-autoloader --working-dir="$APP_DIR"

echo "==> [3/8] Installing & building frontend assets..."
cd "$APP_DIR"
npm ci --omit=dev
npm run build

echo "==> [4/8] Running database migrations..."
$PHP "$APP_DIR/artisan" migrate --force

echo "==> [5/8] Clearing & warming caches..."
$PHP "$APP_DIR/artisan" config:cache
$PHP "$APP_DIR/artisan" route:cache
$PHP "$APP_DIR/artisan" view:cache
$PHP "$APP_DIR/artisan" event:cache

echo "==> [6/8] Fixing storage permissions..."
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "==> [7/8] Restarting queue workers..."
$PHP "$APP_DIR/artisan" queue:restart
supervisorctl restart intellihatch-worker:*

echo "==> [8/8] Reloading nginx & PHP-FPM..."
nginx -t && systemctl reload nginx
systemctl reload php8.4-fpm

echo ""
echo "Deployment complete."
