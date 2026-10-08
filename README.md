# MiniOS - Web Desktop Environment for Laravel

<p align="center">
  <img src="https://raw.githubusercontent.com/novay/minios/main/resources/img/images/logo.png" alt="MiniOS Logo" width="160" />
</p>

<p align="center">
  <strong>Transform your Laravel application into an interactive, multi-window Web Desktop OS.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/novay/minios"><img src="https://img.shields.io/packagist/v/novay/minios.svg?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/novay/minios"><img src="https://img.shields.io/packagist/dt/novay/minios.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="Software License"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-FF2D20.svg?style=flat-square&logo=laravel" alt="Laravel"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-3.x%20%7C%204.x-FB70A9.svg?style=flat-square&logo=livewire" alt="Livewire"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/TailwindCSS-v4-38B2AC.svg?style=flat-square&logo=tailwind-css" alt="Tailwind CSS"></a>
</p>

---

## 🌟 Overview

**MiniOS** is a modern, modular Web Desktop Environment designed for Laravel. It lets you build powerful multi-window applications with a familiar desktop operating system interface—complete with floating windows, a macOS/Ubuntu-inspired dock, top menu bar, application launcher, lock screen, and deep URL state management.

MiniOS is powered by **Laravel**, **Livewire**, **Alpine.js**, **Tailwind CSS v4**, and **Vite**.

---

## 🏛️ Architecture

MiniOS is structured modularly:
- **Core Package (`Novay\MiniOS`)**: Manages the Window Manager, Desktop Workspace, Top Bar, Dock, App Launcher, Context Menus, Lock Screen, Setting Service (`os_setting`), and Asset Pipeline.
- **Built-in System Applications**: Browser, Files, Terminal, Settings, Calculator, Activity Monitor, Control Panel, and About MiniOS.
- **Custom Applications**: Your isolated modules located in `app/MiniOS/{AppName}/` with templates directly in `resources/views/apps/`.

```
mini-os/
├── app/
│   └── MiniOS/
│       ├── Todo/                     <-- Custom App Module
│       │   ├── TodoApp.php           <-- Manifest Contract (DesktopApp)
│       │   └── Livewire/
│       │       └── Todo.php          <-- Livewire Component
│       └── Contact/                  <-- Multi-Route / CRUD Module
│           ├── ContactApp.php        <-- Manifest Contract
│           └── Livewire/
│               ├── Contact.php       <-- Shell Router Component
│               ├── ContactList.php   <-- /contacts Livewire Component
│               └── ContactCreate.php <-- /contacts/create Livewire Component
├── config/
│   └── minios.php                    <-- Configuration & App Registry
├── packages/
│   └── novay/
│       └── minios/                   <-- MiniOS Package
├── resources/
│   └── views/
│       └── apps/                     <-- Blade Templates for Custom Apps
│           ├── todo.blade.php
│           └── contact/
│               ├── contact-list.blade.php
│               └── contact-create.blade.php
└── routes/
    └── web.php                       <-- Application Routing
```

---

## ✨ Features

- 🖥️ **Full Desktop Workspace**: Multi-window management with drag, resize, minimize, maximize, snap, and z-index ordering.
- 🚀 **Built-in System Applications**:
  - 🌐 **Browser**: Embedded web browser with history and navigation controls.
  - 📁 **Files**: Storage explorer with grid/list view and file previews.
  - ⌨️ **Terminal**: Interactive shell supporting Artisan commands and built-in utilities.
  - ⚙️ **Settings**: Themes, dynamic accents (Zinc, Indigo, Emerald, Sky, Amber, Rose, Violet), wallpapers, dock positions, and window behaviors.
  - 🎛️ **Control Panel**: App marketplace & manager: install `.zip` packages, inspect database migrations & models, check composer dependencies, install packages via built-in GUI terminal runner, and safely uninstall with migration rollback.
  - 🧮 **Calculator**: Standard desktop calculator utility.
  - 📊 **Activity Monitor**: Real-time memory, storage, and PHP engine resource statistics.
  - ℹ️ **About MiniOS**: System specifications and version overview.
- 🧩 **Modular Custom App System**: Easily create and register custom applications using your existing Livewire components.
- 🔒 **Security & Authentication**: Built-in Lock Screen, Fortify authentication, and Passkeys support.
- 🎨 **Modern Aesthetics**: Glassmorphic panels, dark mode, smooth micro-animations, and dynamic wallpaper gradients.
- 🔗 **Deep-linking & URL Sync**: Opening applications dynamically synchronizes browser URLs without page reloads.

---

## 📦 Installation & Setup

### 1. Require Package

Require the package via Composer:

```bash
composer require novay/minios
```

### 2. Run Installer & Migrations

Publish configuration, migrations, and assets using the installer:

```bash
php artisan minios:install
php artisan migrate
```

### 3. Frontend Setup (Vite & Tailwind v4)

1. **Update `vite.config.js`** with path aliases:

```javascript
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            '@minios': path.resolve(__dirname, 'vendor/novay/minios/resources/js'),
            '@minios-css': path.resolve(__dirname, 'vendor/novay/minios/resources/css'),
            '@minios-img': path.resolve(__dirname, 'vendor/novay/minios/resources/img'),
        },
    },
});
```
*(Catatan: Jika Anda mengembangkan secara lokal di monorepo `packages/novay/minios`, ganti `vendor/` dengan `packages/`)*

2. **In `resources/js/app.js`**, register the MiniOS Alpine store:

```javascript
import minios from '@minios/minios';

// Register ke Alpine
if (window.Alpine) {
    Alpine.data('minios', minios);
}
```

Or for Starter Kit bundling Livewire ESM:
```javascript
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import minios from '@minios/minios';

Alpine.data('minios', minios);

Livewire.start();
```

3. **In `resources/css/app.css`**, import the MiniOS stylesheet and scan package views:

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/novay/minios/resources/css/minios.css';

@source '../views';
@source '../../vendor/novay/minios/resources/views/**/*.blade.php';
```

4. **Build assets**:

```bash
npm run build
# or run development watch server
npm run dev
```

### 4. Register Desktop Routes

Register `MiniOS::routes()` at the bottom of `routes/web.php` to handle desktop workspace routing and deep linking:

```php
use Illuminate\Support\Facades\Route;
use Novay\MiniOS\Facades\MiniOS;

// Your custom routes (if any)...

// MiniOS Desktop routes (catch-all at bottom)
MiniOS::routes();
```

### 5. Laravel Fortify Integration (Optional)

Jika menggunakan Laravel Fortify, cukup daftarkan tampilan autentikasi MiniOS di `app/Providers/FortifyServiceProvider.php`:

```php
use Novay\MiniOS\Facades\MiniOS;

public function boot(): void
{
    // ...
    MiniOS::fortify();
}
```

---

## 🛠️ Creating Custom Applications

### Method 1: Using the Artisan Generator (Fast)

Generate a new application skeleton in seconds:

```bash
php artisan minios:make-app Notes --icon=document-text --pinned
```

This automatically generates:
1. **Manifest Contract**: `app/MiniOS/Notes/NotesApp.php`
2. **Livewire Component**: `app/MiniOS/Notes/Livewire/Notes.php`
3. **Blade Template**: `resources/views/apps/notes.blade.php`

---

### Method 2: Manual Creation (Step-by-Step)

#### Step 1: Create Livewire Component
Create your Livewire component inside `app/MiniOS/{AppName}/Livewire/`:

```php
// app/MiniOS/Todo/Livewire/Todo.php
namespace App\MiniOS\Todo\Livewire;

use Livewire\Component;

class Todo extends Component
{
    public string $newTask = '';
    public array $tasks = [];

    public function addTask(): void
    {
        if (trim($this->newTask) !== '') {
            $this->tasks[] = trim($this->newTask);
            $this->newTask = '';
        }
    }

    public function render()
    {
        return view('apps.todo');
    }
}
```

Create its Blade template directly in `resources/views/apps/`:

```blade
{{-- resources/views/apps/todo.blade.php --}}
<div class="h-full flex flex-col bg-neutral-900 text-white p-4">
    <div class="flex gap-2 mb-4">
        <input 
            type="text" 
            wire:model="newTask" 
            wire:keydown.enter="addTask" 
            placeholder="Write a task..." 
            class="flex-1 bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <button 
            wire:click="addTask" 
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
        >
            Add
        </button>
    </div>

    <ul class="flex-1 overflow-y-auto space-y-2">
        @forelse ($tasks as $task)
            <li class="bg-neutral-800/60 p-3 rounded-lg text-sm flex items-center justify-between border border-white/5">
                <span>{{ $task }}</span>
            </li>
        @empty
            <li class="text-neutral-500 text-sm italic text-center py-8">No tasks yet.</li>
        @endforelse
    </ul>
</div>
```

---

#### Step 2: Implement the Manifest Contract (`DesktopApp`)

Create the application manifest implementing `Novay\MiniOS\Contracts\DesktopApp`:

```php
// app/MiniOS/Todo/TodoApp.php
namespace App\MiniOS\Todo;

use App\MiniOS\Todo\Livewire\Todo;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class TodoApp implements DesktopApp
{
    /**
     * Unique application ID (kebab-case).
     */
    public function id(): string
    {
        return 'todo';
    }

    /**
     * Display name in Desktop, Dock, and App Launcher.
     */
    public function name(): string
    {
        return 'Todo List';
    }

    /**
     * App icon (Heroicon name or full image URL: .png, .webp, .svg).
     */
    public function icon(): string
    {
        return 'queue-list';
    }

    /**
     * Initial entry URL when opened.
     */
    public function entry(): string
    {
        return '/todo';
    }

    /**
     * Recognized route URLs for this application.
     */
    public function routes(): array
    {
        return [
            '/todo',
            '/todo/today',
            '/todo/plans',
        ];
    }

    /**
     * Whether the application is pinned to the dock by default.
     */
    public function isPinned(): bool
    {
        return true;
    }

    /**
     * Livewire component class (FQCN) or registered alias.
     */
    public function component(): ?string
    {
        return Todo::class;
    }

    /**
     * Initial and constraint geometry configuration for the window.
     */
    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(850, 550)      // Default width, height
            ->min(500, 350)       // Minimum width, height
            ->max(1400, 900);     // Optional maximum width, height
    }
}
```

---

#### Step 3: Register in `config/minios.php`

Add the manifest class to the `apps` array in `config/minios.php`:

```php
return [
    'apps' => [
        \App\MiniOS\Todo\TodoApp::class,
    ],
];
```

Your app is now fully functional!
- Desktop & Dock icons are active with launch notifications.
- Windows are draggable, resizable, minimizable, and maximizable.
- URL state (`/todo`) syncs seamlessly without browser page reloads.

---

## 🔀 Application Routing & Component Patterns

### Pattern A: Native Desktop App (Recommended)
You do **not** need to define separate web routes in `routes/web.php`. MiniOS catches all URLs declared in `DesktopApp::routes()` through its desktop route handler and opens the application window dynamically.

### Pattern B: Multi-Route & Decoupled CRUD Components
When an application has distinct pages (such as `/contacts` for listing and `/contacts/create` for creating new records), you can decouple the UI into dedicated Livewire components:

1. **Manifest (`ContactApp.php`)**:
   ```php
   public function routes(): array
   {
       return [
           '/contacts',
           '/contacts/create',
           '/contacts/edit',
       ];
   }
   ```
2. **Dedicated Components**:
   - `app/MiniOS/Contact/Livewire/ContactList.php` (`/contacts`)
   - `app/MiniOS/Contact/Livewire/ContactCreate.php` (`/contacts/create`)
3. **Shell Switcher (`resources/views/apps/contact.blade.php`)**:
   ```blade
   <div class="h-full w-full overflow-hidden">
       @if ($view === 'create')
           <livewire:dynamic-component :is="\App\MiniOS\Contact\Livewire\ContactCreate::class" wire:key="contact-create-component" />
       @else
           <livewire:dynamic-component :is="\App\MiniOS\Contact\Livewire\ContactList::class" :selected-id="$selectedId" wire:key="contact-list-component" />
       @endif
   </div>
   ```

### Pattern C: Hybrid Standalone Page
If you also want an application accessible as a standalone full-page view without the desktop frame:
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('todo', \App\MiniOS\Todo\Livewire\Todo::class)->name('todo.index');
});

// MiniOS fallback at the bottom
MiniOS::routes();
```

---

## 🎛️ App Packaging & Control Panel

MiniOS provides an integrated **Control Panel** (`/control-panel`) for managing desktop applications:

### 1. App Distribution via `.zip`
Applications can be packaged and distributed as a single `.zip` archive containing:
```
my-app.zip
├── manifest.json              <-- Metadata (id, name, icon, entry, version, author, dependencies)
├── app/                       <-- Manifest & Livewire components
│   └── Todo/
│       ├── TodoApp.php
│       └── Livewire/Todo.php
├── resources/views/           <-- Blade templates
│   └── apps/todo.blade.php
├── database/migrations/       <-- Optional migrations (auto-executed upon install)
└── models/                    <-- Optional Eloquent models
```

Upload `.zip` packages directly via the **Control Panel** UI or via CLI:
- Auto-extracts to `app/MiniOS/{AppName}/` and `resources/views/apps/`
- Validates the `manifest.json` and `DesktopApp` contract
- Automatically runs migrations and registers the application in `config/minios.php`
- Clean uninstaller with automatic migration rollback and file removal

### 2. Dependency Management & GUI Terminal Runner
If an app specifies Composer dependencies in its manifest:
```json
{
  "dependencies": {
    "composer": [
      "spatie/laravel-backup"
    ]
  }
}
```
- **Control Panel Accordion**: Displays detailed app info, migration status, associated models, and package dependencies (with clear installed / missing badges).
- **One-Click Terminal Modal**: If a package is missing, an **"Install via Composer"** button opens a real-time terminal modal in the desktop environment, streaming `composer require` execution logs with instant feedback.
- **Missing Dependency Guard**: If a user attempts to launch an app before installing required packages, MiniOS displays an elegant in-window setup guide explaining what packages are missing and how to install them.

---

## ⚙️ Persistent Settings (`os_setting`)

MiniOS includes a database-persisted, user-scoped setting service with caching:

```php
// Retrieve a setting with default fallback
$theme = os_setting('appearance.theme', 'dark');
$wallpaper = os_setting('appearance.wallpaper', 'wall-1');

// Save a setting for the authenticated user
os_setting()->set('appearance.accent_color', 'emerald');

// Retrieve an entire category array
$dock = os_setting()->getCategory('dock');
```

In frontend JavaScript / Alpine:
```javascript
window.addEventListener('os-setting-updated', (event) => {
    console.log('Setting updated:', event.detail);
});
```

---

## 🧪 Testing & Code Quality

Run Pest tests:
```bash
php artisan test --compact
```

Run Laravel Pint to format code:
```bash
vendor/bin/pint --format agent
```

---

## 📄 License

MiniOS is open-sourced software licensed under the [MIT license](LICENSE).

## 👤 Author

Developed by **[Noviyanto Rahmadi](https://github.com/novay)** ([novay@btekno.id](mailto:novay@btekno.id)).
