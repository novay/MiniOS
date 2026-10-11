# MiniOS Window Manager — Fase 3

Fase 3 dibangun di atas Fase 1–2; kontrak `DesktopApp` dan `WindowConfig` **tidak berubah**. Fase ini menambahkan Sidebar Builder, route-aware ManagedAppContent, dan CRUD Scaffold Generator. Placeholder PHP/Blade dihasilkan **ke project Laravel pemakai**, bukan ke direktori package.

## Instalasi / upgrade

1. Ganti package menggunakan `minios-phase3-full.zip`, atau ekstrak `minios-phase3-patch.zip` ke root package MiniOS Fase 2 (patch memiliki struktur `src/`, `resources/`, `database/`, dst. tanpa folder pembungkus).
2. Pada root project Laravel jalankan:

   ```bash
   composer dump-autoload
   php artisan migrate
   php artisan optimize:clear
   ```

3. Definisikan Gate `minios.manage-apps` bagi admin/developer, sesuai Fase 1.
4. Pada **Window Manager**, pilih aplikasi custom → **App Shell**: `Managed`, `Enable Sidebar = true` → simpan. Buka **Sidebar & CRUD** → **Mulai Edit** → tambah item `Default` atau `CRUD`.
5. Untuk item CRUD yang telah disimpan, klik **Generate**. Reload desktop untuk melihat navigasi. Implementasikan model, form, query, validasi, dan record-level authorisation dalam PHP/Blade hasil generate.

## Konfigurasi keamanan generator

Web CRUD scaffold membuat berkas PHP pada server. Ini **hanya** diperbolehkan oleh `minios.manage-apps` dan ketika `minios.allow_web_scaffolding` bernilai `true` (default hanya environment `local`). Di server produksi, pilih salah satu: biarkan dinonaktifkan dan generate di lingkungan development, atau opt-in secara eksplisit:

```dotenv
MINIOS_ALLOW_WEB_SCAFFOLDING=true
```

**Jangan aktifkan pada public multi-user production** tanpa pemeriksaan deployment, keamanan filesystem, dan otorisasi admin yang ketat. Generator menolak aplikasi core dan manifest di luar `App/MiniOS/{Name}/{Name}App.php`, melakukan exclusive file creation (`fopen(...,'x')`), menolak konflik file, dan tidak menghapus/menimpa source developer. Folder `app/` dan `resources/views/` harus writable jika generate dilakukan dari aplikasi web.

## Menu sidebar

Tabel `minios_app_sidebar_items` sudah ada sejak Fase 1. Migration baru Fase 3 membuat `minios_app_sidebar_states`: ketika app belum pernah dikustomisasi, `Home` tersedia secara virtual. **Mulai Edit** membuat Home sebagai record; bila developer kemudian menghapus semua item, sidebar tetap kosong (tidak kembali diam-diam ke Home). Reset mengembalikan ke default virtual.

| Kolom | Catatan |
|---|---|
| `slug` | Unik per aplikasi; menentukan folder/class CRUD |
| `type` | `default` atau `crud` |
| `icon`, `icon_color` | Heroicon + kelas warna terbatas Tailwind |
| `label`, `description`, `badge` | Informasi visual |
| `permissions` | Array Laravel Gate ability; semuanya harus diizinkan |
| `route_prefix` | Path internal, wajib berada di bawah entry aplikasi |
| `enabled`, `sort_order` | Visibilitas dan urutan |
| `options` | Status dan daftar file hasil generate |

Saat `sidebar_enabled=true`, MiniOS menampilkan sidebar dari database (atau default), dan menerapkan check permission **di server** untuk setiap item. Route yang tak berizin tidak dirender oleh `ManagedAppContent`; menyembunyikan menu bukan pengganti otorisasi record/model di Livewire component developer.

## Navigasi dan komponen yang digenerate

Untuk aplikasi `Todo` yang memiliki `entry() === '/todo'` dan CRUD item `slug=tasks`, `route_prefix=/todo/tasks`:

| Route | Komponen Livewire | View |
|---|---|---|
| `/todo/tasks` | `Tasks/Index.php` | `tasks/index.blade.php` |
| `/todo/tasks/create` | `Tasks/Create.php` | `tasks/create.blade.php` |
| `/todo/tasks/{recordId}/edit` | `Tasks/Edit.php` | `tasks/edit.blade.php` |
| `/todo/tasks/{recordId}` | `Tasks/Show.php` | `tasks/show.blade.php` |

PHP: `app/MiniOS/Todo/Livewire/Tasks/{Index,Create,Edit,Show}.php`.

Blade: `resources/views/apps/todo/tasks/{index,create,edit,show}.blade.php`.

`recordId` adalah string max 100 karakter (`[A-Za-z0-9_-]`) dan tersedia di `Edit` dan `Show` sebagai public Livewire property. Generator sengaja **tidak membuat tabel/model/CRUD database secara otomatis**; isi sesuai domain aplikasi. Folder root untuk Default adalah `resources/views/apps/todo/index.blade.php`; generator `minios:make-app` baru menggunakan lokasi ini. Aplikasi lama yang memakai view `apps.todo` tetap tidak dimodifikasi.

Akses halaman yang dipetakan dari URL menggunakan `ManagedAppContent` di dalam shell dan mengirim `minios-app-navigate`. URL browser di-update dengan `update-window-url`; pada browser back/forward, router menyinkronkan kembali route dalam window yang sama. Route yang tidak dikenal / tidak diizinkan tidak akan merender komponen CRUD.

**Catatan prefix:** Bila `MINIOS_PREFIX=desktop`, jalur aktual menjadi `/desktop/todo/tasks` dan seterusnya. Jangan tambahkan prefix tersebut ke kolom route Sidebar Builder.

## Managed vs Embedded

- **Managed**: jika `shell_mode=managed` dan `sidebar_enabled=true`, AppShell otomatis merender `<x-minios.sidebar.managed :app-id="$appId" />` pada sidebar dan `ManagedAppContent` pada content. Menubar dari Fase 2 tetap berfungsi.
- **Embedded**: aplikasi lama yang sudah menyusun `<x-minios:desktop>` sendiri **tidak dibungkus**. Untuk menggunakan sidebar baru, developer dapat menempatkan `<x-minios.sidebar.managed app-id="todo" />` pada `<x-minios:desktop.sidebar>` dan `ManagedAppContent` di `<x-minios:desktop.content>` secara eksplisit. Jika tidak, komponen sidebar lama tetap digunakan. Ini mencegah sidebar ganda dan menjaga kompatibilitas.
- Statusbar dan menu Fase 2 tidak diganti oleh Fase 3.

## Batasan dan pengujian

- Urutan item memakai tombol naik/turun (drag-and-drop belum dibuat).
- CRUD generator menghasilkan placeholder UI dan Livewire yang dapat dilanjutkan developer; belum otomatis membuat policy/model/schema database.
- Menghapus item dari database **tidak menghapus** source yang digenerate. Mengubah `slug`, jenis, atau `route_prefix` pada item hasil generate tidak diizinkan agar navigasi tetap konsisten.
- Bila file yang akan digenerate sudah ada, generator menolak seluruh operasi dan tidak menimpa source developer.
- Paket mandiri yang diterima **tidak menyertakan host Laravel, vendor, atau database**, sehingga browser E2E, migrasi database sebenarnya, dan Livewire browser interaction harus dites setelah integrasi. Smoke test PHP lokal tersedia: `php tests/smoke-phase3.php` serta tes Fase 1/2.
