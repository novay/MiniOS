# Window Manager — Phase 4

## Deployment

Run `php artisan migrate` and `php artisan optimize:clear` after updating the package. Configure Gate `minios.manage-apps` for trusted administrators **in the host application**. No extra migration is needed for Phase 4.

## Dynamic statusbar

The `layout.statusbar_enabled` flag controls whether a managed app gets a statusbar. Place custom Blade HTML at `resources/views/apps/{app-id}/customs/statusbar.blade.php` (e.g. `todo/customs/statusbar.blade.php`). A missing file gives a safe name + Ready fallback. The statusbar is a normal Blade file, not arbitrary HTML stored in a database.

For managed apps no additional markup is necessary. For embedded apps that already have `<x-minios:desktop>`, include `<x-minios:desktop.statusbar app-id="todo" />` to render the same custom Blade, or supply a normal slot to retain a custom existing statusbar. `statusbarVisible` is session initialized from localStorage under `minios:statusbar:{userId}:{appId}`, updated by the Window menu's toggle, and is never used for server authorization.

`php artisan minios:make-app Todo` creates an editable statusbar template without replacing existing source files. Add your own `@can`, components, or Livewire content in that Blade file; all user actions must still enforce permissions on the server.

## Katalog authorization and code execution

Users can browse Katalog without management permission, but install/uninstall and Composer operations require the host application's Gate `minios.manage-apps`. Web code installation is opt-in:

- `MINIOS_ALLOW_WEB_CATALOG_INSTALL=true` — trusted built-in templates (defaults true for `local`, false outside local).
- `MINIOS_ALLOW_WEB_ZIP_INSTALL=true` — **disabled** by default; archives contain executable PHP. Only enable in a developer-only environment. Uploads do not run database migrations or write to shared `app/Models` or `config`. Uploaded manifest and source code are still executable, so do not enable for untrusted admins or uploads.
- `MINIOS_ALLOW_WEB_COMPOSER=true` — **disabled** by default; allowing Composer modifies project dependencies, should be handled in CI/CD whenever possible.

Uninstall deletes its own app directory, own app views, and MiniOS Window Manager metadata; it intentionally does **not** roll back migrations, drop database tables, remove standalone app Models, or rewrite AppServiceProvider. Back up files and database before uninstall; remove remaining business artifacts separately after review. Apps whose source was explicitly registered in host code must be unregistered by the developer.

## Smoke tests

With PHP available (independent of host Laravel) run `php tests/smoke-phase1.php`, `php tests/smoke-phase2.php`, `php tests/smoke-phase3.php`, and `php tests/smoke-phase4.php`. Run `find src database tests -name '*.php' -print0 | xargs -0 -n1 php -l` for syntax checks.

Run full Laravel feature/integration/browser tests separately after copying the patch to a host Laravel project. The standalone ZIP does not provide a Laravel runtime or database, so this phase cannot certify end-to-end browser behaviour on its own.
