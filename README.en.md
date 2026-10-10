# MiniOS - Web Desktop OS for Laravel

<p align="center">
  <img src="https://raw.githubusercontent.com/novay/minios/main/resources/img/images/logo.png" alt="MiniOS Logo" width="160" />
</p>

<p align="center">
  <strong>Transform your Laravel application into an interactive, multi-window Web Desktop OS. Fast, modular, and effortless.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/novay/minios"><img src="https://img.shields.io/packagist/v/novay/minios.svg?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/novay/minios"><img src="https://img.shields.io/packagist/dt/novay/minios.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="Software License"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-FF2D20.svg?style=flat-square&logo=laravel" alt="Laravel"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-3.x%20%7C%204.x-FB70A9.svg?style=flat-square&logo=livewire" alt="Livewire"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/TailwindCSS-v4-38B2AC.svg?style=flat-square&logo=tailwind-css" alt="Tailwind CSS"></a>
</p>

<p align="center">
  <strong>Language:</strong>
  <a href="README.en.md"><strong>English</strong></a> |
  <a href="README.md">Bahasa Indonesia</a>
</p>

---

## 💡 Why MiniOS?

Traditional web admin dashboards are boring: dark sidebar on the left, top navigation bar, and plain white tables everywhere.

**MiniOS** transforms your Laravel application into an authentic, interactive Web Desktop OS reminiscent of macOS or Windows. It features floating windows that can be dragged, resized, and minimized to the dock, a real-time task manager, an interactive Artisan terminal, and an app installer that lets you install new modules just by uploading a `.zip` archive.

Powered by **Laravel**, **Livewire**, **Alpine.js**, **Tailwind CSS v4**, and **Vite**. Lightweight, reactive, and free of heavy JavaScript frontend frameworks.

---

## 🚀 Key Features

- 🖥️ **Full Desktop Workspace**: Multi-window environment with draggable, resizable windows, edge snapping, minimize to dock, maximize, and automated z-index stacking.
- 🎛️ **Control Panel & App Installer**:
  - Install new applications simply by uploading a `.zip` archive.
  - Automatically executes database migrations and registers apps into system menus.
  - Inspects Composer dependencies with a built-in **"Install via Composer"** real-time terminal modal.
  - Clean uninstall process with database rollback capability.
- 🛠️ **Built-in System Applications**:
  - 🌐 **Browser**: Mini web browser with navigation controls and history.
  - 📁 **Files**: Storage manager supporting Local, S3, and BunnyCDN disks with grid and list views.
  - ⌨️ **Terminal**: Interactive shell for executing Artisan commands right in your browser.
  - ⚙️ **Settings**: Customization for wallpapers, dark/light themes, accent colors, blur intensity, and dock position.
  - 📊 **Activity Monitor**: Windows 11-style task manager monitoring server RAM, disk usage, and CPU load.
  - 🧮 **Calculator** & ℹ️ **About MiniOS**.
- 🔒 **Security & Authentication**: Seamlessly integrates with Laravel Fortify, Passkeys, and an interactive desktop Lock Screen.
- 🔗 **Deep-linking & URL Sync**: Opening apps inside the desktop workspace automatically synchronizes the browser URL without full-page reloads.
- 🤖 **Built-in AI Agent Skill**: Automatically provides structured workflow guidance in `.agents/skills/minios-app-development/` so your AI assistant (Antigravity, Cursor, Windsurf, Claude Code, Copilot) immediately understands how to build MiniOS apps for you.

---

## 🏛️ Directory Structure

MiniOS is modular by design. Custom applications live neatly in `app/MiniOS/{AppName}/`:

```
mini-os/
├── app/
│   └── MiniOS/
│       ├── Todo/                     <-- Your Custom App Module
│       │   ├── TodoApp.php           <-- Application Manifest / Contract
│       │   └── Livewire/
│       │       └── Todo.php          <-- Livewire Component
│       └── POS/                      <-- Another Example App
│           ├── POSApp.php
│           └── Livewire/
│               └── POS.php
├── config/
│   └── minios.php                    <-- Configuration & App Registry
├── resources/
│   └── views/
│       └── apps/                     <-- Blade Views
│           ├── todo.blade.php
│           └── pos.blade.php
└── routes/
    └── web.php                       <-- Desktop Route Catch-all
```

---

## ⚡ Installation & Quick Start

MiniOS can be integrated into existing Laravel applications or installed in a brand new project.

### 🆕 Option 1: Fresh Laravel Project Installation

If you are starting from scratch, create a new Laravel project using the official installer:

```bash
laravel new example
```

Select the interactive prompts in your terminal as follows:
```text
Do you want to use a starter kit? [Yes]
Which frontend stack should your starter kit use? [Livewire]
Which authentication provider do you prefer? [Laravel's built-in authentication]
Would you like to use single-file Livewire components? [Any]
Would you like to add teams support to your application? [Any]

Waiting...

Which authentication features would you like to enable? [Any]

Finished.
```

Once completed, navigate into your project directory:
```bash
cd example
```

---

### 📦 Option 2 / Next: Install MiniOS Package

### 1. Require Package via Composer
```bash
composer require novay/minios
```

### 2. Run Installer & Migrations
Run the interactive installer to set up configs, assets, AI skills, and frontend styles:

```bash
php artisan minios:install
```

> **💡 Installation Modes:**
> * **[0] Full Desktop OS** — Complete Web OS environment (window manager, core apps, wallpapers, and auth views).
> * **[1] UI Kit Only** — Blade components, window styling, and assets only (does NOT modify routes or auth).
> 
> *Or pass a flag directly:* `php artisan minios:install --full` or `php artisan minios:install --ui-kit`.

```bash
php artisan migrate
npm run build
```

<details>
<summary><strong>🛠️ Manual Frontend Configuration (Optional Troubleshooting)</strong></summary>

<br>

> *Note: This is automatically handled by `minios:install`. Only needed if using a custom build pipeline or if automatic injection failed.*

**1. Include MiniOS Directives in your Blade layout (Easiest & Recommended):**
```blade
<head>
    <!-- ... -->
    @miniosStyles
</head>
<body>
    {{ $slot }}

    @livewireScriptConfig
    @fluxScripts
    @miniosScripts
</body>
```

**Or 2. Bundle via Vite using pre-compiled ESM (Optional):**
In `vite.config.js`:
```javascript
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            '@minios': path.resolve(__dirname, 'vendor/novay/minios/dist/minios.esm.js'),
            '@minios-css': path.resolve(__dirname, 'vendor/novay/minios/dist/minios.css'),
            '@minios-img': path.resolve(__dirname, 'vendor/novay/minios/resources/img'),
        },
    },
});
```

And in `resources/css/app.css`:
```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/novay/minios/dist/minios.css';

@source '../views';
@source '../../vendor/novay/minios/resources/views/**/*.blade.php';
```
</details>

---

### 3. Desktop Routes & Authentication (Fully Automated!)

Good news: `php artisan minios:install` **automatically handles both**:
- Appending `MiniOS::routes();` at the **very bottom** of `routes/web.php`.
- Appending `MiniOS::fortify();` inside `boot()` in `app/Providers/FortifyServiceProvider.php` (if Laravel Fortify is installed).

No manual edits needed! If you wish to inspect or customize them:

<details>
<summary><strong>🔍 Inspect Routes in <code>routes/web.php</code></strong></summary>

```php
use Novay\MiniOS\Facades\MiniOS;

// Your custom routes...

// Catch-all MiniOS desktop routes (must be at the very bottom!)
MiniOS::routes();
```
</details>

<details>
<summary><strong>🔍 Inspect Provider in <code>app/Providers/FortifyServiceProvider.php</code></strong></summary>

```php
use Novay\MiniOS\Facades\MiniOS;

public function boot(): void
{
    $this->configureActions();
    $this->configureViews();
    MiniOS::fortify(); // Must be placed AFTER configureViews() to override default views
    $this->configureRateLimiting();
}
```
</details>

---

### 🛑 Disabling MiniOS Authentication Views

If you want to keep your application's existing login interface (Breeze, Jetstream, Filament, or custom auth) and **not** use the MiniOS desktop auth views:

- **Method 1: Via Configuration (Recommended)**
  In `config/minios.php`, set:
  ```php
  'fortify_views' => false,
  ```

- **Method 2: Via Service Provider**
  In `app/Providers/FortifyServiceProvider.php`, remove or comment out:
  ```php
  // MiniOS::fortify();
  ```

*The MiniOS desktop will seamlessly recognize and read your existing login sessions!*

#### ⚠️ "Why am I redirected to `/email/verify` after registering or logging in?"
This is standard Fortify behavior when email verification is enabled but mail sending isn't configured. Choose the solution that fits your environment:

1. **Development / Internal App (Bypass Email Verification):**
   - In `config/fortify.php`, comment out:
     ```php
     // Features::emailVerification(),
     ```
   - In `config/minios.php`, ensure the desktop middleware does not enforce `verified`:
     ```php
     'middleware' => ['web', 'auth'],
     ```
   *You will immediately land directly on the desktop workspace upon login.*

2. **Production App (Real Email Verification):**
   - Configure your `.env` mail credentials (or set `MAIL_MAILER=log` for local testing to view the verification link in `storage/logs/laravel.log`).
   - Clicking the verification link will redirect you directly to the desktop workspace (`/?verified=1`).

3. **Quick Terminal Bypass (Testing):**
   ```bash
   php artisan tinker --execute "App\Models\User::first()->markEmailAsVerified();"
   ```

---

### 🧹 Uninstallation
If you ever want to cleanly remove MiniOS from your Laravel application:

```bash
php artisan minios:uninstall
```

This interactive command prompts for confirmation, then safely cleans up published configurations, public assets, views, routes, and reverts frontend integrations without touching any other parts of your Laravel application.

---

### ❓ "Will installing MiniOS disrupt my existing Laravel application?"

**Short answer: Absolutely not. It is completely safe.**

Here is why:

1. **Existing Routes Remain Untouched:**
   Always place `MiniOS::routes();` at the **very bottom** of your `routes/web.php`. All of your existing application routes (e.g. `/admin`, `/api`, `/checkout`, `/blog`) are matched and handled first by Laravel.
   *(Note: The root `/` URL points to the MiniOS desktop by default. If you want to keep your existing public homepage at `/`, define that route prior to `MiniOS::routes()`)*.

2. **Database Integrity:**
   MiniOS migrations only add 1 new standalone table (`desktop_settings`) and 1 nullable column (`locked_at`) to the existing `users` table. None of your business data or existing schemas are touched.

3. **Authentication Flexibility:**
   MiniOS relies on standard Laravel authentication (`auth`). If your project already uses an authentication solution (Breeze, Jetstream, Filament, or custom controllers), you **do not** need to call `MiniOS::fortify()`. Authenticated users can access the desktop directly through your existing login session!

4. **Assets & Bundling Safety:**
   `minios:install` merely appends module aliases and non-destructive CSS/JS imports. Your existing Tailwind styling and JavaScript components continue to function normally.

---

## 🔨 Creating Custom Applications

You have 3 easy ways to build new applications for your desktop:

### Method 1: Using the Artisan Generator (Fastest)
Run:
```bash
php artisan minios:make-app POS --icon=shopping-cart --pinned
```
This instantly generates 3 scaffolded files:
1. `app/MiniOS/POS/POSApp.php` (Application Manifest)
2. `app/MiniOS/POS/Livewire/POS.php` (Livewire Component)
3. `resources/views/apps/pos.blade.php` (Blade View)

---

### Method 2: Prompting Your AI Assistant
Because `minios:install` automatically publishes the skill guide to `.agents/skills/minios-app-development/`, your AI coding agent (**Google Antigravity**, **Cursor**, **Windsurf**, **Claude Code**, or **GitHub Copilot**) already understands the full MiniOS architecture.

Just prompt your agent:

> *"Build a Point of Sale (POS) app for MiniOS with an interactive cart, product search, and receipt printing modal."*

**What your AI agent will automatically handle:**
- Scaffolding files using `php artisan minios:make-app`.
- Creating Eloquent models, migrations, and executing `php artisan migrate`.
- Writing the reactive Livewire component logic.
- Crafting beautiful desktop-optimized Blade views with dark mode support.
- Writing Pest feature tests to guarantee reliability!

---

### Method 3: Manual Implementation

#### 1. Create the Livewire Component
Create `app/MiniOS/Todo/Livewire/Todo.php`:
```php
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

Create the view in `resources/views/apps/todo.blade.php`:
```blade
<div class="h-full flex flex-col bg-neutral-900 text-white p-4">
    <div class="flex gap-2 mb-4">
        <input 
            type="text" 
            wire:model="newTask" 
            wire:keydown.enter="addTask" 
            placeholder="Add a new task..." 
            class="flex-1 bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <button wire:click="addTask" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            Add
        </button>
    </div>

    <ul class="flex-1 overflow-y-auto space-y-2">
        @forelse ($tasks as $task)
            <li class="bg-neutral-800/60 p-3 rounded-lg text-sm flex items-center justify-between border border-white/5">
                <span>{{ $task }}</span>
            </li>
        @empty
            <li class="text-neutral-500 text-sm italic text-center py-8">No tasks yet. Take a break!</li>
        @endforelse
    </ul>
</div>
```

#### 2. Create the Application Manifest (`DesktopApp`)
Create `app/MiniOS/Todo/TodoApp.php`:
```php
namespace App\MiniOS\Todo;

use App\MiniOS\Todo\Livewire\Todo;
use Novay\MiniOS\Contracts\DesktopApp;
use Novay\MiniOS\Support\WindowConfig;

class TodoApp implements DesktopApp
{
    public function id(): string
    {
        return 'todo';
    }

    public function name(): string
    {
        return 'Todo Notes';
    }

    public function icon(): string
    {
        return 'queue-list'; // Heroicon name or image URL
    }

    public function entry(): string
    {
        return '/todo';
    }

    public function routes(): array
    {
        return ['/todo'];
    }

    public function isPinned(): bool
    {
        return true; // Pin to desktop dock
    }

    public function component(): ?string
    {
        return Todo::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(850, 550)  // Default window size
            ->min(500, 350)   // Minimum resize constraints
            ->max(1400, 900); // Maximum resize constraints
    }
}
```

*That's it! MiniOS automatically discovers and displays your app on the desktop workspace.*

---

## 🔀 Routing & Component Architecture

### Pattern A: Native Desktop App (Recommended)
No custom entries in `routes/web.php` needed. Simply declare your URLs inside `DesktopApp::routes()`, and MiniOS dynamically handles the window mounting.

### Pattern B: Multi-Route & Decoupled CRUD Components
For apps with multiple views (e.g. `/contacts` list and `/contacts/create` form):
1. **Manifest (`ContactApp.php`)**:
   ```php
   public function routes(): array
   {
       return ['/contacts', '/contacts/create'];
   }
   ```
2. **Dedicated Components**:
   - `app/MiniOS/Contact/Livewire/ContactList.php`
   - `app/MiniOS/Contact/Livewire/ContactCreate.php`
3. **Shell View Switcher (`resources/views/apps/contact.blade.php`)**:
   ```blade
   <div class="h-full w-full overflow-hidden">
       @if ($view === 'create')
           <livewire:dynamic-component :is="\App\MiniOS\Contact\Livewire\ContactCreate::class" wire:key="contact-create" />
       @else
           <livewire:dynamic-component :is="\App\MiniOS\Contact\Livewire\ContactList::class" wire:key="contact-list" />
       @endif
   </div>
   ```

---

## 📦 App Distribution via ZIP & Control Panel

Want to distribute your custom app modules across teams or servers? Package them as a `.zip` archive structured like this:

```
my-pos-app.zip
├── manifest.json              <-- Metadata (id, name, icon, version, composer dependencies)
├── app/                       <-- POSApp.php & Livewire/POS.php
├── resources/views/           <-- Blade templates (apps/pos.blade.php)
├── database/migrations/       <-- Database migrations (optional, run automatically)
└── models/                    <-- Eloquent models (optional)
```

Open **Control Panel** (`/control-panel`), click **Install App (.zip)**, and upload:
- Files are extracted to the correct application paths.
- Migrations run automatically in the background.
- If dependencies are missing (e.g. `spatie/laravel-backup`), the Control Panel displays an **[Install via Composer]** button with a real-time terminal modal!

---

## ⚙️ Persistent Settings Helper (`os_setting`)

MiniOS includes a per-user, database-persisted setting store with automatic caching:

```php
// Retrieve setting with fallback
$theme = os_setting('appearance.theme', 'dark');

// Store setting for authenticated user
os_setting()->set('appearance.accent_color', 'emerald');
```

---

## 🧪 Testing & Code Style

Keep your codebase reliable and clean:

```bash
# Run test suite
php artisan test --compact

# Code styling with Laravel Pint
vendor/bin/pint --format agent
```

---

## 📄 License

MiniOS is open-source software licensed under the [MIT License](LICENSE). Feel free to use, modify, and build upon it for personal and commercial projects.

## 👤 Author

Crafted with coffee and passion by **[Novianto Rahmadi](https://github.com/novay)** ([novay@btekno.id](mailto:novay@btekno.id)). If you find this package helpful, give it a ⭐ Star on GitHub!
