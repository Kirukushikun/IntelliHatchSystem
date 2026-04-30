# IntelliHatchSystem — CLAUDE.md

> AI reference guide for the IntelliHatchSystem (IHS) Laravel project.

## Project Overview

**IntelliHatchSystem (IHS)** — Intelligence and insights from hatchery inputs with AI assistance.

- **Framework:** Laravel 12.0 (PHP ^8.2)
- **Frontend:** Livewire 4, Alpine.js 3, Tailwind CSS 4
- **Database:** MySQL (sessions, cache, queue all via database driver)
- **AI:** OpenRouter API (GPT-4o) via `app/Services/OpenRouterClient.php`
- **Build Tool:** Vite 7
- **Charts:** Chart.js

## Development Commands

```bash
composer run setup   # Full setup (install, migrate, seed, etc.)
composer run dev     # Concurrent: server + queue worker + log tail + Vite
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
- Superadmin-only routes: system prompts, admin management, activity logs, form types, CSV import, danger zone, user activity dashboard
- Migration `2026_03_21_114404_update_user_types_for_superadmin.php` shifted existing user_type values up by 1

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Superadmin, admin, and hatchery users |
| `form_types` | 15 form type definitions (with description, impact_level, usage_frequency, isActive) |
| `tags` | Tag definitions (Hatcheryman, Maintenance, QA/QC) |
| `tag_user` | Pivot: tags ↔ users (user categorization) |
| `form_type_tag` | Pivot: form_types ↔ tags (form type tagging) |
| `forms` | Submitted forms (JSON inputs, photos_purged_at) |
| `photos` | Uploaded photos (with disk/path) |
| `incubator-machines` | Incubator machine registry |
| `hatcher-machines` | Hatcher machine registry |
| `plenum-machines` | Plenum machine registry |
| `ps-numbers` | PS Number registry (camelCase columns) |
| `house-numbers` | House Number registry (camelCase columns) |
| `get-sets` | GetSet registry (camelCase columns) |
| `ai_chats` | AI chat conversation storage (multi-form-type support) |
| `system_prompts` | AI system prompt management |
| `activity_logs` | Audit trail / activity log |
| `sessions`, `cache`, `jobs` | Laravel infrastructure |

## Form Types (15)

1. **Incubator Routine** — Incubator routine checklist per shift
2. **Blower Air Hatcher** — Hatcher blower air speed monitoring
3. **Blower Air Incubator** — Incubator blower air speed monitoring
4. **Hatchery Sullair** — Sullair air compressor weekly PMS checklist
5. **Hatcher Machine Accuracy Monitoring** — Hatcher temperature checking
6. **Incubator Machine Accuracy Monitoring** — Incubator temperature checking
7. **Plenum Temperature & Humidity Monitoring**
8. **Entrance Damper Spacing Monitoring**
9. **Incubator Entrance Temperature Monitoring**
10. **Incubator Temperature Calibration**
11. **Hatcher Temperature Calibration**
12. **PASGAR Score**
13. **Incubator Rack PM** — Incubator rack preventive maintenance checklist
14. **Weekly Volt/Ampere** — Weekly voltage and ampere monitoring
15. **Diesel Generator Weekly** — Hatchery diesel generator weekly maintenance checklist

Forms store all inputs as JSON in `forms.form_inputs`. The `Form` model has a `getMachineInfoAttribute()` accessor to extract machine info from that JSON. Form types have `description`, `impact_level`, and `isActive` fields.

## Key Models

```
User           → user_type (0=superadmin, 1=admin, 2=user), is_disabled, username, first_name, last_name
                 hasMany: Form (uploaded_by), AiChat, SystemPrompt (created_by), ActivityLog
                 belongsToMany: Tag (via tag_user)
Form           → belongsTo(FormType), belongsTo(User via uploaded_by), form_inputs (JSON), photos_purged_at
FormType       → hasMany(Form), belongsToMany(Tag via form_type_tag), form_name (unique), description,
                 impact_level, usage_frequency (daily/weekly/monthly), isActive; scope: active()
Tag            → belongsToMany(User via tag_user), belongsToMany(FormType via form_type_tag), name (unique)
Incubator      → table: incubator-machines, incubatorName, isActive, creationDate
Hatcher        → table: hatcher-machines, hatcherName, isActive, creationDate
Plenum         → table: plenum-machines, plenumName, isActive, creationDate
PsNumber       → table: ps-numbers, psNumber, isActive, creationDate
HouseNumber    → table: house-numbers, houseNumber, isActive, creationDate
GetSet         → table: get-sets, getSetName, isActive, creationDate
AiChat         → user_id, prompt, system_prompt_snapshot, context_data, form_type_id, form_type_ids (array),
                  context_period (week/month/all/custom), context_date_from, context_date_to,
                  status (pending/analyzing/done/failed), response, error_message
                  methods: isPending(), isTerminal(), formScopeLabel(), contextPeriodLabel()
SystemPrompt   → name, prompt, is_active, is_archived, created_by; scopes: active(), notArchived()
ActivityLog    → user_id, action, description, subject_type, subject_id, properties (JSON), ip_address
HatcheryUser   → first_name, last_name, is_disabled
```

## Controllers

| Controller | Location | Responsibility |
|-----------|----------|----------------|
| `LoginController` | `Auth/` | Auth: login/logout with rate limiting |
| `FormController` | `Admin/` | Form listing & per-form-type views |
| `DashboardController` | `Admin/` | Dashboard views per form type |
| `InsightsController` | `Admin/` | AI-generated insights per form type |
| `FormsPrintController` | `Admin/` | Print-ready views (signed URLs) |
| `UserController` | `Admin/` | Hatchery user CRUD + password change |
| `ActivityLogExportController` | `Admin/` | Export activity logs as CSV and PDF |
| `IncubatorController` | `Shared/Management/` | Incubator machine management |
| `HatcherController` | `Shared/Management/` | Hatcher machine management |
| `PlenumController` | `Shared/Management/` | Plenum machine management |
| `PsNumberController` | `Shared/Management/` | PS Number management (CRUD + toggle) |
| `HouseNumberController` | `Shared/Management/` | House Number management (CRUD + toggle) |
| `GetSetController` | `Shared/Management/` | GetSet management (CRUD + toggle) |
| `FormStatsController` | `Api/` | API: form statistics with date filtering |
| `WebhookController` | `Api/` | API: send forms to external webhook |

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
│   ├── ActivityLogs/ (Display) [superadmin]
│   ├── FormTypes/ (Display) [superadmin]
│   ├── ImportForms/ [superadmin]
│   │   ├── IncubatorRoutine, HatcherTempCalibration
│   │   ├── IncubatorTempCalibration, DieselGeneratorWeekly
│   ├── DangerZone/ (Display) [superadmin]
│   └── UserActivityDashboard [superadmin]
├── Shared/
│   ├── Forms/ (15 form components)
│   │   ├── IncubatorRoutineForm, BlowerAirHatcherForm, BlowerAirIncubatorForm, HatcherySullairForm
│   │   ├── HatcherMachineAccuracyForm, IncubatorMachineAccuracyForm
│   │   ├── PlenumTempHumidityForm, EntranceDamperSpacingForm
│   │   ├── IncubatorEntranceTempForm, IncubatorTempCalibrationForm, HatcherTempCalibrationForm
│   │   ├── PasgarScoreForm, IncubatorRackPmForm, WeeklyVoltAmpereForm, DieselGeneratorWeeklyForm
│   │   └── Traits/TempPhotoManager
│   ├── FormsDashboard/ (15 dashboards — all form types)
│   └── Management/
│       ├── IncubatorManagement/ (Display, Create, Edit, Delete, Disable)
│       ├── HatcherManagement/ (Display, Create, Edit, Delete, Disable)
│       ├── PlenumManagement/ (Display, Create, Edit, Delete, Disable)
│       ├── PsNumberManagement/ (Display, Create, Edit, Delete, Disable)
│       ├── HouseNumberManagement/ (Display, Create, Edit, Delete, Disable)
│       └── GetSetManagement/ (Display, Create, Edit, Delete, Disable)
├── Components/
│   └── FormNavigation
├── Configs/ (15 form field configurations)
│   ├── IncubatorRoutineConfig, BlowerAirHatcherConfig, BlowerAirIncubatorConfig
│   ├── HatcherySullairConfig, HatcherMachineAccuracyConfig, IncubatorMachineAccuracyConfig
│   ├── PlenumTempHumidityConfig, EntranceDamperSpacingConfig, IncubatorEntranceTempConfig
│   ├── IncubatorTempCalibrationConfig, HatcherTempCalibrationConfig, PasgarScoreConfig
│   ├── IncubatorRackPmConfig, WeeklyVoltAmpereConfig, DieselGeneratorWeeklyConfig
└── Auth/
    └── ChangePassword
```

## Routes Summary

- `GET /` — Redirect to `/login`
- `GET /login`, `POST /login`, `POST /logout` — Auth
- `GET /forms/{type}` — Public form submission (no auth) — all 15 types
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
| `/admin/form-types` | superadmin | Form type management |
| `/admin/import/*` | superadmin | CSV bulk form import |
| `/admin/danger-zone` | superadmin | Data purge operations |
| `/admin/user-activity-dashboard` | superadmin | User activity analytics |
| `/admin/ps-numbers` | admin+ | PS Number management |
| `/admin/house-numbers` | admin+ | House Number management |
| `/admin/get-sets` | admin+ | GetSet management |
| `/user/ps-numbers` | user | PS Number access |
| `/user/house-numbers` | user | House Number access |
| `/user/get-sets` | user | GetSet access |

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
- Methods: `chat()`, `ask(string $userMessage, string $systemPrompt, string $model)`
- Config key: `services.openrouter.key` (env: `OPENROUTER_API_KEY`)

### AI Chat Feature
- Admins submit a prompt with optional form type context and date range
- Supports **multi-select form types** (`form_type_ids` array) or single `form_type_id`
- Queued via `ProcessAiChatRequest` job (async processing)
- Context periods: `week`, `month`, `all`, `custom` (with date_from/date_to)
- Rate limit: 10 requests per hour per user
- Status lifecycle: `pending` → `analyzing` → `done` / `failed`
- System prompt snapshot captured at submission time
- Builds context from `forms.form_inputs` for selected form type(s) + date range

### System Prompts (`/admin/system-prompts`, superadmin only)
- CRUD with archive, activate/deactivate, duplicate
- Only one prompt can be active at a time
- Active prompt is used as system prompt for AI chat requests

## Activity Logging

- **Service:** `app/Services/ActivityLogger.php` — static methods: `log()`, `logForUser()`
- **Helper:** `audit(module, action, label, subject, meta)` — global shorthand in `app/helpers.php`
- **Model:** `ActivityLog` — tracks user_id, action, description, subject_type (module), subject_id, properties (JSON), ip_address
- **Viewer:** `/admin/activity-logs` (superadmin) — search, filter by action/user/date, sort
- **Export:** CSV (UTF-8 BOM for Excel) and PDF with active filters displayed

### Standardized Action Names
Use these consistent action names — never use past tense or compound names:

| Action | When to use |
|--------|-------------|
| `login` | User login |
| `logout` | User logout |
| `create` | New record created |
| `update` | Record modified |
| `delete` | Record removed |
| `enable` | Record activated |
| `disable` | Record deactivated |
| `import` | CSV/bulk import |
| `export` | Data export |
| `reset_password` | Admin resets another user's password |
| `change_password` | User changes own password |

### Standardized Module Names
Always tag logs with a module (stored in `subject_type` column):

| Module | Scope |
|--------|-------|
| `Auth` | Login, logout, own password change |
| `User` | Hatchery user management |
| `Admin` | Admin/superadmin management |
| `Hatcher` | Hatcher machine management |
| `Incubator` | Incubator machine management |
| `Plenum` | Plenum machine management |
| `Form` | Form submissions and imports |
| `FormType` | Form type configuration |

## CSV Import (Superadmin)

Bulk import forms from CSV files. Currently supported:
- Incubator Routine
- Hatcher Temperature Calibration
- Incubator Temperature Calibration
- Diesel Generator Weekly

Components in `app/Livewire/Admin/ImportForms/`.

## Danger Zone (Superadmin)

`/admin/danger-zone` — bulk data management operations:
- **Photo Purge:** Remove photos from forms (keeps form data), filter by quarter/custom date range
- **Form Wipe:** Delete form submissions and photos, filter by date range/year
- **Activity Log Purge:** Delete activity logs, filter by date range/year

Each operation runs as a queued job: `PurgeFormPhotos`, `WipeFormSubmissions`, `PurgeActivityLogs`.

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
GOOGLE_DRIVE_CLIENT_ID=    # Google Drive OAuth (for backups)
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
GOOGLE_DRIVE_FOLDER_PATH=  # e.g. Backup/IHS
ADMIN_NOTIFICATION_EMAIL=  # Admin notification recipient
```

## Middleware

| Alias | Class | Purpose |
|-------|-------|---------|
| `superadmin` | SuperadminMiddleware | user_type === 0 |
| `admin` | AdminMiddleware | user_type === 0 or 1 |
| `user` | UserMiddleware | user_type === 2 |
| `api.key` | ApiKeyMiddleware | x-api-key header validation |

Proxy trust level set to `*` in `bootstrap/app.php`.

## Services

| Service | Purpose |
|---------|---------|
| `OpenRouterClient` | AI requests via OpenRouter API (`chat()`, `ask()`) |
| `ActivityLogger` | Static audit trail logging (`log()`, `logForUser()`) |

## Queue Jobs

| Job | Purpose |
|-----|---------|
| `ProcessAiChatRequest` | Async AI chat processing — builds context, calls OpenRouter, updates AiChat status |
| `PurgeFormPhotos` | Remove photos from forms while keeping form data; quarter/date-range filtering |
| `WipeFormSubmissions` | Bulk delete form submissions & photos; date-range/year filtering |
| `PurgeActivityLogs` | Bulk delete activity logs; date-range/year filtering |

## Custom Artisan Commands

- `CleanupOrphanedPhotos` (`photos:clean [--dry-run]`) — Remove orphaned photos (>24hrs, pending/unreferenced)
- `DropAndReseedFormTypes` (`form:reset`) — Reset and reseed form_types table
- `optimize:app` — Clear routes, config, and optimization caches

## Notable Composer Packages

- `google/apiclient` — Google API client
- `intervention/image-laravel` — Server-side image processing/compression
- `masbug/flysystem-google-drive-ext` — Google Drive filesystem
- `resend/resend-laravel` — Resend email integration
- `spatie/laravel-backup` — Application backup

## Code Conventions

- **Traits:** `app/Traits/SanitizesInput.php` — used for input cleaning (sanitizeInput, sanitizeName)
- **Form photo uploads:** handled via `Shared/Forms/Traits/TempPhotoManager` — 10MB/photo limit, 20 photos/field max, server-side compression (1920px max, 80% JPEG quality)
- **Config classes:** `Configs/*Config` define field configs for each form type
- Forms with JSON inputs — always use `form_inputs` JSON column, not separate columns
- Machine/registry tables use camelCase column names (incubatorName, isActive, creationDate) — follow for all new registries
- Activity logging — use `audit(module, action, label, subject, meta)` or `ActivityLogger::log()` with standardized action/module names (see Activity Logging section)

## Database Seeders

| Seeder | Purpose |
|--------|---------|
| `DatabaseSeeder` | Orchestrator: creates 3 default users (1 superadmin, 2 admins), calls all other seeders |
| `FormTypeSeeder` | Seeds 11 base form types (remaining 4 added via migrations) |
| `TagSeeder` | Seeds 3 default tags: Hatcheryman, Maintenance, QA/QC |
| `MachineSeeder` | Seeds 10 each of Incubator, Hatcher, and Plenum machines |
| `HatcheryUserSeeder` | Seeds 10 hatchery users (user_type=2) |
| `TestSeeder` | Comprehensive test data: registries + form submissions for all 15 form types |

## Migrations (33 total)

```
0001_01_01_000000  create_users_table
0001_01_01_000001  create_cache_table
0001_01_01_000002  create_jobs_table
2025_02_02_085229  create_form_types_table
2026_02_01_130746  create_photos_table
2026_02_01_131433  create_incubator_machines_table
2026_02_01_131433  create_hatcher_machines_table
2026_02_01_131433  create_plenum_machines_table
2026_02_02_085241  create_forms_table
2026_03_15_000000  add_entrance_damper_spacing_form_type
2026_03_15_010000  add_incubator_entrance_temp_form_type
2026_03_15_020000  add_incubator_temp_calibration_form_type
2026_03_15_030000  add_hatcher_temp_calibration_form_type
2026_03_15_040000  create_ps_numbers_table
2026_03_15_040001  create_house_numbers_table
2026_03_19_000000  add_pasgar_score_form_type
2026_03_21_114404  update_user_types_for_superadmin
2026_03_21_120000  create_system_prompts_table
2026_03_21_140000  create_ai_chats_table
2026_03_21_200000  create_activity_logs_table
2026_03_21_200001  add_custom_range_to_ai_chats_table
2026_03_21_200002  add_form_type_ids_to_ai_chats_table
2026_03_25_000000  add_incubator_rack_pm_form_type
2026_03_25_100000  add_weekly_volt_ampere_form_type
2026_03_25_200000  create_get_sets_table
2026_03_25_400000  add_diesel_generator_weekly_form_type
2026_03_28_000000  add_impact_level_to_form_types
2026_03_31_011134  add_description_to_form_types_table
2026_04_24_000000  add_is_active_to_form_types_table
2026_04_24_213019  add_photos_purged_at_to_forms_table
2026_04_25_000000  add_index_to_forms_table
2026_04_30_000000  create_tags_table
2026_04_30_100000  add_usage_frequency_and_form_type_tag
```

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
