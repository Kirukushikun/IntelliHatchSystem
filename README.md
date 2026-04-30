# IntelliHatchSystem (IHS)

Intelligence and Insights from hatchery inputs with the help of AI.

## Overview

IntelliHatchSystem is a web-based hatchery monitoring and management platform built with Laravel. It supports 15 different monitoring form types, machine/registry management, AI-powered analytics, and a comprehensive audit trail.

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Livewire 4, Alpine.js 3, Tailwind CSS 4
- **Charts:** Chart.js
- **Database:** MySQL
- **AI:** OpenRouter API (GPT-4o)
- **Build:** Vite 7

## Features

### Form Monitoring (15 Types)

1. Incubator Routine Checklist Per Shift
2. Hatcher Blower Air Speed Monitoring
3. Incubator Blower Air Speed Monitoring
4. Hatchery Sullair Air Compressor Weekly PMS Checklist
5. Hatcher Machine Accuracy Temperature Checking
6. Incubator Machine Accuracy Temperature Checking
7. Plenum Temperature and Humidity Monitoring
8. Entrance Damper Spacing Monitoring
9. Incubator Entrance Temperature Monitoring
10. Incubator Temperature Calibration
11. Hatcher Temperature Calibration
12. PASGAR Score
13. Incubator Rack Preventive Maintenance Checklist
14. Weekly Voltage and Ampere Monitoring
15. Hatchery Diesel Generator Weekly Maintenance Checklist

- Public form submission (no login required)
- Photo uploads with server-side compression
- Per-form-type dashboards with charts and filtering
- Print-ready views via signed URLs
- CSV bulk import (superadmin)

### Machine & Registry Management

- Incubator, Hatcher, and Plenum machine registries
- PS Number, House Number, and GetSet registries
- Full CRUD with enable/disable toggle per item

### Tagging System

- User categorization tags (Hatcheryman, Maintenance, QA/QC)
- Form type tagging for classification and filtering
- Tag-based user and form type associations

### AI Chat & Insights

- AI-powered chat with form data context (single or multi-form-type)
- Configurable date range filtering (week, month, all, custom)
- System prompt management (superadmin)
- AI-generated insights per form type
- Async processing via queue jobs

### User Roles

| Role | user_type | Access |
|------|-----------|--------|
| Superadmin | 0 | Full access + system config, admin management, activity logs, danger zone |
| Admin | 1 | Form management, dashboards, insights, AI chat, user management |
| Hatchery User | 2 | Form submission, machine/registry views |

### Administration

- User and admin management with password reset
- Activity log viewer with search, filtering, and CSV/PDF export
- Form type management with descriptions, impact levels, usage frequency, and activation toggle
- User activity dashboard (superadmin)
- Danger zone: bulk photo purge, form wipe, log purge (superadmin)

### Backup

- Google Drive integration via `spatie/laravel-backup`
- Automated backup with configurable Google Drive folder

### API

- Form statistics with date filtering
- Form type listing
- Webhook integration for external systems
- Protected by `x-api-key` header authentication

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL

### Installation

```bash
git clone <repo-url>
cd IntelliHatchSystem
cp .env.example .env
composer run setup
```

### Development

```bash
composer run dev   # Starts: server, queue worker, log tail, Vite
```

This runs concurrently:
- `php artisan serve` — Laravel dev server
- `php artisan queue:listen` — Queue worker for async jobs
- `php artisan pail` — Real-time log tail
- `npm run dev` — Vite HMR

### Environment Variables

```
APP_NAME=IHS
DB_CONNECTION=mysql
API_KEY=                   # x-api-key header auth
OPENROUTER_API_KEY=        # OpenRouter AI integration
WEBHOOK_URL=               # External webhook destination
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
GOOGLE_DRIVE_CLIENT_ID=    # Google Drive backup
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
ADMIN_NOTIFICATION_EMAIL=  # Admin notification recipient
```

### Testing

```bash
php artisan test       # Run PHPUnit tests
./vendor/bin/pint      # Fix code style
```

### Seeding

```bash
php artisan db:seed                    # Run all seeders (users, form types, tags, machines, hatchery users)
php artisan db:seed --class=TestSeeder # Generate comprehensive test data for all 15 form types
```

### Docker

```bash
docker-compose up -d
# or
./vendor/bin/sail up
```

## Troubleshooting

### Photo Upload 401 Error (WSL2 / Proxy)

If photo uploads fail with a 401 Unauthorized / CORS error, add the following to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
})
```

## License

MIT
