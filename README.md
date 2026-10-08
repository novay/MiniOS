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

## 💥 Fitur (Bukan Kaleng-Kaleng)

- 🖥️ **Desktop Workspace Beneran**: Jendela ngambang bisa digeser, resize, snap tepi layar, minimize ke dock, maximize, dan z-index otomatis rapi.
- 🎛️ **Control Panel & App Installer**:
  - Pasang aplikasi baru semudah upload file `.zip`.
  - Migrasi database langsung jalan otomatis.
  - Cek dependensi Composer (ada tombol sakti **"Install via Composer"** langsung lewat modal GUI terminal realtime).
  - Uninstall bersih plus opsi rollback tabel database.
- 🛠️ **Aplikasi Bawaan Siap Tempur**:
  - 🌐 **Browser**: Peramban web mini dengan histori dan kontrol navigasi.
  - 📁 **Files**: File explorer penyimpanan lokal / S3 / BunnyCDN (grid & list view).
  - ⌨️ **Terminal**: Shell interaktif buat eksekusi perintah Artisan langsung di browser.
  - ⚙️ **Settings**: Atur wallpaper, dark/light mode, aksen warna, blur, dan posisi dock.
  - 📊 **Activity Monitor**: Task manager pantau RAM, disk, sama load server.
  - 🧮 **Calculator** & ℹ️ **About MiniOS**.
- 🔒 **Login & Lockscreen**: Terintegrasi penuh sama Laravel Fortify, support Passkey & Lock Screen interaktif.
- 🔗 **Deep-linking URL**: Buka aplikasi di jendela desktop otomatis update URL browser tanpa reload halaman.
- 🤖 **AI Agent Ready**: Pas diinstall langsung nyediain skill di `.agents/`, jadi AI assistant lu (Cursor, Windsurf, Antigravity, Copilot) langsung khatam cara bikinin lu aplikasi MiniOS.

---

## 🏛️ Struktur Folder

MiniOS itu modular abis. Aplikasi buatan lu cukup ngumpul di `app/MiniOS/{NamaApp}/`:

```
mini-os/
├── app/
│   └── MiniOS/
│       ├── Todo/                     <-- Modul Aplikasi Kustom Lu
│       │   ├── TodoApp.php           <-- Manifest / Kontrak Aplikasi
│       │   └── Livewire/
│       │       └── Todo.php          <-- Komponen Livewire
│       └── Kasir/                    <-- Contoh Aplikasi Lainnya
│           ├── KasirApp.php
│           └── Livewire/
│               └── Kasir.php
├── config/
│   └── minios.php                    <-- Konfigurasi & Registrasi
├── resources/
│   └── views/
│       └── apps/                     <-- Tampilan Blade Aplikasi
│           ├── todo.blade.php
│           └── kasir.blade.php
└── routes/
    └── web.php                       <-- Cukup panggil MiniOS::routes()
```

---

## ⚡ Cara Pasang (3 Menit Beres)

MiniOS bisa dipasang di aplikasi Laravel yang sudah berjalan maupun proyek baru dari nol.

### 🆕 Opsi 1: Mulai dari Fresh Install Laravel (Proyek Baru)

Kalo lu mau bikin dari kosongan, buat project Laravel baru pake installer resmi:

```bash
laravel new example
```

Pilih opsi di prompt interaktif terminal seperti ini:
```text
Do you want to use a starter kit? [Yes]
Which frontend stack should your starter kit use? [Livewire]
Which authentication provider do you prefer? [Laravel's built-in authentication]
Would you like to use single-file Livewire components? [Bebas]
Would you like to add teams support to your application? [Bebas]

Tunggu...

Which authentication features would you like to enable? [Bebas]

Selesai.
```

Begitu instalasi Laravel beres, masuk ke direktori proyek:
```bash
cd example
```

---

### 📦 Opsi 2 / Lanjutan: Pasang Package MiniOS

### 1. Tarik Package via Composer
```bash
composer require novay/minios
```

### 2. Eksekusi Installer & Migrasi
Gak perlu ngedit file ini-itu satu per satu, perintah ini otomatis nyiapin config, migrasi, aset, skill AI, sekaligus nyuntik alias di `vite.config.js`, `app.css`, dan `app.js`:

```bash
php artisan minios:install
php artisan migrate
npm run build
```

<details>
<summary><strong>🛠️ Apes Pas Install Otomatis? Nih Cara Manualnya (Frontend)</strong></summary>

<br>

> *Catatan: Bagian ini udah ditangani otomatis sama `minios:install`. Buka cuma kalau lu pake bundler kustom atau instalasinya gagal.*

**1. Pasang Alias di `vite.config.js`:**
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

**2. Daftarin Livewire & Alpine di `resources/js/app.js`:**
```javascript
import {
    Livewire,
    Alpine,
} from '../../vendor/livewire/livewire/dist/livewire.esm';
import minios from '@minios/minios';

Alpine.data('minios', minios);

Livewire.start();
```

**3. Impor Stylesheet di `resources/css/app.css`:**
```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/novay/minios/resources/css/minios.css';

@source '../views';
@source '../../vendor/novay/minios/resources/views/**/*.blade.php';
```
</details>

---

### 3. Rute Desktop & Autentikasi (Semuanya Udah Otomatis!)

Kabar gembira: perintah `php artisan minios:install` tadi udah **otomatis**:
- Menaruh `MiniOS::routes();` di baris **paling bawah** `routes/web.php`.
- Menaruh `MiniOS::fortify();` di dalam `boot()` pada `app/Providers/FortifyServiceProvider.php` (kalo Laravel Fortify terpasang).

Lu gak wajib ngetik apa-apa lagi! Tapi kalo mau cek manual atau butuh kustomisasi:

<details>
<summary><strong>🔍 Cek Rute di <code>routes/web.php</code></strong></summary>

```php
use Novay\MiniOS\Facades\MiniOS;

// Rute kustom lu yang lain...

// Tangkap semua rute desktop MiniOS (harus di paling bawah!)
MiniOS::routes();
```
</details>

<details>
<summary><strong>🔍 Cek Provider di <code>app/Providers/FortifyServiceProvider.php</code></strong></summary>

```php
use Novay\MiniOS\Facades\MiniOS;

public function boot(): void
{
    // ...
    MiniOS::fortify();
}
```
</details>

---

### 🛑 Mau Nonaktifkan (Disable) UI Autentikasi MiniOS?

Kalo aplikasi lu mau tetap pake tampilan login bawaan lama (Breeze, Jetstream, Filament, atau custom auth) dan **gak mau** pake UI auth MiniOS:

- **Cara 1: Lewat Config (Rekomendasi)**
  Buka `config/minios.php`, set:
  ```php
  'fortify_views' => false,
  ```

- **Cara 2: Lewat Provider**
  Buka `app/Providers/FortifyServiceProvider.php`, hapus atau komentari baris:
  ```php
  // MiniOS::fortify();
  ```

*Desktop MiniOS bakal tetap jalan lancar jaya membaca sesi login lama lu tanpa masalah!*

#### ⚠️ "Woi, kok abis register/login malah mentok di `/email/verify`?"
Santai, itu kelakuan bawaan Fortify kalau fitur verifikasi email-nya nyala tapi SMTP mailer lu belum diset. Pilih salah satu solusinya:

1. **Jalur Santai / Dev Lokal (Matiin Verifikasi):**
   - Buka `config/fortify.php`, matiin (komentari) baris ini:
     ```php
     // Features::emailVerification(),
     ```
   - Buka `config/minios.php`, pastiin middleware-nya gak maksa `verified`:
     ```php
     'middleware' => ['web', 'auth'],
     ```
   *Beres! Abis login langsung nyelonong masuk desktop tanpa hambatan.*

2. **Jalur Beneran / Production (Pake Email Asli):**
   - Set config email di `.env` lu (atau set `MAIL_MAILER=log` pas testing lokal, link verifikasinya tinggal lu intip di `storage/logs/laravel.log`).
   - Begitu link diklik, otomatis langsung landing di desktop MiniOS (`/?verified=1`).

3. **Jalur Barbar (Verifikasi via Terminal):**
   ```bash
   php artisan tinker --execute "App\Models\User::first()->markEmailAsVerified();"
   ```

---

### ❓ "Kalo dipasang di aplikasi Laravel yang sudah berjalan (Existing Project), bakal ngeganggu/ngerusak gak?"

**Jawaban singkat: AMAN BANGET, GAK AKAN NGERUSAK.**

Tapi pahami 4 poin to the point ini:

1. **Rute Lama Gak Bakal Bentrok:**
   Pastikan selalu menaruh baris `MiniOS::routes();` di baris **paling bawah** `routes/web.php`. Rute-rute aplikasi lu yang sudah ada (seperti `/admin`, `/api`, `/checkout`, `/dashboard`, `/blog`) akan tetap diproses lebih dulu oleh Laravel tanpa terganggu sama sekali.
   *(Catatan: Halaman root `/` secara default bakal diarahkan ke Desktop MiniOS. Kalo homepage publik lama lu mau tetap di `/`, deklarasikan rute homepage lama sebelum baris `MiniOS::routes()`)*.

2. **Database 100% Aman:**
   Migrasi MiniOS cuma nambah 1 tabel baru `desktop_settings` dan 1 kolom nullable `locked_at` di tabel `users`. Gak ada tabel lama yang diubah paksa atau dihapus.

3. **Autentikasi Fleksibel:**
   MiniOS cuma butuh session login bawaan Laravel (`auth`). Kalo aplikasi lu udah punya sistem login sendiri (Breeze, Jetstream, Filament, atau custom auth), lu **gak wajib** panggil `MiniOS::fortify()`. User yang sudah login lewat auth lama lu bisa langsung membuka desktop MiniOS!

4. **Vite & Aset Aman:**
   Script `minios:install` cuma nambah alias di `vite.config.js` dan mengimpor CSS/JS pelengkap di `app.css` & `app.js`. Style Tailwind dan script yang sudah ada sebelumnya tetap berjalan normal.

---

## 🔨 Cara Bikin Aplikasi Sendiri

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

### Cara 2: Suruh AI Kerja Rodi (Paling Enak & Cepet)
Karena perintah `minios:install` tadi otomatis nyelipin skill panduan ke `.agents/skills/minios-app-development/`, AI coding assistant lu (**Google Antigravity**, **Cursor**, **Windsurf**, **Claude Code**, atau **Copilot**) udah paham jeroan arsitektur MiniOS.

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

#### 1. Bikin Komponen Livewire
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

#### 2. Bikin Manifest Aplikasi (`DesktopApp`)
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
