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
| 0 | Superadmin | `superadmin` | `/admin/*` (all + superadmin-only) |
| 1 | Admin | `admin` | `/admin/*` |
| 2 | Hatchery User | `user` | `/user/*` |

- Login rate limit: 5 attempts → 10-minute lockout
- Sessions stored in `sessions` DB table
- API auth via `x-api-key` header (`ApiKeyMiddleware`)
- Superadmin-only routes: system prompts, admin management, activity logs
- Migration `2026_03_21_114404_update_user_types_for_superadmin.php` shifted existing user_type values up by 1

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Superadmin, admin, and hatchery users |
| `form_types` | 12 form type definitions |
| `forms` | Submitted forms (JSON inputs) |
| `photos` | Uploaded photos (with disk/path) |
| `incubator-machines` | Incubator machine registry |
| `hatcher-machines` | Hatcher machine registry |
| `plenum-machines` | Plenum machine registry |
| `ps-numbers` | PS Number registry (camelCase columns) |
| `house-numbers` | House Number registry (camelCase columns) |
| `ai_chats` | AI chat conversation storage |
| `system_prompts` | AI system prompt management |
| `activity_logs` | Audit trail / activity log |
| `sessions`, `cache`, `jobs` | Laravel infrastructure |

## Form Types (12)

1. **Incubator Routine** — Incubator routine monitoring
2. **Blower Air Hatcher** — Hatcher blower/air monitoring
3. **Blower Air Incubator** — Incubator blower/air monitoring
4. **Hatchery Sullair** — Sullair compressor monitoring
5. **Hatcher Machine Accuracy Monitoring**
6. **Incubator Machine Accuracy Monitoring**
7. **Plenum Temperature & Humidity Monitoring**
8. **Entrance Damper Spacing Monitoring**
9. **Incubator Entrance Temperature Monitoring**
10. **Incubator Temperature Calibration**
11. **Hatcher Temperature Calibration**
12. **PASGAR Score**

Forms store all inputs as JSON in `forms.form_inputs`. The `Form` model has a `getMachineInfoAttribute()` accessor to extract machine info from that JSON.

## Key Models

```
User           → user_type (0=superadmin, 1=admin, 2=user), is_disabled, username
Form           → belongsTo(FormType), belongsTo(User via uploaded_by), form_inputs (JSON)
FormType       → hasMany(Form), form_name (unique)
Incubator      → table: incubator-machines, incubatorName, isActive
Hatcher        → table: hatcher-machines, hatcherName, isActive
Plenum         → table: plenum-machines, plenumName, isActive
PsNumber       → table: ps-numbers, psNumber, isActive, creationDate
HouseNumber    → table: house-numbers, houseNumber, isActive, creationDate
AiChat         → user_id, prompt, system_prompt_snapshot, context_data, form_type_id,
                  context_period (week/month/all/custom), context_date_from, context_date_to,
                  status (pending/analyzing/done/failed), response, error_message
SystemPrompt   → name, prompt, is_active, is_archived, created_by; scopes: active(), notArchived()
ActivityLog    → user_id, action, description, subject_type, subject_id, properties (JSON), ip_address
```

## Controllers

| Controller | Responsibility |
|-----------|----------------|
| `LoginController` | Auth: login/logout with rate limiting |
| `FormController` | CRUD for form submissions |
| `DashboardController` | Dashboard views per form type |
| `InsightsController` | AI-generated insights per form type |
| `FormsPrintController` | Print-ready views (signed URLs) |
| `UserController` | Hatchery user CRUD + password change |
| `IncubatorController` | Incubator machine management |
| `HatcherController` | Hatcher machine management |
| `PlenumController` | Plenum machine management |
| `PsNumberController` | PS Number management (CRUD + toggle) |
| `HouseNumberController` | House Number management (CRUD + toggle) |
| `FormStatsController` | API: form statistics with date filtering |
| `WebhookController` | API: send forms to external webhook |
| `ActivityLogExportController` | Export activity logs as CSV and PDF |

## Livewire Component Structure

```
app/Livewire/
├── Admin/
│   ├── DashboardStats
│   ├── InsightsIndex / InsightsDetail
│   ├── UserManagement/ (Display, Create, Edit, Delete, Disable, ResetPassword)
│   ├── AdminManagement/ (Display, Create, Edit, Delete, Disable, ChangePassword) [superadmin]
│   ├── AiChat/ (Index, View)
│   ├── SystemPrompts/ (Display) [superadmin]
│   └── ActivityLogs/ (Display) [superadmin]
├── Shared/
│   ├── Forms/
│   │   ├── IncubatorRoutineForm, BlowerAirHatcherForm, BlowerAirIncubatorForm, HatcherySullairForm
│   │   ├── HatcherMachineAccuracyForm, IncubatorMachineAccuracyForm
│   │   ├── PlenumTempHumidityForm, EntranceDamperSpacingForm
│   │   ├── IncubatorEntranceTempForm, IncubatorTempCalibrationForm
│   │   ├── HatcherTempCalibrationForm, PasgarScoreForm
│   │   └── Traits/TempPhotoManager
│   ├── FormsDashboard/ (per form type — all 12)
│   └── Management/
│       ├── Incubator/ (Display, Create, Edit, Delete, Disable)
│       ├── Hatcher/ (Display, Create, Edit, Delete, Disable)
│       ├── Plenum/ (Display, Create, Edit, Delete, Disable)
│       ├── PsNumberManagement/ (Display, Create, Edit, Delete, Disable)
│       └── HouseNumberManagement/ (Display, Create, Edit, Delete, Disable)
├── Components/
│   └── FormNavigation
├── Configs/
│   └── (IncubatorRoutineConfig, BlowerAirHatcherConfig, BlowerAirIncubatorConfig,
│      HatcherySullairConfig, HatcherMachineAccuracyConfig, IncubatorMachineAccuracyConfig,
│      PlenumTempHumidityConfig, EntranceDamperSpacingConfig, IncubatorEntranceTempConfig,
│      IncubatorTempCalibrationConfig, HatcherTempCalibrationConfig, PasgarScoreConfig)
└── Auth/
    └── ChangePassword
```

## Routes Summary

- `GET /` — Landing page
- `GET /login`, `POST /login`, `POST /logout` — Auth
- `GET /forms/{type}` — Public form submission (no auth) — all 12 types
- `/admin/*` — Admin area (admin or superadmin middleware)
- `/user/*` — User area (user middleware)
- `/api/*` — API (ApiKeyMiddleware + throttle:60,1)
- Print routes use **signed URLs** (`/admin/print/*`)

### Notable Admin Routes

| Route | Access | Purpose |
|-------|--------|---------|
| `/admin/ai-chat` | admin+ | AI chat list |
| `/admin/ai-chat/{id}` | admin+ | View chat |
| `/admin/print/ai-chat/{id}` | admin+ (signed) | Print chat |
| `/admin/system-prompts` | superadmin | System prompt management |
| `/admin/admin-management` | superadmin | Admin/superadmin user management |
| `/admin/activity-logs` | superadmin | Activity log viewer |
| `/admin/activity-logs/export/csv` | superadmin | Export logs as CSV |
| `/admin/activity-logs/export/pdf` | superadmin | Export logs as PDF |
| `/admin/ps-numbers` | admin+ | PS Number management |
| `/admin/house-numbers` | admin+ | House Number management |
| `/user/ps-numbers` | user | PS Number access |
| `/user/house-numbers` | user | House Number access |

## API Endpoints

```
GET  /api/form-stats          # Form stats; filter: form_type_id, date_filter, date_from/to
GET  /api/form-types          # List form types
POST /api/webhook/send-form   # Send single form to webhook
POST /api/webhook/send-multiple # Send multiple forms to webhook
```

Header required: `x-api-key: {API_KEY from .env}`

## AI Features

### OpenRouter Service (`app/Services/OpenRouterClient.php`)
- Model: `gpt-4o` (default)
- Retries: up to 3x with exponential backoff
- Retryable codes: 429, 500, 502, 503, 504
- Key method: `ask(string $userMessage, string $systemPrompt, string $model)`
- Config key: `services.openrouter.key` (env: `OPENROUTER_API_KEY`)

### AI Chat Feature
- Admins submit a prompt with optional form type context and date range
- Queued via `ProcessAiChatRequest` job (async processing)
- Context periods: `week`, `month`, `all`, `custom` (with date_from/date_to)
- Rate limit: 10 requests per hour per user
- Status lifecycle: `pending` → `analyzing` → `done` / `failed`
- System prompt snapshot captured at submission time
- Builds context from `forms.form_inputs` for selected form type + date range

### System Prompts (`/admin/system-prompts`, superadmin only)
- CRUD with archive, activate/deactivate, duplicate
- Only one prompt can be active at a time
- Active prompt is used as system prompt for AI chat requests

## Activity Logging

- **Service:** `app/Services/ActivityLogger.php` — static, call `ActivityLogger::log(action, description, ...)`
- **Model:** `ActivityLog` — tracks user_id, action, description, subject_type, subject_id, properties (JSON), ip_address
- **Viewer:** `/admin/activity-logs` (superadmin) — search, filter by action/user/date, sort
- **Export:** CSV (UTF-8 BOM for Excel) and PDF with active filters displayed

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
| `superadmin` | SuperadminMiddleware | user_type === 0 |
| `admin` | AdminMiddleware | user_type === 1 |
| `user` | UserMiddleware | user_type === 2 |
| `api.key` | ApiKeyMiddleware | x-api-key header validation |

Proxy trust level set to `*` in `bootstrap/app.php`.

## Services

| Service | Purpose |
|---------|---------|
| `OpenRouterClient` | AI requests via OpenRouter API |
| `ActivityLogger` | Static audit trail logging |

## Queue Jobs

| Job | Purpose |
|-----|---------|
| `ProcessAiChatRequest` | Async AI chat processing — builds context, calls OpenRouter, updates AiChat status |

## Custom Artisan Commands

- `CleanupOrphanedPhotos` — Remove photos without DB references
- `DropAndReseedFormTypes` — Reset and reseed form_types table
- `optimize:app` — Clear routes, config, and optimization caches

## Notable Composer Packages

- `google/apiclient` — Google API client
- `masbug/flysystem-google-drive-ext` — Google Drive filesystem
- `spatie/laravel-backup` — Application backup

## Code Conventions

- **Traits:** `app/Traits/SanitizesInput.php` — used for input cleaning (sanitizeInput, sanitizeName)
- **Form photo uploads:** handled via `Shared/Forms/Traits/TempPhotoManager`
- **Config classes:** `Configs/*Config` define field configs for each form type
- Forms with JSON inputs — always use `form_inputs` JSON column, not separate columns
- Machine/registry tables use camelCase column names (incubatorName, isActive, creationDate) — follow for all new registries
- Activity logging — call `ActivityLogger::log()` for significant user actions

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
