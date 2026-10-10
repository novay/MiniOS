# MiniOS Desktop App Layouting System

MiniOS menyediakan set komponen arsitektur tata letak antarmuka desktop-grade (**Desktop Layout System**) yang dirancang khusus untuk mempermudah developer membangun aplikasi jendela (*windowed app*) dengan standar antarmuka modern seperti Windows 11 Fluent Design dan macOS.

Sistem layout ini membagi antarmuka menjadi 4 zona modular yang fleksibel dan **sepenuhnya opsional**. Developer bebas mengombinasikan bagian mana saja yang dibutuhkan oleh aplikasinya.

---

## 📐 Diagram Tata Letak (*Layout Architecture*)

```text
-------------------------------------------------
| [Menu] File Edit Window Help                  | <- Opsional
-------------------------------------------------
| [Sidebar]       | [Content] Konten Utama      |
| Navigasi Kiri   | (Wajib / Pusat Aplikasi)    |
| (Opsional &     |                             |
| Smooth Resize)  |                             |
|                 |                             |
|                 | ___________________________ |
|                 | [Statusbar] Statusbar fixed | <- Opsional
-------------------------------------------------
```

Arsitektur CSS Grid yang digunakan secara cerdas menyesuaikan diri (`auto-collapsing tracks`):
1. **Menu Bar (`<x-minios:desktop.menu>`)**: *(Opsional)* Membentang horizontal di bagian teratas jendela. Jika tidak digunakan, baris menu otomatis hilang (tinggi 0px) tanpa menyisakan ruang kosong.
2. **Sidebar (`<x-minios:desktop.sidebar>`)**: *(Opsional)* Berada di sebelah kiri. Jika tidak digunakan, kolom sidebar otomatis hilang (lebar 0px) dan area konten otomatis meluas 100% penuh.
3. **Content (`<x-minios:desktop.content>`)**: Area utama aplikasi yang fleksibel dan dilengkapi `@container` (Tailwind Container Queries).
4. **Statusbar (`<x-minios:desktop.statusbar>`)**: *(Opsional)* Berada di bagian bawah. Jika tidak digunakan, baris statusbar otomatis hilang tanpa menyisakan ruang kosong.

---

## 🚀 Fitur Unggulan

1. **Komponen Modular & Bebas Kombinasi (Opsional)**:
   - **Full Layout**: Menu Bar + Sidebar + Content + Statusbar (misal: *Katalog*, *Settings*).
   - **Tanpa Sidebar**: Menu Bar + Content + Statusbar (misal: *Notepad / TextEdit*, *Code Editor*).
   - **Tanpa Menu & Statusbar**: Sidebar + Content saja (misal: *SoundCloud Music Player*, *File Manager sederhana*).
   - **Hanya Konten**: Content saja (misal: *Kalkulator*, *Photo Viewer*, *Canvas Game*).

2. **Smooth Fluid Resizer (Sleek 1px Divider & Zero Layout Shift)**:
   - Lebar divider adalah garis elegan berukuran presisi 1px (`w-px`).
   - Lebar border **tidak berubah saat di-hover**, melainkan berubah warna menjadi lebih gelap/kontras (`hover:bg-neutral-400 dark:hover:bg-neutral-500`). Hal ini mencegah konten di sebelah kanan bergeser atau melompat (*zero layout shift*).
   - Area penangkapan kursor (*hit target*) diperluas secara transparan (`-left-1.5 -right-1.5`) agar mudah ditangkap kursor mouse tanpa mengorbankan estetika visual.
   - Tidak ada gangguan *snap to collapse* saat digeser mentok ke kiri—sidebar tetap tertahan pada batas minimumnya (`minWidth`).
   - Buka/tutup (*toggle collapse*) dilakukan secara eksplisit melalui:
     - Tombol toggle di sidebar (ikon `bars-3-bottom-left`)
     - Shortcut keyboard (misal: `⌘B` / `Ctrl+B`)
     - Menu aplikasi (`Tampilan > Buka/Tutup Sidebar`)
     - Klik dua kali (*double click*) pada pembatas geser (*resizer divider handle*).

3. **Tailwind Container Queries Built-in (`@container`)**:
   - Area `<x-minios:desktop.content>` secara otomatis dibungkus dengan utilitas `@container`.
   - Grid kartu dan elemen UI beradaptasi terhadap **ukuran jendela aplikasi**, bukan resolusi layar monitor. Cukup gunakan utilitas seperti `@[540px]:grid-cols-2` dan `@[860px]:grid-cols-3`.

4. **Alpine.js Reactive State Terintegrasi**:
   - Komponen induk `<x-minios:desktop>` mengelola state:
     - `sidebarCollapsed`: Status boolean apakah sidebar sedang tertutup (ikon ringkas) atau terbuka.
     - `toggleSidebar()`: Method untuk membuka/menutup sidebar.
     - `expandSidebar()`: Membuka sidebar ke lebar semula.
     - `collapseSidebar()`: Menutup sidebar.
     - `sidebarWidth`: Lebar aktif sidebar dalam satuan piksel.

---

## 🧩 Komponen Tersedia

Semua komponen dapat dipanggil menggunakan format `<x-minios:desktop.*>` maupun `<x-minios.desktop.*>`:

| Komponen | Status | Tag Blade | Deskripsi & Atribut Utama |
|---|---|---|---|
| **Desktop Shell** | Wajib | `<x-minios:desktop>` | Kontainer grid utama. Props: `width="260"`, `min-width="160"`, `max-width="420"`, `collapsed="false"` |
| **App Menu Bar** | **Opsional** | `<x-minios:desktop.menu>` | Bar horizontal teratas, tempat meletakkan menubar aplikasi (`<x-minios.menubar>`) |
| **Sidebar Navigation** | **Opsional** | `<x-minios:desktop.sidebar>` | Area navigasi kiri dengan resizer handle terintegrasi. Props: `resizable="true"`, `collapsed-width="w-16"` |
| **Main Content** | Inti | `<x-minios:desktop.content>` | Area utama aplikasi, otomatis dilengkapi `@container` dan scrollbar vertikal |
| **Status Bar** | **Opsional** | `<x-minios:desktop.statusbar>` | Bar informasi bawah jendela yang terkunci (*fixed*) |

---

## 🛠️ Contoh Variasi Penggunaan

### 1. Pola Lengkap (Menu + Sidebar + Konten + Statusbar)
*Contoh aplikasi: Katalog MiniOS, Settings, Control Panel.*

```blade
<x-minios:desktop app-id="katalog" width="260" min-width="180" max-width="380">
    {{-- 1. Menu Bar Aplikasi (Opsional) --}}
    <x-minios:desktop.menu>
        <x-minios.menubar>
            {{-- Menu File --}}
            <x-minios.menubar.menu label="{{ __('File') }}">
                <x-minios.menubar.item
                    @click="$dispatch('close-window', { id: 'katalog' })"
                    icon="x-mark"
                    shortcut="⌘W"
                >
                    {{ __('Tutup Jendela') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Menu View --}}
            <x-minios.menubar.menu label="{{ __('View') }}">
                <x-minios.menubar.item
                    @click="toggleSidebar()"
                    icon="bars-3-bottom-left"
                    shortcut="⌘B"
                >
                    <span x-text="sidebarCollapsed ? '{{ __('Buka Sidebar') }}' : '{{ __('Tutup Sidebar') }}'"></span>
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Menu Window --}}
            <x-minios.menubar.menu label="{{ __('Window') }}">
                <x-minios.menubar.item
                    wire:click="$refresh"
                    icon="arrow-path"
                    shortcut="⌘R"
                >
                    {{ __('Muat Ulang') }}
                </x-minios.menubar.item>

                <x-minios.menubar.separator />

                <x-minios.menubar.item
                    @click="toggleStatusbar()"
                    icon="chart-bar"
                    shortcut="⌘P"
                >
                    <span x-text="statusbarVisible ? '{{ __('Disable Status Bar') }}' : '{{ __('Enable Status Bar') }}'"></span>
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Menu Help --}}
            <x-minios.menubar.menu label="{{ __('Help') }}">
                <x-minios.menubar.item
                    @click="$dispatch('open-about')"
                    icon="information-circle"
                    shortcut="⌘A"
                >
                    {{ __('About') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>
        </x-minios.menubar>

        <div class="flex items-center gap-2 pr-1 text-[11px] text-neutral-400">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>
            <span>v1.0.0</span>
        </div>
    </x-minios:desktop.menu>

    {{-- 2. Sidebar Navigasi (Opsional) --}}
    <x-minios:desktop.sidebar>
        <div class="mb-4 flex items-center justify-between">
            <h2 x-show="!sidebarCollapsed" class="text-xs font-bold">Katalog</h2>
            <button type="button" @click="toggleSidebar()">
                <flux:icon name="bars-3-bottom-left" class="size-4" />
            </button>
        </div>
        <nav class="flex flex-1 flex-col gap-1">
            <button type="button" class="flex items-center gap-2 p-2 rounded-xl text-xs">
                <flux:icon name="sparkles" class="size-4" />
                <span x-show="!sidebarCollapsed">Jelajah</span>
            </button>
        </nav>
    </x-minios:desktop.sidebar>

    {{-- 3. Konten Utama (Container Queries Ready) --}}
    <x-minios:desktop.content>
        <div class="p-6">
            <div class="grid grid-cols-1 @[540px]:grid-cols-2 @[860px]:grid-cols-3 gap-4">
                {{-- Kartu konten --}}
            </div>
        </div>
    </x-minios:desktop.content>

    {{-- 4. Statusbar Fixed (Opsional) --}}
    <x-minios:desktop.statusbar>
        <span>Siap</span>
        <span>v1.0.0</span>
    </x-minios:desktop.statusbar>
</x-minios:desktop>
```

#### Ringkasan Standar Properti Menubar Item:
| Menu | Aksi | Icon | Shortcut | Handler | Penjelasan |
|---|---|---|---|---|---|
| **File** | Tutup Jendela | `x-mark` | `⌘W` | `@click="$dispatch('close-window', { id: 'katalog' })"` | Otomatis ditangkap Window Manager MiniOS |
| **View** | Buka/Tutup Sidebar | `bars-3-bottom-left` | `⌘B` | `@click="toggleSidebar()"` | Memanggil fungsi Alpine bawaan `<x-minios:desktop>` |
| **Window** | Muat Ulang | `arrow-path` | `⌘R` | `wire:click="$refresh"` | Me-refresh komponen Livewire tanpa reload browser |
| **Window** | Disable/Enable Status Bar | `chart-bar` | `⌘P` | `@click="toggleStatusbar()"` | Mengontrol visibilitas `<x-minios:desktop.statusbar>` |
| **Help** | About | `information-circle` | `⌘A` | `@click="$dispatch('open-about')"` | Menampilkan informasi/modal tentang aplikasi |


---

### 2. Pola Tanpa Sidebar (Menu + Konten + Statusbar)
*Contoh aplikasi: Text Editor / Notepad, Log Viewer.*

```blade
<x-minios:desktop>
    <x-minios:desktop.menu>
        <x-minios.menubar>
            <x-minios.menubar.menu label="File">
                <x-minios.menubar.item wire:click="save" shortcut="⌘S">Simpan</x-minios.menubar.item>
            </x-minios.menubar.menu>
        </x-minios.menubar>
    </x-minios:desktop.menu>

    <x-minios:desktop.content>
        <textarea class="w-full h-full p-4 bg-transparent resize-none border-none outline-none font-mono text-sm"></textarea>
    </x-minios:desktop.content>

    <x-minios:desktop.statusbar>
        <span>Baris 1, Kolom 1</span>
        <span>UTF-8</span>
    </x-minios:desktop.statusbar>
</x-minios:desktop>
```

---

### 3. Pola Tanya Menu & Statusbar (Sidebar + Konten)
*Contoh aplikasi: Music Player (SoundCloud), Chat App, Email Client sederhana.*

```blade
<x-minios:desktop width="240">
    <x-minios:desktop.sidebar>
        <div class="p-3">
            <h3 class="font-bold text-xs">Daftar Putar</h3>
        </div>
    </x-minios:desktop.sidebar>

    <x-minios:desktop.content>
        <div class="p-6">
            {{-- Pemutar musik dan track list --}}
        </div>
    </x-minios:desktop.content>
</x-minios:desktop>
```

---

### 4. Pola Minimalis (Hanya Konten)
*Contoh aplikasi: Kalkulator, Dialog Sederhana, Image Viewer, Game.*

```blade
<x-minios:desktop>
    <x-minios:desktop.content>
        <div class="flex h-full items-center justify-center p-4">
            {{-- Komponen aplikasi langsung memenuhi 100% canvas jendela --}}
            <h1 class="text-base font-medium">Kalkulator MiniOS</h1>
        </div>
    </x-minios:desktop.content>
</x-minios:desktop>
```

---

## 💡 Best Practices

1. **Gunakan `@container` Queries**: Hindari `md:` atau `lg:` untuk elemen di dalam `<x-minios:desktop.content>` agar kartu dan tombol tidak terpotong saat jendela diperkecil.
2. **Gunakan `x-show="!sidebarCollapsed"`**: Pasang direktif ini pada teks navigasi di sidebar agar otomatis berubah menjadi mode ikon (*icon-only rail*) yang rapi saat sidebar ditutup.
3. **Modals Scoped Dalam Jendela**: Letakkan modal dengan kelas `absolute inset-0 z-50` di dalam `<x-minios:desktop>` agar dialog konfirmasi tetap berada di dalam bingkai jendela aplikasi.

---

## ⌨️ Panduan Shortcut Keyboard

MiniOS membedakan shortcut keyboard menjadi 2 kategori:

### 1. Label Visual di Menu Bar (`shortcut="..."`)
Untuk menampilkan petunjuk pintasan keyboard di sebelah kanan item menu:
```blade
<x-minios.menubar.item wire:click="openUploadModal" icon="arrow-up-tray" shortcut="⌘O">
    {{ __('Pasang ZIP') }}
</x-minios.menubar.item>
```
*Atribut `shortcut` pada menubar hanya bertugas menampilkan teks badge tombol (misal `⌘O`, `Ctrl+S`, `Space`).*

### 2. Mendaftarkan Eksekusi Tombol Keyboard
Untuk membuat shortcut benar-benar berjalan saat ditekan, gunakan salah satu pendekatan berikut:

#### A. Alpine.js Keyboard Modifiers (Direkomendasikan di Level Jendela)
Tambahkan event listener keyboard langsung pada kontainer aplikasi Anda:
```blade
<div
    x-data="{ ... }"
    @keydown.cmd.b.window.prevent="toggleSidebar()"
    @keydown.ctrl.b.window.prevent="toggleSidebar()"
    @keydown.cmd.s.window.prevent="$wire.save()"
>
```

#### B. Membatasi Hanya untuk Jendela yang Sedang Aktif
Agar shortcut tidak tertukar antar jendela yang sedang terbuka di MiniOS, periksa `activeWindow`:
```blade
<div
    x-data="{ ... }"
    @keydown.window="(e) => {
        if (activeWindow !== 'katalog') return;
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            toggleSidebar();
        }
    }"
>
```

#### C. Shortcut Sistem Global (`minios.js`)
Shortcut tingkat desktop (seperti `Cmd+Space` untuk drawer aplikasi, `Cmd+F12` / `Cmd+F11` untuk volume master, dan `Cmd+W` untuk menutup jendela aktif) dikelola secara terpusat di `resources/js/minios.js` pada fungsi `initKeyboardShortcuts()`.

