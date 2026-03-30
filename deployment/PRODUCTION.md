# IntelliHatch (IHS) — Production Deployment Guide

Stack: **Nginx · MySQL · Redis · PHP-FPM 8.2 · Supervisor · Cron · Cloudflared**

---

## 1. System Packages

```bash
# Core
apt update && apt install -y \
  nginx \
  mysql-server \
  redis-server \
  supervisor \
  git \
  unzip \
  curl

# PHP 8.2 + required extensions
apt install -y \
  php8.4-fpm \
  php8.4-cli \
  php8.4-mysql \
  php8.4-redis \
  php8.4-mbstring \
  php8.4-xml \
  php8.4-bcmath \
  php8.4-curl \
  php8.4-zip \
  php8.4-gd \
  php8.4-intl \
  php8.4-tokenizer \
  php8.4-fileinfo

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

---

## 2. MySQL — Database Setup

```bash
mysql_secure_installation   # follow prompts

mysql -u root -p <<EOF
CREATE DATABASE intellihatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'intellihatch'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON intellihatch.* TO 'intellihatch'@'localhost';
FLUSH PRIVILEGES;
EOF
```

---

## 3. Redis

```bash
# Enable & start
systemctl enable redis-server
systemctl start redis-server

# Verify
redis-cli ping   # should return PONG
```

---

## 4. Application Setup

```bash
cd /var/www/intellihatch

# 4a. Environment file
cp .env.example .env

# Edit .env — minimum required changes:
nano .env
```

**.env production values to set:**

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
API_KEY=<generate a strong key>

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=intellihatch
DB_USERNAME=intellihatch
DB_PASSWORD=STRONG_PASSWORD_HERE

# Switch queue & cache to Redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database     # keep sessions in DB (safe behind proxy)

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

```bash
# 4b. Generate app key
php artisan key:generate

# 4c. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --omit=dev
npm run build

# 4d. Migrate & seed
php artisan migrate --force
php artisan db:seed --class=FormTypeSeeder   # initial form types

# 4e. Storage symlink & permissions
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 4f. Cache configs for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 5. Nginx

```bash
# Copy site config
cp /var/www/intellihatch/deployment/nginx.conf /etc/nginx/sites-available/intellihatch

# Enable site
ln -s /etc/nginx/sites-available/intellihatch /etc/nginx/sites-enabled/intellihatch

# Remove default site (optional)
rm -f /etc/nginx/sites-enabled/default

# Test & reload
nginx -t
systemctl reload nginx
```

---

## 6. Supervisor (Queue Workers)

```bash
# Copy config
cp /var/www/intellihatch/deployment/supervisor.conf /etc/supervisor/conf.d/intellihatch.conf

# Create log directory
mkdir -p /var/log/supervisor

# Load new config
supervisorctl reread
supervisorctl update

# Verify workers are running
supervisorctl status
```

The config starts **2 queue worker processes** pointing at the `redis` driver.
Adjust `numprocs` in `supervisor.conf` based on your server capacity.

---

## 7. Cron (Laravel Scheduler)

```bash
# Add to root or www-data crontab
crontab -u www-data -e
```

Add this single line:

```cron
* * * * * php /var/www/intellihatch/artisan schedule:run >> /dev/null 2>&1
```

This triggers Laravel's scheduler every minute. The app currently runs:
- `app:cleanup-orphaned-photos` — **every hour** (cleans up temp/orphaned photos)

---

## 8. Cloudflared Tunnel

```bash
# Install cloudflared
curl -L --output cloudflared.deb \
  https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
dpkg -i cloudflared.deb

# Authenticate (opens browser)
cloudflared tunnel login

# Create tunnel
cloudflared tunnel create intellihatch
# Note the Tunnel ID shown in output

# Route DNS (replace with your domain)
cloudflared tunnel route dns intellihatch your-domain.com

# Edit the config — fill in Tunnel ID and domain
cp /var/www/intellihatch/deployment/cloudflared.yml ~/.cloudflared/config.yml
nano ~/.cloudflared/config.yml

# Install as system service
cloudflared service install

# Start
systemctl enable cloudflared
systemctl start cloudflared

# Verify tunnel is active
cloudflared tunnel info intellihatch
```

---

## 9. php.ini Tweaks (optional but recommended)

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
upload_max_filesize = 64M
post_max_size       = 64M
max_execution_time  = 300
memory_limit        = 256M
```

Then restart PHP-FPM:

```bash
systemctl restart php8.4-fpm
```

---

## 10. Ongoing Deployments

```bash
bash /var/www/intellihatch/deployment/deploy.sh
```

The script handles: `git pull → composer → npm build → migrate → cache → permissions → queue restart → nginx reload`.

---

## Service Summary

| Service       | Command                          |
|---------------|----------------------------------|
| Nginx         | `systemctl status nginx`         |
| PHP-FPM       | `systemctl status php8.4-fpm`    |
| MySQL         | `systemctl status mysql`         |
| Redis         | `systemctl status redis-server`  |
| Supervisor    | `supervisorctl status`           |
| Cloudflared   | `systemctl status cloudflared`   |
| Queue workers | `supervisorctl status intellihatch-worker:*` |

---

## Quick Troubleshooting

| Symptom | Check |
|---------|-------|
| 500 errors | `tail -n 50 /var/www/intellihatch/storage/logs/laravel.log` |
| Jobs not processing | `supervisorctl status` · `redis-cli llen queues:default` |
| Scheduler not running | `crontab -u www-data -l` · `php artisan schedule:list` |
| Tunnel offline | `systemctl status cloudflared` · `cloudflared tunnel info intellihatch` |
| Upload failures | Check `client_max_body_size` in nginx.conf and `upload_max_filesize` in php.ini |
