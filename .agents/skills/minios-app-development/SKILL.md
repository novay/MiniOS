---
name: minios-app-development
description: "Use this skill whenever creating, scaffolding, modifying, or extending desktop applications in MiniOS. Trigger whenever the user asks to build an app (e.g. 'buatkan aplikasi Notes', 'bikin app Kasir', 'create an Invoice manager', 'tambahkan app baru'), create a DesktopApp manifest, or develop windowed Livewire UI for MiniOS. Covers: architecture, Artisan scaffolding (php artisan minios:make-app), DesktopApp contract, window geometry, routing & deep-linking, Livewire reactivity within window frames, desktop UI aesthetics, dark mode, persistent settings, database integration, ZIP packaging & installer, and testing."
license: MIT
metadata:
  author: novay
---

# MiniOS Application Development Guide

MiniOS is a multi-window Web Desktop Operating System built for Laravel applications. Applications run as modular Livewire components hosted inside movable, resizable, floating desktop window containers.

When a developer asks to build, package, or create an application in MiniOS, follow this workflow to build a complete, production-ready desktop application.

---

## 🏛️ Application Anatomy

Every MiniOS application is structured in `app/Apps/{AppName}/`:

```
app/
└── Apps/
    └── {AppName}/
        ├── {AppName}App.php          <-- Manifest Contract (DesktopApp)
        ├── Livewire/
        │   └── {AppName}.php         <-- Root Livewire Component
        └── Models/ (optional)        <-- Eloquent Models if isolated
resources/
└── views/
    └── apps/
        └── {kebab-name}.blade.php    <-- Desktop Window Blade View
```

MiniOS automatically auto-discovers and registers any `{AppName}App` in `app/Apps/` that implements `Novay\MiniOS\Contracts\DesktopApp`. No manual configuration in `config/minios.php` is necessary.

---

## 🚀 1. Scaffolding with Artisan

To quickly scaffold a new application, run:

```bash
php artisan minios:make-app {Name} --icon={icon-name} --pinned
```

**Options**:
- `{Name}`: StudlyCase name of the application (e.g. `Notes`, `FinanceTracker`, `MusicPlayer`).
- `--icon`: Heroicon name (e.g. `document-text`, `currency-dollar`, `musical-note`) or image URL.
- `--pinned`: Add `--pinned` if the app should appear in the dock by default. Omit if it should only appear in the full-screen App Launcher.

This command generates:
1. `app/Apps/{Name}/{Name}App.php` (Manifest)
2. `app/Apps/{Name}/Livewire/{Name}.php` (Component)
3. `resources/views/apps/{kebab-name}.blade.php` (Blade View)

---

## 📜 2. The `DesktopApp` Manifest Contract

The manifest must implement `Novay\MiniOS\Contracts\DesktopApp`.

```php
<?php

namespace App\Apps\Notes;

use App\Apps\Notes\Livewire\Notes;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class NotesApp implements DesktopApp
{
    /**
     * Unique application ID (kebab-case).
     */
    public function id(): string
    {
        return 'notes';
    }

    /**
     * Display name in Launcher, Dock tooltip, and Window title.
     */
    public function name(): string
    {
        return 'Notes';
    }

    /**
     * App icon: Heroicon name or full URL to PNG/SVG.
     */
    public function icon(): string
    {
        return 'document-text';
    }

    /**
     * Primary entry URL when opened.
     */
    public function entry(): string
    {
        return '/notes';
    }

    /**
     * Recognized route URLs for deep-linking.
     */
    public function routes(): array
    {
        return [
            '/notes',
            '/notes/create',
            '/notes/{id}',
        ];
    }

    /**
     * True to pin in the Dock by default; false for App Launcher only.
     */
    public function isPinned(): bool
    {
        return true;
    }

    /**
     * FQCN of the root Livewire component.
     */
    public function component(): ?string
    {
        return Notes::class;
    }

    /**
     * Initial window size and constraints.
     */
    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(900, 600)      // Default width, height (px)
            ->min(550, 400);      // Minimum constraint
    }

    /**
     * Optional: External Composer package dependencies required by this app.
     * The Control Panel will inspect these packages, display installation status,
     * and provide direct 'composer require' commands if missing.
     *
     * @return array<int, string>
     */
    public function packages(): array
    {
        return [
            'spatie/laravel-backup',
        ];
    }
}
```

---

## 🎨 3. Window UI & Aesthetics Guidelines

Windows in MiniOS are floating containers that can be resized by the user. The Blade template lives inside the window body:

### Core Layout Rules:
1. **Full Dimensions**: Always use `class="flex h-full w-full flex-col bg-white dark:bg-[#191919] text-neutral-900 dark:text-neutral-100 overflow-hidden select-none"` on the root element.
2. **Window Header / Toolbar**: A top bar inside the window for navigation, search, and primary actions:
   ```blade
   <header class="flex h-12 shrink-0 items-center justify-between border-b border-neutral-200/80 dark:border-white/10 px-4 bg-neutral-50/70 dark:bg-[#202020]/70 backdrop-blur-md">
       <div class="flex items-center gap-3">
           <h1 class="text-sm font-semibold">Notes</h1>
       </div>
       <div class="flex items-center gap-2">
           <button wire:click="create" class="flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500 transition">
               <flux:icon name="plus" class="size-3.5" />
               <span>Baru</span>
           </button>
       </div>
   </header>
   ```
3. **Scrollable Content**: Always wrap dynamic content in `flex-1 overflow-y-auto`:
   ```blade
   <main class="flex-1 overflow-y-auto p-4 space-y-3">
       ...
   </main>
   ```
4. **Master-Detail Sidebar Pattern**: For productivity apps (Notes, Mail, Files, Contacts):
   ```blade
   <div class="flex flex-1 overflow-hidden">
       {{-- Left Sidebar --}}
       <aside class="w-64 shrink-0 border-r border-neutral-200/80 dark:border-white/10 overflow-y-auto p-3 bg-neutral-50/50 dark:bg-black/20">
           ...
       </aside>
       {{-- Main Workspace --}}
       <main class="flex-1 overflow-y-auto p-5">
           ...
       </main>
   </div>
   ```
5. **Dark Mode & Theming**:
   - Always supply `dark:` variants for all borders, backgrounds, and text.
   - Use subtle borders (`border-neutral-200 dark:border-white/10`).
   - Use high-contrast accessible text colors (`text-neutral-900 dark:text-neutral-100` and `text-neutral-500 dark:text-neutral-400`).

---

## ⚡ 4. Livewire Component Best Practices

### A. Scoping to Authenticated User
Always scope personal data to `auth()->id()`:

```php
public function getNotesProperty()
{
    return Note::query()
        ->where('user_id', auth()->id())
        ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
        ->latest()
        ->get();
}
```

### B. Inline Modals & Dialogs
Use Livewire state or Alpine.js for modal dialogs within the window. Ensure modals have `z-30` or higher inside the window and backdrop blur.

### C. Flash Notifications
Notify the user within the window or using Livewire events:
```php
$this->dispatch('notify', message: 'Catatan berhasil disimpan!');
```

---

## 🗄️ 5. Database & Model Generation

If the app requires database storage:
1. Create migration and model:
   ```bash
   php artisan make:model Note -m
   ```
2. Define fields in migration (include `foreignId('user_id')->constrained()->cascadeOnDelete()`).
3. Run `php artisan migrate`.
4. Configure `$fillable` or `$guarded` in the Model class.

---

## 📦 6. Packaging & Distributing Applications (.ZIP Archive)

MiniOS supports installing custom applications via `.zip` archive upload in **Control Panel > Pasang Aplikasi (.ZIP)**.

### Archive Structure Specification:
```text
{AppName}.zip
└── {AppName}/
    ├── {AppName}App.php          <-- Manifest (wajib)
    ├── Livewire/
    │   └── {AppName}.php         <-- Komponen Livewire (wajib)
    ├── views/
    │   └── {kebab-name}.blade.php<-- Template Blade (dimirror ke resources/views/apps/)
    ├── Models/ (opsional)
    │   └── ...                   <-- Eloquent Models (dimirror ke app/Models/{AppName}/)
    └── migrations/ (opsional)
        └── ...                   <-- Database migrations (dimirror ke database/migrations/)
```

### Automatic Installer Behaviors (`ControlPanel::installZip`):
1. **Security**: Zip-Slip & Path Traversal protection.
2. **Extraction**:
   - Manifest & Livewire diletakkan di `app/Apps/{AppName}/`.
   - `views/` dimirror ke `resources/views/apps/{kebab-name}.blade.php`.
   - `Models/` dimirror ke `app/Models/{AppName}/`.
   - `migrations/` dimirror ke `database/migrations/`.
3. **Auto-Migrate**: Jika arsip membawa file migration, `Artisan::call('migrate', ['--force' => true])` dijalankan secara otomatis.
4. **Runtime Registration**: `MiniOS::register($fullClass)` dipanggil langsung saat itu juga sehingga aplikasi langsung muncul tanpa perlu refresh.

---

## 🔄 7. Lifecycle Registrasi ke ServiceProvider

Bagaimana MiniOS mengenali dan meregistrasikan aplikasi kustom?

1. **Saat Pasang ZIP (Runtime)**:
   - Sesaat setelah ZIP diekstrak, `ControlPanel::installZip()` memanggil `MiniOS::register($fullClass)`. Aplikasi langsung aktif di in-memory desktop registry dan event `app-installed` dikirim ke frontend.
2. **Setiap Request / Booting Laravel**:
   - Di `Novay\MiniOS\MiniOSServiceProvider::boot()`, method `discoverCustomApps()` memindai direktori `app/Apps/*/*App.php`.
   - Setiap kelas yang mengimplementasikan `DesktopApp` otomatis didaftarkan ke `MiniOS::registry()`.
   - **Developer tidak perlu mengubah `AppServiceProvider` atau `config/minios.php` manual**. Penambahan file di `app/Apps/{AppName}/` otomatis aktif secara permanen.

---

## 🧪 8. Testing MiniOS Applications

Setiap aplikasi baru harus memiliki feature test di `tests/Feature/Apps/{Name}AppTest.php`:

```php
<?php

use App\Apps\Notes\NotesApp;
use App\Apps\Notes\Livewire\Notes;
use App\Models\User;
use Livewire\Livewire;

test('notes app manifest registers correctly with minios', function () {
    $app = app(NotesApp::class);

    expect($app->id())->toBe('notes')
        ->and($app->name())->toBe('Notes')
        ->and($app->entry())->toBe('/notes')
        ->and($app->component())->toBe(Notes::class);
});

test('authenticated user can interact with notes livewire component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Notes::class)
        ->assertOk()
        ->assertSee('Notes');
});
```

Run test:
```bash
vendor/bin/pest --filter=NotesAppTest
```

---

## 📋 Implementation Checklist for Agents

When a user asks: *"Buatkan aplikasi X di MiniOS"*:
1. **Analyze Requirements**: Determine app ID, icon, data model, and default window size.
2. **Scaffold**: Execute `php artisan minios:make-app {Name} --icon={icon} --pinned`.
3. **Database (if needed)**: Create migration & model, run `php artisan migrate`.
4. **Implement Livewire**: Write state variables, validation rules, actions in `app/Apps/{Name}/Livewire/{Name}.php`.
5. **Design Blade UI**: Implement a Windows 11 / macOS desktop-styled view in `resources/views/apps/{kebab-name}.blade.php` with responsive flexbox, dark mode, and smooth interactions.
6. **Test**: Write and run feature test in `tests/Feature/Apps/{Name}AppTest.php`.
7. **Format & Assets**: Run `vendor/bin/pint --format agent` and `npm run build` if assets need compiling.
