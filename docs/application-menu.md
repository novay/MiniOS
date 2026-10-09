# MiniOS Application Menu (App Menubar)

MiniOS menyediakan sistem **Application Menu (App Menubar)** desktop-grade seperti standar antarmuka aplikasi Windows 11 dan macOS (misal: `File`, `Edit`, `View`, `Help`). Fitur ini memungkinkan setiap mini-app memiliki menu bar horizontal dengan dropdown interaktif, sub-menu bertingkat (*flyout*), item checkbox/radio, shortcut keyboard, ikon, pemisah (*separator*), serta status *danger* dan *disabled*.

---

## 🚀 Fitur Utama

1. **Desktop Menu Behavior**:
   - Menu dropdown terbuka saat diklik.
   - **Hover-to-switch**: Saat salah satu menu sedang terbuka, menggeser kursor (*hover*) ke menu lain akan langsung membuka menu tersebut tanpa perlu klik ulang (perilaku standar desktop OS).
   - **Click-outside & Escape**: Menutup dropdown otomatis jika pengguna mengklik di luar area menu atau menekan tombol `Esc`.
2. **WinUI & macOS Aesthetics**:
   - Tampilan *acrylic glass* dengan `backdrop-blur-2xl`, border halus, dan bayangan lembut.
   - Highlight seleksi bergaya modern Windows 11 (latar belakang biru dengan teks kontras tinggi).
3. **Sub-Menu Bertingkat (*Nested Flyouts*)**:
   - Mendukung sub-menu yang mekar ke arah kanan saat kursor diarahkan ke item induknya.
4. **Item States & Rich Controls**:
   - **Item Aksi**: Tombol aksi standar dengan ikon dan petunjuk shortcut keyboard (misal `Ctrl+P`, `Ctrl+C`).
   - **Submenu**: Menampung daftar aksi turunan dengan indikator panah kanan (`chevron-right`).
   - **Checkbox**: Menampilkan tanda centang (`✓`) sesuai status aktif/nonaktif.
   - **Radio**: Menampilkan indikator titik bulat (`•`) untuk opsi pilihan tunggal.
   - **Separator**: Garis pembatas horizontal antar grup menu.
   - **Danger / Destructive**: Teks merah untuk aksi berbahaya (seperti *Delete*, *Reset*).
   - **Disabled**: Status nonaktif dengan transparansi dan pemblokiran klik otomatis.
5. **Dukungan Penuh Livewire & Alpine.js**:
   - Bekerja mulus dengan `wire:click="..."`, `@click="..."`, dan atribut tautan `href="..."`.

---

## 🧩 Komponen Tersedia

Semua komponen dapat dipanggil menggunakan prefix Blade `<x-minios.menubar.*>`:

| Komponen | Tag | Keterangan & Props Utama |
|---|---|---|
| **Menubar Container** | `<x-minios.menubar>` | Kontainer bar horizontal yang mengelola state menu aktif |
| **Menu Dropdown** | `<x-minios.menubar.menu>` | Dropdown menu utama. Props: `label="File"`, `id="..."` (opsional) |
| **Menu Item** | `<x-minios.menubar.item>` | Item aksi. Props: `icon="..."`, `shortcut="Ctrl+S"`, `disabled`, `danger`, `href` |
| **Submenu Flyout** | `<x-minios.menubar.submenu>` | Submenu bertingkat. Props: `label="..."`, `icon="..."`, `disabled` |
| **Checkbox Item** | `<x-minios.menubar.checkbox>` | Item dengan tanda centang. Props: `:checked="true"`, `shortcut="..."`, `disabled` |
| **Radio Item** | `<x-minios.menubar.radio>` | Item dengan tanda titik radio. Props: `:checked="true"`, `shortcut="..."`, `disabled` |
| **Separator** | `<x-minios.menubar.separator />` | Garis pembatas horizontal antar kelompok item |

---

## 🛠️ Contoh Penggunaan

### 1. Struktur Standar dalam Header Aplikasi

Berikut adalah contoh implementasi lengkap menubar pada header window mini-app:

```blade
<header class="flex h-10 shrink-0 items-center justify-between border-b border-black/10 dark:border-white/10 px-3 bg-white/80 dark:bg-[#18181b]/95 backdrop-blur-md">
    <div class="flex items-center gap-3">
        {{-- App Logo & Title --}}
        <div class="flex items-center gap-2">
            <img src="{{ asset('minios/images/ic-app.webp') }}" class="size-5 object-contain" alt="App" />
            <span class="text-xs font-semibold leading-none text-neutral-800 dark:text-white">Editor</span>
        </div>

        <div class="h-4 w-px bg-neutral-200 dark:bg-white/10"></div>

        {{-- Application Menubar --}}
        <x-minios.menubar>
            {{-- Menu File --}}
            <x-minios.menubar.menu label="{{ __('File') }}">
                <x-minios.menubar.item
                    wire:click="createNew"
                    icon="document-plus"
                    shortcut="Ctrl+N"
                >
                    {{ __('Dokumen Baru') }}
                </x-minios.menubar.item>

                <x-minios.menubar.item
                    wire:click="openFile"
                    icon="folder-open"
                    shortcut="Ctrl+O"
                >
                    {{ __('Buka File...') }}
                </x-minios.menubar.item>

                <x-minios.menubar.separator />

                {{-- Submenu --}}
                <x-minios.menubar.submenu label="{{ __('Ekspor Sebagai') }}" icon="arrow-up-tray">
                    <x-minios.menubar.item wire:click="exportPdf">
                        {{ __('Dokumen PDF (.pdf)') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.item wire:click="exportMarkdown">
                        {{ __('Markdown (.md)') }}
                    </x-minios.menubar.item>
                </x-minios.menubar.submenu>

                <x-minios.menubar.separator />

                <x-minios.menubar.item
                    wire:click="save"
                    icon="document-check"
                    shortcut="Ctrl+S"
                >
                    {{ __('Simpan') }}
                </x-minios.menubar.item>

                <x-minios.menubar.item
                    wire:click="deleteDocument"
                    icon="trash"
                    danger
                >
                    {{ __('Hapus Dokumen') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Menu Tampilan --}}
            <x-minios.menubar.menu label="{{ __('Tampilan') }}">
                <x-minios.menubar.checkbox
                    wire:click="toggleWordWrap"
                    :checked="$wordWrap"
                >
                    {{ __('Word Wrap') }}
                </x-minios.menubar.checkbox>

                <x-minios.menubar.separator />

                <x-minios.menubar.radio
                    wire:click="setFontSize('small')"
                    :checked="$fontSize === 'small'"
                >
                    {{ __('Font Kecil (12px)') }}
                </x-minios.menubar.radio>

                <x-minios.menubar.radio
                    wire:click="setFontSize('medium')"
                    :checked="$fontSize === 'medium'"
                >
                    {{ __('Font Sedang (14px)') }}
                </x-minios.menubar.radio>
            </x-minios.menubar.menu>

            {{-- Menu Bantuan --}}
            <x-minios.menubar.menu label="{{ __('Bantuan') }}">
                <x-minios.menubar.item
                    href="https://btekno.id/docs"
                    target="_blank"
                    icon="arrow-top-right-on-square"
                >
                    {{ __('Dokumentasi Online') }}
                </x-minios.menubar.item>
                <x-minios.menubar.separator />
                <x-minios.menubar.item
                    @click="$wire.dispatch('toast-info', { message: 'Editor v1.0 untuk MiniOS' })"
                    icon="information-circle"
                >
                    {{ __('Tentang Aplikasi') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>
        </x-minios.menubar>
    </div>
</header>
```

---

### 2. Studi Kasus Nyata: Aplikasi SoundCloud

Pada aplikasi **SoundCloud** bawaan MiniOS (`packages/novay/minios/resources/views/apps/soundcloud.blade.php`), Application Menu diterapkan untuk mengelola playlist dan pemutar:

- **Menu `Playlist`**:
  - `Ganti Playlist...` (`wire:click="openConfig"`)
  - Submenu `Playlist Contoh` $\rightarrow$ `AI / Cyberpunk Mix` (`wire:click="useSample"`)
  - `Salin URL Player` (menyalin ke clipboard via Alpine/JavaScript)
  - `Hapus / Reset Playlist` (status `danger` dengan konfirmasi)
- **Menu `Pemutar`**:
  - `Audio Persisten (Latar Belakang)` (checkbox aktif)
  - `Muat Ulang Pemutar`
- **Menu `Bantuan`**:
  - `Buka SoundCloud.com` (tautan eksternal tab baru)
  - `Tentang SoundCloud MiniOS`
