# Changelog - MiniOS Katalog (App Store & Marketplace)

Semua perubahan, penambahan fitur, dan perbaikan untuk aplikasi **Katalog** MiniOS didokumentasikan di berkas ini.

Format changelog ini mengacu pada prinsip [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mengikuti [Semantic Versioning](https://semver.org/).

---

## [1.0.0] - 2026-10-10

Rilis resmi pertama aplikasi **Katalog MiniOS** dengan antarmuka Fluent Windows 11 terpadu ala Settings App, sistem manajemen aplikasi modular, serta kesiapan integrasi API ekosistem cloud marketplace.

### ✨ Fitur Baru (Added)

#### 1. Navigasi & Tampilan Sidebar 2-Kolom (Fluent Settings Pattern)
- Mengadopsi arsitektur antarmuka dua kolom menyerupai *Settings App* dengan sidebar navigasi di sisi kiri dan konten dinamis di panel kanan.
- Menu navigasi utama:
  - **Jelajah**: Showcase unggulan, *Weekly Spotlight Hero Banner*, *Top Charts* (ranking #1 hingga #6), *Editor's Choice*, serta promo tema desktop.
  - **Aplikasi**: Galeri template aplikasi dengan filter kategori (*Productivity*, *Media*, *Developer*, *Games*, *Utilities*), pencarian cepat, rating, unduhan, dan tombol pasang.
  - **Themes**: Showcase tema desktop visual (*Fluent Mica*, *Cyberpunk Neon 2077*, *Nordic Frost*, *macOS Sonoma Glass*, *Tokyo Night*, *Retro 95*) lengkap dengan kartu mockup OS dan palet swatch warna.
  - **Installed App**: Halaman pengelola seluruh aplikasi aktif (sistem dan kustom).
- Tombol aksi cepat *Pasang dari ZIP* dan indikator ukuran total penyimpanan aplikasi di bagian bawah sidebar.

#### 2. Manajemen Aplikasi Terpasang (Installed Apps)
- **Tile Kartu Aplikasi**: Tampilan daftar aplikasi rapi dengan nama, ID, versi, ukuran penyimpanan, dan status tipe (*System Core* vs *Custom*).
- **Inspeksi Arsitektur Modular (Accordion Details)**:
  - **Komponen Arsitektur**: Menampilkan FQCN kelas manifest, Livewire component name, entrypoint route, dan waktu instalasi.
  - **Rute Terdaftar**: Daftar endpoint route yang diekspos oleh aplikasi.
  - **Database Migrations & Models**: Pemeriksaan status migrasi database (*Applied* / *Pending*) serta model Eloquent yang terhubung.
  - **Dependencies (Composer)**: Deteksi otomatis apakah paket Composer eksternal yang dibutuhkan aplikasi telah terpasang di vendor.

#### 3. Instalasi & Penghapusan Paket Mandiri
- **1-Click ZIP Installer**: Fasilitas mengunggah dan mengekstrak berkas `.zip` package aplikasi MiniOS yang otomatis memvalidasi manifest, mendaftarkan route, dan menjalankan migrasi database.
- **GUI Composer Terminal**: Modal CLI interaktif untuk mengeksekusi instalasi paket Composer yang hilang secara langsung dari antarmuka GUI tanpa harus beralih ke terminal manual.
- **Safe App Uninstallation**: Dialog konfirmasi pencabutan aplikasi kustom yang membersihkan berkas controller, model, view, serta secara otomatis melakukan rollback migrasi database terkait.

#### 4. Window-Scoped Modals (Desktop Sheet Modal)
- Seluruh dialog modal (*Upload ZIP*, *Composer Installer*, *Uninstall Confirmation*) diisolasi ke dalam kanvas window aplikasi (`absolute inset-0 z-50` di dalam `katalog/modals/`).
- Dialog modal tidak lagi menutupi layar desktop penuh, memungkinkan jendela Katalog tetap dapat dipindahkan (*draggable*) saat modal aktif.

#### 5. Internasionalisasi & Lokalisasi Dinamis
- Dukungan multibahasa penuh (Bahasa Indonesia & English) menggunakan trait `HasTranslations`.
- Sinkronisasi otomatis saat bahasa sistem diubah melalui Settings.

---

### 📌 Persiapan Rilis Berikutnya (Planned for Future Releases)
- Integrasi API live marketplace ke `https://minios.btekno.id` untuk instalasi aplikasi remote dan sinkronisasi lisensi.
- Toggle sidebar collapse (mode ikon dengan tooltip mengambang).
- Fasilitas penggantian ikon aplikasi kustom dengan opsi kembali ke default (*revert*).
