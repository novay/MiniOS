# Built-in Window Manager — Phase 1

Phase 1 adds a visual application-definition editor to MiniOS. This is **not** a replacement for the JavaScript window lifecycle engine or the existing Settings > Window Manager preferences.

## What's implemented

- Core app `window-manager`, visible in the application launcher (not pinned by default).
- `AppDefinitionResolver`: base PHP `DesktopApp` manifest + optional *global* DB override.
- AppRegistry surfaces the resolved `name`, `icon`, `entry`, `routes`, `pinned`, `component`, `window`, and `layout` values to the existing desktop engine.
- UI: choose/search apps; edit identity, entry/routes/component (custom apps only), window dimensions, resizable/maximizable/center, pinned flag; enable menubar/sidebar/statusbar flags; save/reset.
- Security: all reads, selections, writes and resets in the manager require Laravel authorization gate `minios.manage-apps`; unauthorized users see a restricted-access panel instead of an exception that breaks the desktop.
- Migration prepares `minios_app_definitions`, `minios_app_menus`, `minios_app_sidebar_items` (the latter two for later phases).

## Upgrade / installation

If the package is a local path repository, copy/replace its files and update Composer autoload as usual. When using a published package, install the new package version.

```bash
php artisan migrate
php artisan optimize:clear
```

If `minios-views` was previously published to `resources/views/vendor/minios`, the host project's overrides can take precedence. Publish the new app view when necessary, preserving any user modifications.

### Required admin gate (secure by default)

In your application's service provider `boot()` method:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('minios.manage-apps', static function ($user): bool {
    // Adapt to your own roles/permissions implementation.
    return $user->hasRole('admin');
});
```

The `hasRole()` example **requires your own RBAC package or method**. For the default Laravel user model, implement the authorization via an `is_admin` column, your existing role/permission logic, or an explicit admin user identifier. Do **not** grant this gate to all logged-in users. Without an explicitly defined gate, editing is denied.

## Data flow

```text
DesktopApp (PHP; source of truth / fallbacks)
  -> AppDefinitionResolver
      -> minios_app_definitions.overrides JSON (global diffs only)
  -> AppRegistry::toArray()
  -> config('desktop.applications') during boot
  -> existing Desktop / Window Manager JS engine
```

`settings` is not used because that table stores user-specific preferences. No code is overwritten when a setting changes. Selecting **Reset to Default** deletes the row for that app ID. Existing custom `DesktopApp` classes work unchanged.

Changes to active window state are applied **after a full desktop page reload**. Rebuilding assets is not necessary in Phase 1 (no JavaScript was changed). `max_width` and `max_height` are persisted as metadata, but the current desktop JavaScript window engine may not yet enforce those hard bounds; enforcement belongs to the later window-shell integration.

## Scope intentionally deferred

- Automatic rendering of a managed app's menubar, sidebar, or statusbar. The three flags are stored now; existing hard-coded app layouts continue to render as before.
- Menu builder and action dispatch (Phase 2).
- Sidebar CRUD navigation builder, generator and virtual route navigation (Phase 3).
- Shared layout integration, statusbar Blade slots and end-to-end app behavior (Phase 4).
- The app's `id` is immutable after creation, and system app executable targets (`entry`, `routes`, `component`) are read-only through this UI to protect core routes.

## Tests

```bash
php tests/smoke-phase1.php
php tests/smoke-overrides.php
find src database/migrations -name '*.php' -print0 | xargs -0 -n1 php -l
```

These verify baseline manifest-to-registry behavior, in-memory override saving/merging/resetting, and PHP syntax; actual end-to-end tests (migrations, Livewire UI, Gate rules, database writes, browser navigation) must be run in a host Laravel 11/12/13 application with migrations and an admin account configured.
