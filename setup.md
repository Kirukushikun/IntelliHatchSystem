# Setup Guide

## System Requirements

- **PHP**: 8.2+
- **Composer**: latest stable
- **Node.js**: 18+ (recommended)
- **npm**: comes with Node.js
- **Database**:
  - SQLite (default in `.env.example`), or
  - MySQL / MariaDB, or
  - PostgreSQL

## Installation

### 1) Install PHP dependencies

```bash
composer install
```

### 2) Create your environment file

```bash
cp .env.example .env
```

Then update values as needed (at minimum):

- `APP_URL`
- `DB_CONNECTION` (and DB credentials if not using SQLite)
- `API_KEY` (used by `api.key` middleware)
- `WEBHOOK_URL` (if your environment uses webhooks)

### 3) Generate the application key

```bash
php artisan key:generate
```

### 4) Run migrations (and seed if needed)

```bash
php artisan migrate
```

If you want sample/initial data:

```bash
php artisan db:seed
```
```bash
php artisan db:seed --class=FormTypeSeeder
```
```bash
php artisan form:reset
```

If you need to customize initial data, edit:

- `database/seeders/DatabaseSeeder.php`
- `database/seeders/FormTypeSeeder.php`

### 5) Install dependencies 

```bash
npm install
```
```bash
composer install
```

### 6) Storage symlink

```bash
php artisan storage:link
```

### 7) Build frontend assets

```bash
npm run build
```

## Technology Stack

- **Backend**: Laravel 12
- **UI / Reactive**: Livewire 4
- **Frontend tooling**: Vite + Tailwind CSS
- **JS**: Alpine.js

## Common Notes

### API Key middleware

Routes protected by `api.key` middleware require `API_KEY` to be set in `.env`.

### Proxy / Upload issues

If you see `401 Unauthorized` related to proxy / CORS-style upload behavior, this repo has previously fixed it by trusting proxies in `bootstrap/app.php`:
```php
$middleware->trustProxies(at: '*');
```