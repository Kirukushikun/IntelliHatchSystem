#!/usr/bin/env bash
# =============================================================================
# IntelliHatch (IHS) — Production Deployment Script
# Usage: bash deployment/deploy.sh
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/intellihatch"
PHP="php8.4"
COMPOSER="composer"

# Pull first, then restart this script from the freshly pulled copy — otherwise
# bash keeps running the old version it already read, and fixes to this file
# only take effect on the deploy after next.
if [ "${IHS_DEPLOY_PULLED:-0}" != "1" ]; then
    echo "==> [1/8] Pulling latest code..."
    git -C "$APP_DIR" pull origin main
    IHS_DEPLOY_PULLED=1 exec bash "$APP_DIR/deployment/deploy.sh" "$@"
fi

echo "==> [2/8] Installing PHP dependencies (production)..."
$COMPOSER install --no-dev --optimize-autoloader --working-dir="$APP_DIR"

echo "==> [3/8] Installing & building frontend assets..."
cd "$APP_DIR"
# Vite/Tailwind are devDependencies — they're needed to build, so install them,
# build, then drop them again.
# --include=dev: plain "npm ci" also skips them when NODE_ENV=production is set.
npm ci --include=dev
npm run build
npm prune --omit=dev

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

echo "==> [6b/8] Installing PHP overrides (upload limits)..."
for SAPI in fpm cli; do
    install -m 644 "$APP_DIR/deployment/php-ihs.ini" "/etc/php/8.4/$SAPI/conf.d/99-ihs.ini"
done

echo "==> [7/8] Restarting queue workers..."
$PHP "$APP_DIR/artisan" queue:restart
supervisorctl restart intellihatch-worker:*

echo "==> [8/8] Reloading nginx & PHP-FPM..."
nginx -t && systemctl reload nginx
systemctl reload php8.4-fpm

echo ""
echo "Deployment complete."
