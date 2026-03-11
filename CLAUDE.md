# IntelliHatchSystem — CLAUDE.md

> AI reference guide for the IntelliHatchSystem (IHS) Laravel project.

## Project Overview

**IntelliHatchSystem (IHS)** — Intelligence and insights from hatchery inputs with AI assistance.

- **Framework:** Laravel 12.0 (PHP ^8.2)
- **Frontend:** Livewire 4, Alpine.js 3, Tailwind CSS 4
- **Database:** MySQL (sessions, cache, queue all via database driver)
- **AI:** OpenRouter API (GPT-4o) via `app/Services/OpenRouterClient.php`
- **Build Tool:** Vite 7

## Development Commands

```bash
composer run setup   # Full setup (install, migrate, seed, etc.)
npm run dev          # Concurrent: Vite + queue worker + log tail
php artisan test     # Run PHPUnit tests
./vendor/bin/pint    # Fix code style
```

## User Roles & Auth

| user_type | Role | Middleware | Prefix |
|-----------|------|-----------|--------|
| 0 | Admin | `admin` | `/admin/*` |
| 1 | Hatchery User | `user` | `/user/*` |

- Login rate limit: 5 attempts → 10-minute lockout
- Sessions stored in `sessions` DB table
- API auth via `x-api-key` header (`ApiKeyMiddleware`)

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Both admin and hatchery users |
| `form_types` | 4 form type definitions |
| `forms` | Submitted forms (JSON inputs) |
| `photos` | Uploaded photos (with disk/path) |
| `incubator-machines` | Incubator machine registry |
| `hatcher-machines` | Hatcher machine registry |
| `plenum-machines` | Plenum machine registry |
| `sessions`, `cache`, `jobs` | Laravel infrastructure |

## Form Types (4)

1. **Incubator Routine** — `form_type_id` = incubator routine type
2. **Blower Air Hatcher** — Hatcher blower/air monitoring
3. **Blower Air Incubator** — Incubator blower/air monitoring
4. **Hatchery Sullair** — Sullair compressor monitoring

Forms store all inputs as JSON in `forms.form_inputs`. The `Form` model has a `getMachineInfoAttribute()` accessor to extract machine info from that JSON.

## Key Models

```
User           → user_type (0=admin, 1=user), is_disabled, username
Form           → belongsTo(FormType), belongsTo(User via uploaded_by), form_inputs (JSON)
FormType       → hasMany(Form), form_name (unique)
Incubator      → table: incubator-machines, incubatorName, isActive
Hatcher        → table: hatcher-machines, hatcherName, isActive
Plenum         → table: plenum-machines, plenumName, isActive
```

## Controllers

| Controller | Responsibility |
|-----------|----------------|
| `LoginController` | Auth: login/logout with rate limiting |
| `FormController` | CRUD for form submissions |
| `DashboardController` | Dashboard views per form type |
| `InsightsController` | AI-generated insights per form type |
| `FormsPrintController` | Print-ready views (signed URLs) |
| `UserController` | User CRUD + password change |
| `IncubatorController` | Incubator machine management |
| `HatcherController` | Hatcher machine management |
| `PlenumController` | Plenum machine management |
| `FormStatsController` | API: form statistics with date filtering |
| `WebhookController` | API: send forms to external webhook |

## Livewire Component Structure

```
app/Livewire/
├── Admin/
│   ├── DashboardStats
│   ├── InsightsIndex / InsightsDetail
│   └── UserManagement/ (Display, Create, Edit, Delete, Disable, ResetPassword)
├── Shared/
│   ├── Forms/ (IncubatorRoutineForm, BlowerAirHatcherForm, BlowerAirIncubatorForm, HatcherySullairForm)
│   ├── FormsDashboard/ (per form type)
│   └── Management/ (Incubator/Hatcher/Plenum — Display, Create, Edit, Delete, Disable)
├── Components/
│   └── FormNavigation
├── Configs/
│   └── (IncubatorRoutineConfig, BlowerAirHatcherConfig, BlowerAirIncubatorConfig, HatcherySullairConfig)
└── Auth/
    └── ChangePassword
```

## Routes Summary

- `GET /` — Landing page
- `GET /login`, `POST /login`, `POST /logout` — Auth
- `GET /forms/{type}` — Public form submission (no auth)
- `/admin/*` — Admin area (admin middleware)
- `/user/*` — User area (user middleware)
- `/api/*` — API (ApiKeyMiddleware + throttle:60,1)
- Print routes use **signed URLs** (`/admin/print/*`)

## API Endpoints

```
GET  /api/form-stats          # Form stats; filter: form_type_id, date_filter, date_from/to
GET  /api/form-types          # List form types
POST /api/webhook/send-form   # Send single form to webhook
POST /api/webhook/send-multiple # Send multiple forms to webhook
```

Header required: `x-api-key: {API_KEY from .env}`

## AI Service

`app/Services/OpenRouterClient.php`
- Model: `gpt-4o` (default)
- Retries: up to 3x with exponential backoff
- Retryable codes: 429, 500, 502, 503, 504
- Key method: `ask(string $userMessage, string $systemPrompt, string $model)`
- Config key: `services.openrouter.key` (env: `OPENROUTER_API_KEY`)

## Key .env Variables

```
APP_NAME=IHS
DB_CONNECTION=mysql
API_KEY=                   # Used for x-api-key header auth
OPENROUTER_API_KEY=        # OpenRouter AI integration
WEBHOOK_URL=               # External webhook destination
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Middleware

| Alias | Class | Purpose |
|-------|-------|---------|
| `admin` | AdminMiddleware | user_type === 0 |
| `user` | UserMiddleware | user_type === 1 |
| `api.key` | ApiKeyMiddleware | x-api-key header validation |

Proxy trust level set to `*` in `bootstrap/app.php`.

## Custom Artisan Commands

- `CleanupOrphanedPhotos` — Remove photos without DB references
- `DropAndReseedFormTypes` — Reset and reseed form_types table

## Code Conventions

- **Traits:** `app/Traits/SanitizesInput.php` — used for input cleaning (sanitizeInput, sanitizeName)
- **Form photo uploads:** handled via `Shared/Forms/Traits/TempPhotoManager`
- **Config classes:** `Configs/*Config` define field configs for each form type
- Forms with JSON inputs — always use `form_inputs` JSON column, not separate columns
- Machine tables use camelCase column names (incubatorName, isActive, creationDate)

## Testing

```bash
php artisan test
# Framework: PHPUnit 11.5
# Dev: Faker for seeding, DebugBar for profiling
```

## Docker

```bash
docker-compose up -d   # Start services
./vendor/bin/sail up   # Alternative via Laravel Sail
```
