# MiniOS - Web Desktop OS buat Laravel

<p align="center">
  <img src="https://raw.githubusercontent.com/novay/minios/main/resources/img/images/logo.png" alt="MiniOS Logo" width="160" />
</p>

<p align="center">
  <strong>Sulap web Laravel lu jadi OS desktop multi-window. Gak pake ribet, sat-set langsung jalan.</strong>
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
  <strong>Bahasa:</strong>
  <a href="README.md"><strong>Bahasa Indonesia</strong></a> |
  <a href="README.en.md">English</a>
</p>

---

## 🥱 Ngapain Bikin Dashboard yang Gitu-Gitu Aja?

Jujur aja, dashboard admin jaman now ngebosenin parah: sidebar item di kiri, navbar di atas, isinya tabel putih polosan. 

**MiniOS** nyulap aplikasi Laravel lu jadi Web Desktop OS interaktif berasa pake macOS atau Windows. Jendelanya bisa digeser (*draggable*), di-resize, di-minimize ke dock, ada task manager, terminal interaktif, sampe fitur pasang aplikasi baru tinggal lempar file `.zip`.

Ditenagai langsung sama **Laravel**, **Livewire**, **Alpine.js**, **Tailwind CSS v4**, dan **Vite**. Enteng, reaktif, gak ada dependensi framework JS ribet.

---

## ⚡ Cara Pasang (<1 Menit Beres)

### 1. Install via Composer
```bash
composer require novay/minios
```

### 2. Install, Migrasi & Run

```bash
php artisan minios:install
php artisan migrate
npm run build
```

---

### 3. Selesai

Lu gak wajib ngetik apa-apa lagi! 
*Desktop MiniOS bakal tetap jalan lancar jaya membaca sesi login lama lu tanpa masalah!*

---

### Mentok di `/email/verify`?
Itu kelakuan bawaan Fortify. Langsung gini saja: 

Buka `config/fortify.php`, matiin (komentari) baris ini:
```php
// Features::emailVerification(),
```

<br/>

##  Bikin Kustom Aplikasi di MiniOS

Ada 3 opsi, pilih yang paling cocok sama gaya lu:

### Cara 1: Pake Artisan Generator (Paling Sat-Set)
Tinggal ketik:
```bash
php artisan minios:make-app Kasir --icon=shopping-cart --pinned
```
Bum! Langsung brojol 3 file:
1. `app/MiniOS/Kasir/KasirApp.php` (Manifest aplikasi)
2. `app/MiniOS/Kasir/Livewire/Kasir.php` (Komponen Livewire)
3. `resources/views/apps/kasir.blade.php` (Tampilan Blade)

---

### Cara 2: Suruh AI (Paling Enak & Cepet)
Proses instalasi sudah nyelipin skill untuk agent, jadi AI *coding assistant* lu (**Google Antigravity**, **Cursor**, **Windsurf**, **Claude Code**, atau **Copilot**) udah paham jeroan arsitektur MiniOS.

Tinggal suruh pake prompt santai begini:

> *"Bro, buatin aplikasi Kasir (POS) di MiniOS. Ada keranjang belanja, pencarian barang, sama modal cetak struk."*

**Yang bakal dikerjain otomatis sama AI:**
- Bikin kerangka file via `minios:make-app`.
- Buatin migrasi database + Model Eloquent + jalanin migrasinya.
- Ngoding logic reaktif Livewire-nya.
- Mendesain UI Blade ala desktop yang clean, responsif, dan support mode gelap.
- Nulis testing Pest-nya sekalian!

---

### Cara 3: Bikin Manual (Kalo Lu Gabut & Doyan Ngetik)

<details>
<summary>
<strong>1. Bikin Komponen Livewire</strong>
</summary>
Buat file di `app/MiniOS/Todo/Livewire/Todo.php`:
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

Bikin tampilannya di `resources/views/apps/todo.blade.php`:
```blade
<div class="h-full flex flex-col bg-neutral-900 text-white p-4">
    <div class="flex gap-2 mb-4">
        <input 
            type="text" 
            wire:model="newTask" 
            wire:keydown.enter="addTask" 
            placeholder="Tulis catatan..." 
            class="flex-1 bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <button wire:click="addTask" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            Tambah
        </button>
    </div>

    <ul class="flex-1 overflow-y-auto space-y-2">
        @forelse ($tasks as $task)
            <li class="bg-neutral-800/60 p-3 rounded-lg text-sm flex items-center justify-between border border-white/5">
                <span>{{ $task }}</span>
            </li>
        @empty
            <li class="text-neutral-500 text-sm italic text-center py-8">Belum ada tugas. Santai dulu gak sih?</li>
        @endforelse
    </ul>
</div>
```
</details>

<details>
<summary>
<strong>2. Bikin Manifest Aplikasi (`DesktopApp`)</strong>
</summary>
Buat file di `app/MiniOS/Todo/TodoApp.php`:

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
        return 'Catatan Tugas';
    }

    public function icon(): string
    {
        return 'queue-list'; // Nama icon heroicon atau URL gambar
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
        return true; // Pin ke dock bawah
    }

    public function component(): ?string
    {
        return Todo::class;
    }

    public function window(): WindowConfig
    {
        return WindowConfig::make()
            ->size(850, 550)  // Ukuran default
            ->min(500, 350)   // Minimal resize
            ->max(1400, 900); // Maksimal resize
    }
}
```

*Selesai! MiniOS otomatis nemuin dan nampilin aplikasi lu di desktop.*

</details>

---

## 🔀 Pola Routing & Komponen

### Pola A: Native Desktop App (Rekomendasi)
Gak perlu bikin route manual di `routes/web.php`. Daftarin aja URL-nya di `DesktopApp::routes()`, MiniOS yang bakal nangkep dan ngebuka jendelanya secara dinamis.

### Pola B: Multi-Route & Decoupled CRUD Components
Kalo aplikasi lu punya beberapa halaman (misal: `/contacts` buat daftar, dan `/contacts/create` buat form baru):
1. **Manifest (`ContactApp.php`)**:
   ```php
   public function routes(): array
   {
       return ['/contacts', '/contacts/create'];
   }
   ```
2. **Pisahin Komponen Livewire**:
   - `app/MiniOS/Contact/Livewire/ContactList.php`
   - `app/MiniOS/Contact/Livewire/ContactCreate.php`
3. **Switcher View (`resources/views/apps/contact.blade.php`)**:
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

## 📦 Distribusi App via ZIP & Control Panel

Punya modul aplikasi yang mau lu bagiin ke temen atau pindah server? Bungkus aja jadi `.zip` dengan susunan begini:

```
aplikasi-kasir.zip
├── manifest.json              <-- Metadata (id, nama, icon, versi, dependensi composer)
├── app/                       <-- Berisi KasirApp.php & Livewire/Kasir.php
├── resources/views/           <-- Berisi template apps/kasir.blade.php
├── database/migrations/       <-- File migrasi (opsional, otomatis dijalankan)
└── models/                    <-- File model Eloquent (opsional)
```

Tinggal buka **Control Panel** di desktop MiniOS (`/control-panel`), klik **Pasang Aplikasi (.zip)**, lalu upload:
- Otomatis diekstrak ke direktori yang pas.
- Migrasi database langsung dieksekusi di background.
- Kalo butuh package luar (misal: `spatie/laravel-backup`), Control Panel bakal nampilin tombol **[Pasang via Composer]** lengkap sama terminal interaktif realtime!

---

## ⚙️ Helper Pengaturan (`os_setting`)

MiniOS nyediain helper pengaturan database per-user dengan cache otomatis:

```php
// Ambil setting (plus nilai default kalo belum ada)
$theme = os_setting('appearance.theme', 'dark');

// Simpan setting buat user yang lagi login
os_setting()->set('appearance.accent_color', 'emerald');
```

---

## 🧪 Testing & Code Style

Biar gak ada drama bug di production:

```bash
# Tes fitur
php artisan test --compact

# Rapihin format kode
vendor/bin/pint --format agent
```

---

## 📄 Lisensi

MiniOS adalah software open-source berlisensi [MIT License](LICENSE). Bebas lu pake, acak-acak, dan kembangin buat project pribadi maupun komersial.

## 👤 Pembuat

Dibuat dengan kopi dan cinta oleh **[Novianto Rahmadi](https://github.com/novay)** ([novay@btekno.id](mailto:novay@btekno.id)). Kalo ngebantu kerjaan lu, jangan lupa lempar ⭐ Star di GitHub ya cuy!
