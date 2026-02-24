# IntelliHatchSystem Code Map

This document is a quick reference for **what each main directory/file is for**.

## `app/`
Laravel application source code (business logic).

### `app/Console/`
- **Purpose**: Artisan console tooling.
- **Contains**:
  - `Commands/`: custom `php artisan ...` commands.

#### `app/Console/Commands/CleanupOrphanedPhotos.php`
- **Purpose**: Cleans up old orphaned/temporary photo records and files (e.g. `FORMID`, `_pending_`) older than 24 hours.
- **Contains**: DB scan (`photos` + `forms` reference check) and public-disk cleanup for `storage/app/public/forms/*`.

#### `app/Console/Commands/DropAndReseedFormTypes.php`
- **Purpose**: Utility command to reset/reseed `form_types`.

### `app/Http/`
- **Purpose**: HTTP layer (controllers, middleware).
- **Contains**:
  - `Controllers/`: web/admin/api controllers.
  - `Middleware/`: request guards/role checks.

#### `app/Http/Controllers/Admin/`
- **Purpose**: Admin-facing routes (management + printing).

#### `app/Http/Controllers/Api/`
- **Purpose**: API endpoints (typically protected by API key middleware).

#### `app/Http/Controllers/Shared/`
- **Purpose**: Shared controllers used across roles/modules.

#### `app/Http/Middleware/AdminMiddleware.php`
- **Purpose**: Restricts routes to admin users.

#### `app/Http/Middleware/UserMiddleware.php`
- **Purpose**: Restricts routes to normal users.

#### `app/Http/Middleware/ApiKeyMiddleware.php`
- **Purpose**: Protects API routes via API key validation.

### `app/Livewire/`
- **Purpose**: Livewire components (stateful UI).
- **Contains**:
  - `Admin/`: admin screens (e.g. user management).
  - `Auth/`: auth-related components.
  - `Configs/`: configuration screens.
  - `Shared/`: reusable dashboards/forms/management components.

#### `app/Livewire/Shared/FormsDashboard/`
- **Purpose**: Dashboard pages for submitted forms (filtering, search, pagination, modals).
- **Contains**:
  - `BlowerAirHatcherDashboard.php`
  - `BlowerAirIncubatorDashboard.php`
  - `HatcherySullairDashboard.php`
  - `IncubatorRoutineDashboard.php`

#### `app/Livewire/Shared/Management/`
- **Purpose**: CRUD-style machine management components (open modal, create/edit/delete/disable).
- **Contains**:
  - `HatcherManagement/`
  - `IncubatorManagement/`
  - `PlenumManagement/`

### `app/Models/`
Eloquent models (database tables).

- `Form.php`: Form submission record (contains form inputs payload + metadata).
- `FormType.php`: Lookup model for available form types.
- `Hatcher.php`, `Incubator.php`, `Plenum.php`: Machine entities.
- `HatcheryUser.php`: Hatchery/user relationship model (role/ownership mapping if used).
- `User.php`: User entity.

### `app/Providers/`
Laravel service providers.

- `AppServiceProvider.php`: App bootstrap hooks (bindings, boot logic).

### `app/Traits/`
Shared traits.

- `SanitizesInput.php`: Shared input sanitization helpers used across Livewire components/forms.

---

## `database/`
Database schema and seed data.

### `database/migrations/`
- **Purpose**: Versioned schema definitions.
- **Contains**:
  - Core Laravel tables (`users`, `cache`, `jobs`).
  - App tables (`form_types`, `forms`, `photos`, and machine tables for hatcher/incubator/plenum).

### `database/factories/`
- **Purpose**: Model factories for generating fake data (dev/testing).

### `database/seeders/`
- **Purpose**: Seed initial/reference data.

### `database/.gitignore`
- **Purpose**: Keeps generated/local DB artifacts out of git.
- **How to use**: Git automatically respects it.
- **Related files**: none

---

## `routes/`
Route definitions.

- `web.php`: Browser routes (views, Livewire pages, admin pages).
- `api.php`: API routes (often protected by `ApiKeyMiddleware`).
- `console.php`: Console route definitions.

---

## `resources/`
Frontend and Blade view layer.

### `resources/views/`
- **Purpose**: Blade templates (layouts, pages, Livewire views, shared components).
- **Contains**:
  - `admin/`: admin pages/layouts.
  - `auth/`: login/auth pages.
  - `components/`: reusable Blade components.
  - `livewire/`: Livewire component templates.
  - `shared/`: shared partials and includes.

#### `resources/views/livewire/shared/forms-dashboard/`
- **Purpose**: UI for dashboards in `app/Livewire/Shared/FormsDashboard/*`.
- **Contains**:
  - `blower-air-hatcher-dashboard.blade.php`
  - `blower-air-incubator-dashboard.blade.php`
  - `hatchery-sullair-dashboard.blade.php`
  - `incubator-routine-dashboard.blade.php`
  - `modals/`: photo/detail modals used by dashboards.

#### `resources/views/livewire/shared/management/`
- **Purpose**: UI for machine management CRUD modals and lists.
- **Contains**:
  - `hatcher-management/`, `incubator-management/`, `plenum-management/`.

### `resources/css/`
- **Purpose**: Styles (typically compiled by Vite).

### `resources/js/`
- **Purpose**: Frontend JS entrypoints (typically compiled by Vite).

---

# File-by-file Index (Detailed)

Each entry follows:

- **Purpose**: what the file does.
- **How to use**: how it’s invoked (route, mounted Livewire, artisan, etc.).
- **Related files**: files that typically work together.

## `routes/`

### `routes/web.php`
- **Purpose**: Browser routes (landing page, public forms, auth, admin and user areas, print routes).
- **How to use**: Loaded automatically by Laravel; visit URLs like `/login`, `/admin/dashboard`, `/user/forms`, etc.
- **Related files**:
  - Controllers: `app/Http/Controllers/*`
  - Views: `resources/views/admin/*`, `resources/views/shared/*`, `resources/views/auth/*`

### `routes/api.php`
- **Purpose**: API routes (form stats and webhook endpoints).
- **How to use**: Call endpoints under `/api/*` from an HTTP client.
- **Related files**:
  - Middleware: `app/Http/Middleware/ApiKeyMiddleware.php`
  - Controllers: `app/Http/Controllers/Api/FormStatsController.php`, `app/Http/Controllers/Api/WebhookController.php`

### `routes/console.php`
- **Purpose**: Console scheduling and closure commands.
- **How to use**: Used by scheduler (`php artisan schedule:run`).
- **Related files**:
  - Command: `app/Console/Commands/CleanupOrphanedPhotos.php`
  - Note: verify scheduled command name matches the command signature (`photos:clean`).

## `app/Console/Commands/`

### `app/Console/Commands/CleanupOrphanedPhotos.php`
- **Purpose**: Cleans orphaned/temporary photo rows older than 24h and deletes DB records (and files if present) to keep DB and `storage/app/public` in sync.
- **How to use**: `php artisan photos:clean` (use `--dry-run` to preview).
- **Related files**:
  - Scheduler: `routes/console.php`
  - Tables: `database/migrations/2026_02_01_130746_create_photos_table.php`, `database/migrations/2026_02_02_085241_create_forms_table.php`

### `app/Console/Commands/DropAndReseedFormTypes.php`
- **Purpose**: Drops/resets/reseeds `form_types`.
- **How to use**: Run its artisan command (see signature in file).
- **Related files**: `database/seeders/FormTypeSeeder.php`

## `app/Http/Middleware/`

### `app/Http/Middleware/AdminMiddleware.php`
- **Purpose**: Restricts routes to admin users.
- **How to use**: Applied via `Route::middleware('admin')` in `routes/web.php`.
- **Related files**: `routes/web.php`

### `app/Http/Middleware/UserMiddleware.php`
- **Purpose**: Restricts routes to regular users.
- **How to use**: Applied via `Route::middleware('user')` in `routes/web.php`.
- **Related files**: `routes/web.php`

### `app/Http/Middleware/ApiKeyMiddleware.php`
- **Purpose**: Validates API key access for API routes.
- **How to use**: Applied in `routes/api.php` middleware group.
- **Related files**: `routes/api.php`

## `app/Http/Controllers/`

### `app/Http/Controllers/Controller.php`
- **Purpose**: Base controller.
- **How to use**: Extended by other controllers.
- **Related files**: `app/Http/Controllers/**`

### `app/Http/Controllers/Auth/LoginController.php`
- **Purpose**: Login/logout flow; redirects to admin or user landing based on `user_type`.
- **How to use**: `/login` (GET/POST), `/logout` (POST).
- **Related files**:
  - Routes: `routes/web.php`
  - View: `resources/views/auth/login.blade.php`

### `app/Http/Controllers/Admin/DashboardController.php`
- **Purpose**: Returns admin dashboard shell pages.
- **How to use**: `/admin/dashboard`, `/admin/*-dashboard`.
- **Related files**:
  - Routes: `routes/web.php`
  - Views: `resources/views/admin/dashboard.blade.php`, `resources/views/admin/*-dashboard.blade.php`
  - Livewire: dashboards under `app/Livewire/Shared/FormsDashboard/*` (mounted by the dashboard views)

### `app/Http/Controllers/Admin/FormController.php`
- **Purpose**: Forms listing + routes to form entry pages.
- **How to use**: `/admin/forms`, `/user/forms/*`.
- **Related files**:
  - Views: `resources/views/shared/forms.blade.php`, `resources/views/shared/forms/*.blade.php`
  - Livewire forms: `app/Livewire/Shared/Forms/*`

### `app/Http/Controllers/Admin/FormsPrintController.php`
- **Purpose**: Signed print pages for form submissions and incubator routine performance.
- **How to use**: Visit signed routes under `/admin/print/*`.
- **Related files**:
  - Routes: `routes/web.php`
  - Views: `resources/views/admin/print/forms.blade.php`, `resources/views/admin/print/incubator-routine-performance.blade.php`
  - Dashboard trigger: `app/Livewire/Shared/FormsDashboard/IncubatorRoutineDashboard.php`

### `app/Http/Controllers/Admin/UserController.php`
- **Purpose**: Admin users page + change password page.
- **How to use**: `/admin/users`, `/admin/change-password`.
- **Related files**:
  - Views: `resources/views/admin/users.blade.php`, `resources/views/auth/change-password-page.blade.php`
  - Livewire: `app/Livewire/Admin/UserManagement/*`

### `app/Http/Controllers/Shared/Management/HatcherController.php`
- **Purpose**: Hatcher machines management page shell.
- **How to use**: `/admin/hatcher-machines`, `/user/hatcher-machines`.
- **Related files**:
  - View: `resources/views/shared/management/hatcher-machines.blade.php`
  - Livewire: `app/Livewire/Shared/Management/HatcherManagement/*`

### `app/Http/Controllers/Shared/Management/IncubatorController.php`
- **Purpose**: Incubator machines management page shell.
- **How to use**: `/admin/incubator-machines`, `/user/incubator-machines`.
- **Related files**:
  - View: `resources/views/shared/management/incubator-machines.blade.php`
  - Livewire: `app/Livewire/Shared/Management/IncubatorManagement/*`

### `app/Http/Controllers/Shared/Management/PlenumController.php`
- **Purpose**: Plenum machines management page shell.
- **How to use**: `/admin/plenum-machines`, `/user/plenum-machines`.
- **Related files**:
  - View: `resources/views/shared/management/plenum-machines.blade.php`
  - Livewire: `app/Livewire/Shared/Management/PlenumManagement/*`

### `app/Http/Controllers/Api/FormStatsController.php`
- **Purpose**: Returns JSON form statistics with date + type filters.
- **How to use**: `GET /api/form-stats`, `GET /api/form-types`.
- **Related files**:
  - Routes: `routes/api.php`
  - Models: `app/Models/Form.php`, `app/Models/FormType.php`

### `app/Http/Controllers/Api/WebhookController.php`
- **Purpose**: Sends a form payload to an external webhook URL.
- **How to use**: `POST /api/webhook/send-form`.
- **Related files**:
  - Routes: `routes/api.php`
  - Config: `config/services.php` (webhook URL)

## `app/Livewire/Admin/`

### `app/Livewire/Admin/DashboardStats.php`
- **Purpose**: Admin dashboard stats cards + charts (reads `form_types` and `forms`).
- **How to use**: Mount with `@livewire('admin.dashboard-stats')` or `<livewire:admin.dashboard-stats />`.
- **Related files**:
  - Livewire view: `resources/views/livewire/admin/dashboard-stats.blade.php`
  - Page shell: `resources/views/admin/dashboard.blade.php`

### `app/Livewire/Admin/UserManagement/Display.php`
- **Purpose**: Main users list/table Livewire component.
- **How to use**: Mount with `@livewire('admin.user-management.display')` or `<livewire:admin.user-management.display />`.
- **Related files**: `resources/views/livewire/admin/user-management/display-user-management.blade.php`

### `app/Livewire/Admin/UserManagement/Create.php`
- **Purpose**: Create user modal.
- **How to use**: Mount with `@livewire('admin.user-management.create')` or `<livewire:admin.user-management.create />`.
- **Related files**: `resources/views/livewire/admin/user-management/create-user-management.blade.php`

### `app/Livewire/Admin/UserManagement/Edit.php`
- **Purpose**: Edit user modal.
- **How to use**: Mount with `@livewire('admin.user-management.edit')` or `<livewire:admin.user-management.edit />`.
- **Related files**: `resources/views/livewire/admin/user-management/edit-user-management.blade.php`

### `app/Livewire/Admin/UserManagement/Delete.php`
- **Purpose**: Delete user modal.
- **How to use**: Mount with `@livewire('admin.user-management.delete')` or `<livewire:admin.user-management.delete />`.
- **Related files**: `resources/views/livewire/admin/user-management/delete-user-management.blade.php`

### `app/Livewire/Admin/UserManagement/Disable.php`
- **Purpose**: Disable/enable user modal.
- **How to use**: Mount with `@livewire('admin.user-management.disable')` or `<livewire:admin.user-management.disable />`.
- **Related files**: `resources/views/livewire/admin/user-management/disable-user-management.blade.php`

### `app/Livewire/Admin/UserManagement/ResetPassword.php`
- **Purpose**: Reset password modal.
- **How to use**: Mount with `@livewire('admin.user-management.reset-password')` or `<livewire:admin.user-management.reset-password />`.
- **Related files**: `resources/views/livewire/admin/user-management/reset-password-user-management.blade.php`

## `app/Livewire/Auth/`

### `app/Livewire/Auth/ChangePassword.php`
- **Purpose**: Change password form component.
- **How to use**: Mount with `@livewire('auth.change-password')` or `<livewire:auth.change-password />`.
- **Related files**: `resources/views/livewire/auth/change-password.blade.php`

## `app/Livewire/Configs/`

### `app/Livewire/Configs/IncubatorRoutineConfig.php`
- **Purpose**: Validation rules/messages/default state + step mapping for incubator routine form.
- **How to use**: Used by `app/Livewire/Shared/Forms/IncubatorRoutineForm.php`.
- **Related files**: `resources/views/livewire/shared/forms/incubator-routine-form.blade.php`

### `app/Livewire/Configs/BlowerAirHatcherConfig.php`
- **Purpose**: Validation rules/messages/default state + step mapping for blower air hatcher form.
- **How to use**: Used by `app/Livewire/Shared/Forms/BlowerAirHatcherForm.php`.
- **Related files**: `resources/views/livewire/shared/forms/blower-air-hatcher-form.blade.php`

### `app/Livewire/Configs/BlowerAirIncubatorConfig.php`
- **Purpose**: Validation rules/messages/default state + step mapping for blower air incubator form.
- **How to use**: Used by `app/Livewire/Shared/Forms/BlowerAirIncubatorForm.php`.
- **Related files**: `resources/views/livewire/shared/forms/blower-air-incubator-form.blade.php`

### `app/Livewire/Configs/HatcherySullairConfig.php`
- **Purpose**: Validation rules/messages/default state + step mapping for hatchery sullair form.
- **How to use**: Used by `app/Livewire/Shared/Forms/HatcherySullairForm.php`.
- **Related files**: `resources/views/livewire/shared/forms/hatchery-sullair-form.blade.php`

## `app/Livewire/Shared/Forms/`

### `app/Livewire/Shared/Forms/IncubatorRoutineForm.php`
- **Purpose**: Multi-step incubator routine form; handles validation, uploads, submission, and webhook send.
- **How to use**: Mount with `@livewire('shared.forms.incubator-routine-form')` or `<livewire:shared.forms.incubator-routine-form />`.
- **Related files**:
  - Livewire view: `resources/views/livewire/shared/forms/incubator-routine-form.blade.php`
  - Config: `app/Livewire/Configs/IncubatorRoutineConfig.php`
  - Trait: `app/Livewire/Shared/Forms/Traits/TempPhotoManager.php`
  - Cleanup: `app/Console/Commands/CleanupOrphanedPhotos.php`

### `app/Livewire/Shared/Forms/BlowerAirHatcherForm.php`
- **Purpose**: Multi-step blower air hatcher form.
- **How to use**: Mount with `@livewire('shared.forms.blower-air-hatcher-form')` or `<livewire:shared.forms.blower-air-hatcher-form />`.
- **Related files**: `resources/views/livewire/shared/forms/blower-air-hatcher-form.blade.php`, `app/Livewire/Configs/BlowerAirHatcherConfig.php`

### `app/Livewire/Shared/Forms/BlowerAirIncubatorForm.php`
- **Purpose**: Multi-step blower air incubator form.
- **How to use**: Mount with `@livewire('shared.forms.blower-air-incubator-form')` or `<livewire:shared.forms.blower-air-incubator-form />`.
- **Related files**: `resources/views/livewire/shared/forms/blower-air-incubator-form.blade.php`, `app/Livewire/Configs/BlowerAirIncubatorConfig.php`

### `app/Livewire/Shared/Forms/HatcherySullairForm.php`
- **Purpose**: Multi-step hatchery sullair weekly PMS form.
- **How to use**: Mount with `@livewire('shared.forms.hatchery-sullair-form')` or `<livewire:shared.forms.hatchery-sullair-form />`.
- **Related files**: `resources/views/livewire/shared/forms/hatchery-sullair-form.blade.php`, `app/Livewire/Configs/HatcherySullairConfig.php`

### `app/Livewire/Shared/Forms/Traits/TempPhotoManager.php`
- **Purpose**: Centralizes “pending photo” behavior (temp upload naming, finalize, cleanup).
- **How to use**: Used by Livewire form components.
- **Related files**:
  - DB/photos: `database/migrations/2026_02_01_130746_create_photos_table.php`
  - Cleanup command: `app/Console/Commands/CleanupOrphanedPhotos.php`

## `app/Livewire/Shared/FormsDashboard/`

### `app/Livewire/Shared/FormsDashboard/IncubatorRoutineDashboard.php`
- **Purpose**: Admin dashboard for incubator routine forms (search/filter/pagination/photo modal + printing).
- **How to use**: Mount with `@livewire('shared.forms-dashboard.incubator-routine-dashboard')` or `<livewire:shared.forms-dashboard.incubator-routine-dashboard />`.
- **Related files**:
  - Livewire view: `resources/views/livewire/shared/forms-dashboard/incubator-routine-dashboard.blade.php`
  - Modal: `resources/views/livewire/shared/forms-dashboard/modals/incubator-routine-view.blade.php`
  - Print: `app/Http/Controllers/Admin/FormsPrintController.php`

### `app/Livewire/Shared/FormsDashboard/BlowerAirHatcherDashboard.php`
- **Purpose**: Admin dashboard for blower air hatcher forms.
- **How to use**: Mount with `@livewire('shared.forms-dashboard.blower-air-hatcher-dashboard')` or `<livewire:shared.forms-dashboard.blower-air-hatcher-dashboard />`.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/blower-air-hatcher-dashboard.blade.php`, modal `resources/views/livewire/shared/forms-dashboard/modals/blower-air-hatcher-view.blade.php`

### `app/Livewire/Shared/FormsDashboard/BlowerAirIncubatorDashboard.php`
- **Purpose**: Admin dashboard for blower air incubator forms.
- **How to use**: Mount with `@livewire('shared.forms-dashboard.blower-air-incubator-dashboard')` or `<livewire:shared.forms-dashboard.blower-air-incubator-dashboard />`.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/blower-air-incubator-dashboard.blade.php`, modal `resources/views/livewire/shared/forms-dashboard/modals/blower-air-incubator-view.blade.php`

### `app/Livewire/Shared/FormsDashboard/HatcherySullairDashboard.php`
- **Purpose**: Admin dashboard for hatchery sullair forms.
- **How to use**: Mount with `@livewire('shared.forms-dashboard.hatchery-sullair-dashboard')` or `<livewire:shared.forms-dashboard.hatchery-sullair-dashboard />`.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/hatchery-sullair-dashboard.blade.php`, modal `resources/views/livewire/shared/forms-dashboard/modals/hatchery-sullair-view.blade.php`

## `app/Livewire/Shared/Management/*`

Each management module follows the same pattern:

- `Display.php` renders a list/table.
- `Create.php`, `Edit.php`, `Delete.php`, `Disable.php` are modal actions.
- Each PHP component has a paired Blade view under `resources/views/livewire/shared/management/...`.

### `app/Livewire/Shared/Management/HatcherManagement/Display.php`
- **Purpose**: Hatcher machines list/table.
- **How to use**: Mount with `@livewire('shared.management.hatcher-management.display')` or `<livewire:shared.management.hatcher-management.display />`.
- **Related files**: `resources/views/livewire/shared/management/hatcher-management/display-hatcher-management.blade.php`

### `app/Livewire/Shared/Management/HatcherManagement/Create.php`
- **Purpose**: Create hatcher modal.
- **How to use**: Mount with `@livewire('shared.management.hatcher-management.create')` or `<livewire:shared.management.hatcher-management.create />`.
- **Related files**: `resources/views/livewire/shared/management/hatcher-management/create-hatcher-management.blade.php`

### `app/Livewire/Shared/Management/HatcherManagement/Edit.php`
- **Purpose**: Edit hatcher modal.
- **How to use**: Mount with `@livewire('shared.management.hatcher-management.edit')` or `<livewire:shared.management.hatcher-management.edit />`.
- **Related files**: `resources/views/livewire/shared/management/hatcher-management/edit-hatcher-management.blade.php`

### `app/Livewire/Shared/Management/HatcherManagement/Delete.php`
- **Purpose**: Delete hatcher modal.
- **How to use**: Mount with `@livewire('shared.management.hatcher-management.delete')` or `<livewire:shared.management.hatcher-management.delete />`.
- **Related files**: `resources/views/livewire/shared/management/hatcher-management/delete-hatcher-management.blade.php`

### `app/Livewire/Shared/Management/HatcherManagement/Disable.php`
- **Purpose**: Enable/disable hatcher modal.
- **How to use**: Mount with `@livewire('shared.management.hatcher-management.disable')` or `<livewire:shared.management.hatcher-management.disable />`.
- **Related files**: `resources/views/livewire/shared/management/hatcher-management/disable-hatcher-management.blade.php`

### `app/Livewire/Shared/Management/IncubatorManagement/Display.php`
- **Purpose**: Incubator machines list/table.
- **How to use**: Mount with `@livewire('shared.management.incubator-management.display')` or `<livewire:shared.management.incubator-management.display />`.
- **Related files**: `resources/views/livewire/shared/management/incubator-management/display-incubator-management.blade.php`

### `app/Livewire/Shared/Management/IncubatorManagement/Create.php`
- **Purpose**: Create incubator modal.
- **How to use**: Mount with `@livewire('shared.management.incubator-management.create')` or `<livewire:shared.management.incubator-management.create />`.
- **Related files**: `resources/views/livewire/shared/management/incubator-management/create-incubator-management.blade.php`

### `app/Livewire/Shared/Management/IncubatorManagement/Edit.php`
- **Purpose**: Edit incubator modal.
- **How to use**: Mount with `@livewire('shared.management.incubator-management.edit')` or `<livewire:shared.management.incubator-management.edit />`.
- **Related files**: `resources/views/livewire/shared/management/incubator-management/edit-incubator-management.blade.php`

### `app/Livewire/Shared/Management/IncubatorManagement/Delete.php`
- **Purpose**: Delete incubator modal.
- **How to use**: Mount with `@livewire('shared.management.incubator-management.delete')` or `<livewire:shared.management.incubator-management.delete />`.
- **Related files**: `resources/views/livewire/shared/management/incubator-management/delete-incubator-management.blade.php`

### `app/Livewire/Shared/Management/IncubatorManagement/Disable.php`
- **Purpose**: Enable/disable incubator modal.
- **How to use**: Mount with `@livewire('shared.management.incubator-management.disable')` or `<livewire:shared.management.incubator-management.disable />`.
- **Related files**: `resources/views/livewire/shared/management/incubator-management/disable-incubator-management.blade.php`

### `app/Livewire/Shared/Management/PlenumManagement/Display.php`
- **Purpose**: Plenum machines list/table.
- **How to use**: Mount with `@livewire('shared.management.plenum-management.display')` or `<livewire:shared.management.plenum-management.display />`.
- **Related files**: `resources/views/livewire/shared/management/plenum-management/display-plenum-management.blade.php`

### `app/Livewire/Shared/Management/PlenumManagement/Create.php`
- **Purpose**: Create plenum modal.
- **How to use**: Mount with `@livewire('shared.management.plenum-management.create')` or `<livewire:shared.management.plenum-management.create />`.
- **Related files**: `resources/views/livewire/shared/management/plenum-management/create-plenum-management.blade.php`

### `app/Livewire/Shared/Management/PlenumManagement/Edit.php`
- **Purpose**: Edit plenum modal.
- **How to use**: Mount with `@livewire('shared.management.plenum-management.edit')` or `<livewire:shared.management.plenum-management.edit />`.
- **Related files**: `resources/views/livewire/shared/management/plenum-management/edit-plenum-management.blade.php`

### `app/Livewire/Shared/Management/PlenumManagement/Delete.php`
- **Purpose**: Delete plenum modal.
- **How to use**: Mount with `@livewire('shared.management.plenum-management.delete')` or `<livewire:shared.management.plenum-management.delete />`.
- **Related files**: `resources/views/livewire/shared/management/plenum-management/delete-plenum-management.blade.php`

### `app/Livewire/Shared/Management/PlenumManagement/Disable.php`
- **Purpose**: Enable/disable plenum modal.
- **How to use**: Mount with `@livewire('shared.management.plenum-management.disable')` or `<livewire:shared.management.plenum-management.disable />`.
- **Related files**: `resources/views/livewire/shared/management/plenum-management/disable-plenum-management.blade.php`

## `app/Models/`

### `app/Models/Form.php`
- **Purpose**: Main form submission record (contains JSON inputs + submission timestamp).
- **How to use**: Queried by dashboards/print/API.
- **Related files**: `database/migrations/2026_02_02_085241_create_forms_table.php`

### `app/Models/FormType.php`
- **Purpose**: Lookup for form type definitions.
- **How to use**: Determine which forms belong to a type.
- **Related files**: `database/seeders/FormTypeSeeder.php`

### `app/Models/User.php`
- **Purpose**: Auth user model.
- **How to use**: Login + user management + reports.
- **Related files**: `app/Http/Controllers/Auth/LoginController.php`, `app/Livewire/Admin/UserManagement/*`

### `app/Models/Hatcher.php`
- **Purpose**: Hatcher machine entity.
- **How to use**: Used by hatcher management and form machine selection.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/*`, `database/migrations/2026_02_01_131433_create_hatcher_machines_table.php`

### `app/Models/Incubator.php`
- **Purpose**: Incubator machine entity.
- **How to use**: Used by incubator management and incubator routine/blower forms.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/*`, `database/migrations/2026_02_01_131433_create_incubator_machines_table.php`

### `app/Models/Plenum.php`
- **Purpose**: Plenum machine entity.
- **How to use**: Used by plenum management and related forms.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/*`, `database/migrations/2026_02_01_131433_create_plenum_machines_table.php`

### `app/Models/HatcheryUser.php`
- **Purpose**: Mapping model for hatchery/user association.
- **How to use**: Used wherever that mapping is needed.
- **Related files**: `app/Models/User.php`

## `app/Providers/`

### `app/Providers/AppServiceProvider.php`
- **Purpose**: Application service provider.
- **How to use**: Auto-loaded by Laravel.
- **Related files**: `bootstrap/providers.php`

## `app/Traits/`

### `app/Traits/SanitizesInput.php`
- **Purpose**: Shared input sanitization helpers used across Livewire components/forms.
- **How to use**: Imported by components/controllers.
- **Related files**: Livewire form components under `app/Livewire/Shared/Forms/*`

## `database/`

### `database/migrations/0001_01_01_000000_create_users_table.php`
- **Purpose**: Users table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/User.php`

### `database/migrations/0001_01_01_000001_create_cache_table.php`
- **Purpose**: Cache table schema (if using `database` cache driver).
- **How to use**: `php artisan migrate`.
- **Related files**: `.env` cache config.

### `database/migrations/0001_01_01_000002_create_jobs_table.php`
- **Purpose**: Jobs table schema (queues).
- **How to use**: `php artisan migrate`.
- **Related files**: queue config.

### `database/migrations/2025_02_02_085229_create_form_types_table.php`
- **Purpose**: Form types table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/FormType.php`, `database/seeders/FormTypeSeeder.php`

### `database/migrations/2026_02_02_085241_create_forms_table.php`
- **Purpose**: Forms submission table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/Form.php`

### `database/migrations/2026_02_01_130746_create_photos_table.php`
- **Purpose**: Photo tracking table for temp/finalized uploads.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Livewire/Shared/Forms/Traits/TempPhotoManager.php`, `app/Console/Commands/CleanupOrphanedPhotos.php`

### `database/migrations/2026_02_01_131433_create_hatcher_machines_table.php`
- **Purpose**: Hatcher machines table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/Hatcher.php`

### `database/migrations/2026_02_01_131433_create_incubator_machines_table.php`
- **Purpose**: Incubator machines table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/Incubator.php`

### `database/migrations/2026_02_01_131433_create_plenum_machines_table.php`
- **Purpose**: Plenum machines table schema.
- **How to use**: `php artisan migrate`.
- **Related files**: `app/Models/Plenum.php`

### `database/seeders/DatabaseSeeder.php`
- **Purpose**: Root seeder.
- **How to use**: `php artisan db:seed`.
- **Related files**: other seeders.

### `database/seeders/FormTypeSeeder.php`
- **Purpose**: Seeds `form_types` reference data.
- **How to use**: called from `DatabaseSeeder` or artisan.
- **Related files**: `app/Console/Commands/DropAndReseedFormTypes.php`

### `database/seeders/TestSeeder.php`
- **Purpose**: Seeds local/test data.
- **How to use**: run explicitly.
- **Related files**: `database/factories/*`

### `database/factories/HatcherFactory.php`
- **Purpose**: Factory for `Hatcher` records.
- **How to use**: Used in seeders/tests to generate hatchers.
- **Related files**: `app/Models/Hatcher.php`, `database/seeders/TestSeeder.php`

### `database/factories/IncubatorFactory.php`
- **Purpose**: Factory for `Incubator` records.
- **How to use**: Used in seeders/tests to generate incubators.
- **Related files**: `app/Models/Incubator.php`, `database/seeders/TestSeeder.php`

### `database/factories/PlenumFactory.php`
- **Purpose**: Factory for `Plenum` records.
- **How to use**: Used in seeders/tests to generate plenums.
- **Related files**: `app/Models/Plenum.php`, `database/seeders/TestSeeder.php`

### `database/factories/UserFactory.php`
- **Purpose**: Factory for `User` records.
- **How to use**: Used in seeders/tests to generate users.
- **Related files**: `app/Models/User.php`, `database/seeders/TestSeeder.php`

### `database/factories/HatcherySullairFactory.php`
- **Purpose**: Factory for hatchery sullair related data (used for seeding/testing).
- **How to use**: Used in seeders/tests.
- **Related files**: `database/seeders/TestSeeder.php`

## `resources/`

### `resources/css/app.css`
- **Purpose**: Main CSS entry file.
- **How to use**: Compiled by Vite and included from the layout.
- **Related files**: `resources/views/components/layout.blade.php`

### `resources/js/app.js`
- **Purpose**: Main JS entry file.
- **How to use**: Compiled by Vite and included from the layout.
- **Related files**: `resources/views/components/layout.blade.php`

### `resources/js/bootstrap.js`
- **Purpose**: Frontend bootstrap/init file.
- **How to use**: Imported by `resources/js/app.js`.
- **Related files**: `resources/js/app.js`

## `resources/views/components/`

### `resources/views/components/layout.blade.php`
- **Purpose**: Base HTML layout wrapper (assets, main slots).
- **How to use**: Use as `<x-layout>...</x-layout>`.
- **Parameters**: none
- **Slots**:
  - `slot` (page HTML body/content)
- **Render**: Wraps the entire page with HTML, head, and body tags, including CSS and JS assets.
- **Related files**: `resources/css/app.css`, `resources/js/app.js`

### `resources/views/components/navbar.blade.php`
- **Purpose**: Top navigation bar.
- **How to use**: Use as `<x-navbar />`.
- **Parameters**:
  - `hideDate` (bool, default `false`)
  - `includeSidebar` (bool, default `false`)
  - `user` (User model, default `null`) - used to detect admin vs user + build links
  - `title` (string|null, default `null`) - breadcrumb/title text
- **Slots**:
  - `slot` (page content)
  - `slot:navbarActions` (optional) - extra actions area beside breadcrumbs (only shown when `includeSidebar` is true)
- **Render**: Displays the top navigation bar with links and optional sidebar toggle.
- **Related files**: `resources/views/components/layout.blade.php`

### `resources/views/components/sidebar.blade.php`
- **Purpose**: Sidebar navigation (admin/user menus).
- **How to use**: Use as `<x-sidebar />`.
- **Parameters**:
  - `user` (User model|null) - determines menu items
  - `currentPage` (string|null) - optional external hint (component primarily uses `request()->is(...)`)
- **Slots**: none
- **Render**: Displays the sidebar navigation menu based on the user's role.
- **Related files**: route names in `routes/web.php`

### `resources/views/components/title.blade.php`
- **Purpose**: Standard page title/header component.
- **How to use**: Use as `<x-title />` (pass props/slots as needed).
- **Parameters**:
  - `subtitle` (string, default `''`)
- **Slots**:
  - `slot` (required) - the main title text
- **Render**: Displays a standard page title with optional subtitle.
- **Related files**: pages under `resources/views/*`

### `resources/views/components/button.blade.php`
- **Purpose**: Standard button component.
- **How to use**: Use as `<x-button>...</x-button>`.
- **Parameters**:
  - `variant` (string, default `primary`) - e.g. `primary`, `secondary`, `success`, `warning`, `danger`, `outline-*`, `ghost`, `link`
  - `size` (string, default `md`) - `xs|sm|md|lg|xl`
  - `icon` (Blade SVG paths/string, default `null`)
  - `iconPosition` (string, default `left`) - `left|right`
  - `type` (string, default `button`) - HTML button type
  - `disabled` (bool, default `false`)
  - `fullWidth` (bool, default `false`)
  - `loading` (bool, default `false`) - shows spinner
- **Slots**:
  - `slot` (button text/content)
- **Render**: Displays a customizable button with various styles and options.
- **Related files**: used by most forms/modals

### `resources/views/components/text-input.blade.php`
- **Purpose**: Text input component.
- **How to use**: Use as `<x-text-input />`.
- **Parameters**:
  - `label` (string, default `''`)
  - `name` (string, default `''`)
  - `errorKey` (string|null, default `null`) - defaults to `name`
  - `value` (string, default `''`)
  - `placeholder` (string, default `Enter text here`)
  - `required` (bool, default `false`)
  - `type` (string, default `text`) - supports `password` (adds show/hide toggle)
  - `class` (string, default `''`) - wrapper class
  - `icon` (string, default `''`) - supports `user|lock`
  - `wireModel` (string|null, default `null`) - if set uses `wire:model="..."`
  - `subtext` (string, default `''`) - rendered under label
- **Slots**: none
- **Render**: Displays a customizable text input field with label, error handling, and optional icon.
- **Related files**: Livewire form views

### `resources/views/components/text-area.blade.php`
- **Purpose**: Textarea component.
- **How to use**: Use as `<x-text-area />`.
- **Parameters**:
  - `label` (string, default `''`)
  - `name` (string, default `''`)
  - `errorKey` (string|null, default `null`) - defaults to `name`
  - `value` (string, default `''`)
  - `placeholder` (string, default `Enter text here`)
  - `required` (bool, default `false`)
  - `subtext` (string, default `''`)
- **Slots**: none
- **Render**: Displays a customizable textarea field with label and error handling.
- **Related files**: Livewire form views

### `resources/views/components/checkbox.blade.php`
- **Purpose**: Checkbox component.
- **How to use**: Use as `<x-checkbox />`.
- **Parameters**:
  - `label` (string)
  - `name` (string) - will submit as `name[]`
  - `errorKey` (string|null, default `null`) - defaults to `name`
  - `options` (array, default `[]`) - `[value => label]`
  - `required` (bool, default `false`) - enables client-side required validation when not using `wire:model`
  - `columns` (int, default `5`) - grid columns
  - `gridClass` (string, default `gap-2`)
- **Slots**: none
- **Render**: Displays a customizable checkbox group with label and error handling.
- **Related files**: Livewire views

### `resources/views/components/dropdown.blade.php`
- **Purpose**: Dropdown UI component.
- **How to use**: Use as `<x-dropdown>...</x-dropdown>`.
- **Parameters**:
  - `label` (string, default `''`)
  - `name` (string, default `''`)
  - `errorKey` (string|null, default `null`) - defaults to `name`
  - `required` (bool, default `false`)
  - `placeholder` (string, default `Select an option`)
  - `options` (array, default `[]`) - `[value => display]`
- **Slots**:
  - `slot` (optional) - append extra `<option>` items
- **Render**: Displays a customizable dropdown select field with label and error handling.
- **Related files**: dashboard toolbars and filters

### `resources/views/components/custom-pagination.blade.php`
- **Purpose**: Shared pagination UI.
- **How to use**: Use as `<x-custom-pagination />`.
- **Parameters**:
  - `currentPage` (int, default `1`)
  - `lastPage` (int, default `1`)
  - `pages` (array, default `[]`) - list of page numbers to render (max is determined by caller)
  - `onPageChange` (string|null, default `null`) - Livewire method name; defaults to `gotoPage`
- **Slots**: none
- **Render**: Displays a customizable pagination component with links to navigate through pages.
- **Related files**: management `Display` Livewire components

### `resources/views/components/photo-attach.blade.php`
- **Purpose**: Photo upload/attach UI.
- **How to use**: Use as `<x-photo-attach />`.
- **Parameters**:
  - `label` (string, default `''`)
  - `name` (string, default `''`) - used as the photo key and input name (`name[]`)
  - `required` (bool, default `false`)
- **Slots**: none
- **Render**: Displays a customizable photo upload field with label and error handling.
- **Related files**: `app/Livewire/Shared/Forms/Traits/TempPhotoManager.php`

### `resources/views/components/progress-navigation.blade.php`
- **Purpose**: Multi-step form progress navigation UI.
- **How to use**: Use as `<x-progress-navigation />`.
- **Parameters**:
  - `currentStep` (int, default `1`)
  - `visibleStepIds` (int[], default `[1]`)
  - `canProceed` (bool, default `true`) - disables Next when false
  - `isLastVisibleStep` (bool, default `false`) - shows Submit when true
  - `showProgress` (bool, default `false`) - shows progress dots
- **Slots**:
  - `slot` (step content)
- **Render**: Displays a customizable progress navigation component for multi-step forms.
- **Related files**: `app/Livewire/Components/FormNavigation.php`

### `resources/views/components/toast.blade.php`
- **Purpose**: Toast notification UI.
- **How to use**: Use as `<x-toast />` (typically mounted once in the layout).
- **Parameters**:
  - `messages` (array, default `[]`) - if empty, reads `session('error'|'success'|'warning'|'info')`
- **Slots**: none
- **Render**: Displays a customizable toast notification component with messages.
- **Related files**: Livewire components dispatching `showToast`

### `resources/views/components/dark-mode-toggle.blade.php`
- **Purpose**: UI toggle for theme mode.
- **How to use**: Use as `<x-dark-mode-toggle />`.
- **Parameters**: none
- **Slots**: none
- **Render**: Displays a customizable dark mode toggle button.
- **Related files**: `resources/views/components/navbar.blade.php`

### `resources/views/components/change-password.blade.php`
- **Purpose**: Change password UI component.
- **How to use**: Use as `<x-change-password />`.
- **Parameters**:
  - `class` (string, default `''`) - wrapper classes
- **Slots**: none
- **Render**: Displays a customizable change password form.
- **Related files**: `resources/views/auth/change-password-page.blade.php`

## `resources/views/auth/`

### `resources/views/auth/login.blade.php`
- **Purpose**: Login page.
- **How to use**: Visit `/login`.
- **Related files**: `app/Http/Controllers/Auth/LoginController.php`

### `resources/views/auth/change-password-page.blade.php`
- **Purpose**: Page shell for password change.
- **How to use**: Visit `/admin/change-password` or `/user/change-password`.
- **Related files**: `app/Livewire/Auth/ChangePassword.php`, `resources/views/livewire/auth/change-password.blade.php`

## `resources/views/admin/`

### `resources/views/admin/dashboard.blade.php`
- **Purpose**: Admin landing dashboard.
- **How to use**: Visit `/admin/dashboard`.
- **Related files**: `app/Livewire/Admin/DashboardStats.php`, `resources/views/livewire/admin/dashboard-stats.blade.php`

### `resources/views/admin/users.blade.php`
- **Purpose**: Admin user management page shell.
- **How to use**: Visit `/admin/users`.
- **Related files**: Livewire `app/Livewire/Admin/UserManagement/*`, `resources/views/livewire/admin/user-management/*`

### `resources/views/admin/incubator-routine-dashboard.blade.php`
- **Purpose**: Admin page shell for incubator routine dashboard.
- **How to use**: Visit `/admin/incubator-routine-dashboard`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/IncubatorRoutineDashboard.php`

### `resources/views/admin/blower-air-hatcher-dashboard.blade.php`
- **Purpose**: Admin page shell for blower air hatcher dashboard.
- **How to use**: Visit `/admin/blower-air-hatcher-dashboard`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/BlowerAirHatcherDashboard.php`

### `resources/views/admin/blower-air-incubator-dashboard.blade.php`
- **Purpose**: Admin page shell for blower air incubator dashboard.
- **How to use**: Visit `/admin/blower-air-incubator-dashboard`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/BlowerAirIncubatorDashboard.php`

### `resources/views/admin/hatchery-sullair-dashboard.blade.php`
- **Purpose**: Admin page shell for hatchery sullair dashboard.
- **How to use**: Visit `/admin/hatchery-sullair-dashboard`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/HatcherySullairDashboard.php`

## `resources/views/admin/print/`

### `resources/views/admin/print/forms.blade.php`
- **Purpose**: Generic print output for forms list.
- **How to use**: Open signed print URLs under `/admin/print/forms/*`.
- **Related files**: `app/Http/Controllers/Admin/FormsPrintController.php`

### `resources/views/admin/print/incubator-routine-performance.blade.php`
- **Purpose**: Incubator routine performance report print output.
- **How to use**: Open signed URL `/admin/print/performance/incubator-routine`.
- **Related files**: `app/Http/Controllers/Admin/FormsPrintController.php`

## `resources/views/shared/`

### `resources/views/shared/forms.blade.php`
- **Purpose**: Forms listing page (admin + user).
- **How to use**: Visit `/admin/forms` or `/user/forms`.
- **Related files**: `app/Http/Controllers/Admin/FormController.php`

### `resources/views/shared/forms/incubator-routine.blade.php`
- **Purpose**: Page shell for incubator routine form.
- **How to use**: `/forms/incubator-routine` (public) and `/user/forms/incubator-routine`.
- **Related files**: `app/Livewire/Shared/Forms/IncubatorRoutineForm.php`

### `resources/views/shared/forms/blower-air-hatcher.blade.php`
- **Purpose**: Page shell for blower air hatcher form.
- **How to use**: `/forms/blower-air-hatcher` and `/user/forms/blower-air-hatcher`.
- **Related files**: `app/Livewire/Shared/Forms/BlowerAirHatcherForm.php`

### `resources/views/shared/forms/blower-air-incubator.blade.php`
- **Purpose**: Page shell for blower air incubator form.
- **How to use**: `/forms/blower-air-incubator` and `/user/forms/blower-air-incubator`.
- **Related files**: `app/Livewire/Shared/Forms/BlowerAirIncubatorForm.php`

### `resources/views/shared/forms/hatchery-sullair.blade.php`
- **Purpose**: Page shell for hatchery sullair form.
- **How to use**: `/forms/hatchery-sullair` and `/user/forms/hatchery-sullair`.
- **Related files**: `app/Livewire/Shared/Forms/HatcherySullairForm.php`

### `resources/views/shared/hatcher-machines.blade.php`
- **Purpose**: Page shell for viewing hatchers (shared).
- **How to use**: Typically mounted via management routes.
- **Related files**: `resources/views/shared/management/hatcher-machines.blade.php`

### `resources/views/shared/incubator-machines.blade.php`
- **Purpose**: Page shell for viewing incubators (shared).
- **How to use**: Typically mounted via management routes.
- **Related files**: `resources/views/shared/management/incubator-machines.blade.php`

### `resources/views/shared/plenum-machines.blade.php`
- **Purpose**: Page shell for viewing plenums (shared).
- **How to use**: Typically mounted via management routes.
- **Related files**: `resources/views/shared/management/plenum-machines.blade.php`

## `resources/views/shared/management/`

### `resources/views/shared/management/hatcher-machines.blade.php`
- **Purpose**: Hatcher management page shell.
- **How to use**: `/admin/hatcher-machines` or `/user/hatcher-machines`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Display.php`

### `resources/views/shared/management/incubator-machines.blade.php`
- **Purpose**: Incubator management page shell.
- **How to use**: `/admin/incubator-machines` or `/user/incubator-machines`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Display.php`

### `resources/views/shared/management/plenum-machines.blade.php`
- **Purpose**: Plenum management page shell.
- **How to use**: `/admin/plenum-machines` or `/user/plenum-machines`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Display.php`

## `resources/views/livewire/admin/`

### `resources/views/livewire/admin/dashboard-stats.blade.php`
- **Purpose**: Blade view for admin dashboard stats Livewire component.
- **How to use**: Rendered by `app/Livewire/Admin/DashboardStats.php`.
- **Related files**: `app/Livewire/Admin/DashboardStats.php`

## `resources/views/livewire/admin/user-management/`

### `resources/views/livewire/admin/user-management/create-user-management.blade.php`
- **Purpose**: Create user modal UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/Create.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/Create.php`

### `resources/views/livewire/admin/user-management/edit-user-management.blade.php`
- **Purpose**: Edit user modal UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/Edit.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/Edit.php`

### `resources/views/livewire/admin/user-management/delete-user-management.blade.php`
- **Purpose**: Delete user modal UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/Delete.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/Delete.php`

### `resources/views/livewire/admin/user-management/disable-user-management.blade.php`
- **Purpose**: Disable/enable user modal UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/Disable.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/Disable.php`

### `resources/views/livewire/admin/user-management/display-user-management.blade.php`
- **Purpose**: Users list/table UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/Display.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/Display.php`

### `resources/views/livewire/admin/user-management/reset-password-user-management.blade.php`
- **Purpose**: Reset password modal UI.
- **How to use**: Rendered by `app/Livewire/Admin/UserManagement/ResetPassword.php`.
- **Related files**: `app/Livewire/Admin/UserManagement/ResetPassword.php`

## `resources/views/livewire/auth/`

### `resources/views/livewire/auth/change-password.blade.php`
- **Purpose**: Blade view for Livewire change password.
- **How to use**: Rendered by `app/Livewire/Auth/ChangePassword.php`.
- **Related files**: `app/Livewire/Auth/ChangePassword.php`

## `resources/views/livewire/shared/forms/`

### `resources/views/livewire/shared/forms/incubator-routine-form.blade.php`
- **Purpose**: Blade view for incubator routine form Livewire component.
- **How to use**: Rendered by `app/Livewire/Shared/Forms/IncubatorRoutineForm.php`.
- **Related files**: `app/Livewire/Shared/Forms/IncubatorRoutineForm.php`

### `resources/views/livewire/shared/forms/blower-air-hatcher-form.blade.php`
- **Purpose**: Blade view for blower air hatcher form.
- **How to use**: Rendered by `app/Livewire/Shared/Forms/BlowerAirHatcherForm.php`.
- **Related files**: `app/Livewire/Shared/Forms/BlowerAirHatcherForm.php`

### `resources/views/livewire/shared/forms/blower-air-incubator-form.blade.php`
- **Purpose**: Blade view for blower air incubator form.
- **How to use**: Rendered by `app/Livewire/Shared/Forms/BlowerAirIncubatorForm.php`.
- **Related files**: `app/Livewire/Shared/Forms/BlowerAirIncubatorForm.php`

### `resources/views/livewire/shared/forms/hatchery-sullair-form.blade.php`
- **Purpose**: Blade view for hatchery sullair form.
- **How to use**: Rendered by `app/Livewire/Shared/Forms/HatcherySullairForm.php`.
- **Related files**: `app/Livewire/Shared/Forms/HatcherySullairForm.php`

## `resources/views/livewire/shared/forms-dashboard/`

### `resources/views/livewire/shared/forms-dashboard/incubator-routine-dashboard.blade.php`
- **Purpose**: UI for incubator routine dashboard.
- **How to use**: Rendered by `app/Livewire/Shared/FormsDashboard/IncubatorRoutineDashboard.php`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/IncubatorRoutineDashboard.php`

### `resources/views/livewire/shared/forms-dashboard/blower-air-hatcher-dashboard.blade.php`
- **Purpose**: UI for blower air hatcher dashboard.
- **How to use**: Rendered by `app/Livewire/Shared/FormsDashboard/BlowerAirHatcherDashboard.php`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/BlowerAirHatcherDashboard.php`

### `resources/views/livewire/shared/forms-dashboard/blower-air-incubator-dashboard.blade.php`
- **Purpose**: UI for blower air incubator dashboard.
- **How to use**: Rendered by `app/Livewire/Shared/FormsDashboard/BlowerAirIncubatorDashboard.php`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/BlowerAirIncubatorDashboard.php`

### `resources/views/livewire/shared/forms-dashboard/hatchery-sullair-dashboard.blade.php`
- **Purpose**: UI for hatchery sullair dashboard.
- **How to use**: Rendered by `app/Livewire/Shared/FormsDashboard/HatcherySullairDashboard.php`.
- **Related files**: `app/Livewire/Shared/FormsDashboard/HatcherySullairDashboard.php`

## `resources/views/livewire/shared/forms-dashboard/modals/`

### `resources/views/livewire/shared/forms-dashboard/modals/incubator-routine-view.blade.php`
- **Purpose**: Dashboard modal/detail view for incubator routine submission.
- **How to use**: Included by incubator routine dashboard.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/incubator-routine-dashboard.blade.php`

### `resources/views/livewire/shared/forms-dashboard/modals/blower-air-hatcher-view.blade.php`
- **Purpose**: Dashboard modal/detail view for blower air hatcher submission.
- **How to use**: Included by blower air hatcher dashboard.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/blower-air-hatcher-dashboard.blade.php`

### `resources/views/livewire/shared/forms-dashboard/modals/blower-air-incubator-view.blade.php`
- **Purpose**: Dashboard modal/detail view for blower air incubator submission.
- **How to use**: Included by blower air incubator dashboard.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/blower-air-incubator-dashboard.blade.php`

### `resources/views/livewire/shared/forms-dashboard/modals/hatchery-sullair-view.blade.php`
- **Purpose**: Dashboard modal/detail view for hatchery sullair submission.
- **How to use**: Included by hatchery sullair dashboard.
- **Related files**: `resources/views/livewire/shared/forms-dashboard/hatchery-sullair-dashboard.blade.php`

## `resources/views/livewire/shared/management/`

### `resources/views/livewire/shared/management/hatcher-management/display-hatcher-management.blade.php`
- **Purpose**: Hatcher list/table UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/HatcherManagement/Display.php`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Display.php`

### `resources/views/livewire/shared/management/hatcher-management/create-hatcher-management.blade.php`
- **Purpose**: Create hatcher modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/HatcherManagement/Create.php`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Create.php`

### `resources/views/livewire/shared/management/hatcher-management/edit-hatcher-management.blade.php`
- **Purpose**: Edit hatcher modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/HatcherManagement/Edit.php`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Edit.php`

### `resources/views/livewire/shared/management/hatcher-management/delete-hatcher-management.blade.php`
- **Purpose**: Delete hatcher modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/HatcherManagement/Delete.php`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Delete.php`

### `resources/views/livewire/shared/management/hatcher-management/disable-hatcher-management.blade.php`
- **Purpose**: Disable hatcher modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/HatcherManagement/Disable.php`.
- **Related files**: `app/Livewire/Shared/Management/HatcherManagement/Disable.php`

### `resources/views/livewire/shared/management/incubator-management/display-incubator-management.blade.php`
- **Purpose**: Incubator list/table UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/IncubatorManagement/Display.php`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Display.php`

### `resources/views/livewire/shared/management/incubator-management/create-incubator-management.blade.php`
- **Purpose**: Create incubator modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/IncubatorManagement/Create.php`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Create.php`

### `resources/views/livewire/shared/management/incubator-management/edit-incubator-management.blade.php`
- **Purpose**: Edit incubator modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/IncubatorManagement/Edit.php`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Edit.php`

### `resources/views/livewire/shared/management/incubator-management/delete-incubator-management.blade.php`
- **Purpose**: Delete incubator modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/IncubatorManagement/Delete.php`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Delete.php`

### `resources/views/livewire/shared/management/incubator-management/disable-incubator-management.blade.php`
- **Purpose**: Disable incubator modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/IncubatorManagement/Disable.php`.
- **Related files**: `app/Livewire/Shared/Management/IncubatorManagement/Disable.php`

### `resources/views/livewire/shared/management/plenum-management/display-plenum-management.blade.php`
- **Purpose**: Plenum list/table UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/PlenumManagement/Display.php`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Display.php`

### `resources/views/livewire/shared/management/plenum-management/create-plenum-management.blade.php`
- **Purpose**: Create plenum modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/PlenumManagement/Create.php`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Create.php`

### `resources/views/livewire/shared/management/plenum-management/edit-plenum-management.blade.php`
- **Purpose**: Edit plenum modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/PlenumManagement/Edit.php`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Edit.php`

### `resources/views/livewire/shared/management/plenum-management/delete-plenum-management.blade.php`
- **Purpose**: Delete plenum modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/PlenumManagement/Delete.php`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Delete.php`

### `resources/views/livewire/shared/management/plenum-management/disable-plenum-management.blade.php`
- **Purpose**: Disable plenum modal UI.
- **How to use**: Rendered by `app/Livewire/Shared/Management/PlenumManagement/Disable.php`.
- **Related files**: `app/Livewire/Shared/Management/PlenumManagement/Disable.php`

## `app/Livewire/Components/FormNavigation.php`
- **Purpose**: Base class for multi-step form navigation (step tracking, visible fields, shifting).
- **How to use**: Extended by form components like `IncubatorRoutineForm`.
- **Related files**: `resources/views/components/progress-navigation.blade.php`