# MiniOS Window Manager — Fase 2

Fase 2 dibangun **di atas Fase 1** (tidak kompatibel dengan package yang belum memasang Fase 1). Kontrak `DesktopApp` **tidak berubah**; aplikasi dan layout lama tetap berjalan. Fase ini membawa `AppMenuService`, dua mode AppShell, builder Livewire, dan action dispatcher deklaratif.

## Upgrade

1. Pakai `minios-phase2-full.zip` untuk mengganti package, atau salin isi `minios-phase2-patch.zip` ke direktori root package **hasil Fase 1**. Jangan salin patch ke root project Laravel jika package berada di `packages/novay/minios`.
2. Dari root project Laravel jalankan `composer dump-autoload`, `php artisan migrate`, dan `php artisan optimize:clear`.
3. Definisikan Laravel Gate `minios.manage-apps` untuk administrator (lihat dokumen Fase 1).
4. Buka **Show Applications → Window Manager**. Pilih aplikasi → tab **App Shell**, aktifkan **Enable Menubar**, lalu simpan. Pilih mode `managed` untuk aplikasi yang view-nya *belum* mempunyai layout desktop, atau `embedded` apabila view-nya sudah memakai `<x-minios:desktop>`.
5. Untuk mengubah struktur menu buka tab **Menubar Builder**, klik **Aktifkan Editor**. Sampai editor diaktifkan, semua menu bawaan tersedia secara virtual dan tidak disimpan ke database. Reload desktop setelah mengubah konfigurasi atau menu.

## Mode rendering

- **Managed**: wrapper AppShell otomatis ditambahkan oleh desktop host untuk aplikasi terdaftar yang mengaktifkan satu atau lebih dari `menubar_enabled`, `sidebar_enabled`, `statusbar_enabled`; untuk *custom apps* yang hanya merender konten Livewire/Blade. Default untuk aplikasi **non-core**. Statusbar memakai `resources/views/apps/{slug}/customs/statusbar.blade.php` jika tersedia; sidebar berisi placeholder sampai Fase 3.
- **Embedded**: untuk aplikasi yang sudah memiliki `<x-minios:desktop app-id="slug">`, sehingga tidak dibungkus lagi. Saat `menubar_enabled=true`, isi `<x-minios:desktop.menu>` otomatis diganti dengan hasil Menu Builder; jika off, slot menubar lama tetap dipakai. Default untuk core apps.
- Jika aplikasi embedded **tidak mempunyai** `<x-minios:desktop.menu>`, developer dapat menyisipkan `<x-minios.menubar.dynamic app-id="slug" />` pada area menu existing, selama berada di dalam `<x-minios:desktop>` agar action handler tersedia.
- Toggle sidebar/statusbar untuk app embedded tetap mengikuti implementasi layout aplikasinya sendiri. Fase 3 akan menyediakan sidebar dinamis. Aplikasi embedded tidak otomatis kehilangan sidebar yang sudah ada ketika flag `sidebar_enabled=false` demi menjaga kompatibilitas.

## Default actions

| Menubar | Action | Syarat |
|---|---|---|
| File → Close | `window.close` | Selalu |
| View → Toggle Sidebar | `sidebar.toggle` | sidebar_enabled |
| Window → Reload Page | `window.refresh` | Selalu |
| Window → Toggle Statusbar | `statusbar.toggle` | statusbar_enabled |
| Help → About | `help.about` | Selalu |

About memuat `resources/views/apps/{app-slug}/customs/about.blade.php`, jika ada, selain itu modal fallback. File view dicari pada project Laravel (bukan direktori `vendor` package). Jika flag sidebar/statusbar berubah setelah menu dikustomisasi, reset menu ke default lalu aktifkan Editor lagi atau tambahkan aksi baru secara manual.

## Builder

`minios_app_menus` yang dibuat Fase 1 menyimpan tree (menggunakan `parent_id`), perintah, urutan, status, shortcut, dan permissions. Migration Fase 2 membuat `minios_app_menu_states`: membedakan menu belum pernah diedit (pakai default virtual) dari **menu yang sengaja dikosongkan**.

Builder mempunyai Group (root/submenu), Action, Separator, edit/hapus, urutan naik/turun, Gate abilities per baris, dan enable/disable. Maksimal tiga tingkat; route untuk `app.navigate` wajib berada di dalam Owned Routes aplikasi. Setiap mutasi memerlukan Gate `minios.manage-apps` dan validasi server. Perubahan menu global untuk semua pengguna; permission setiap item dievaluasi saat render berdasarkan user saat ini.

Action yang tersedia: `window.close`, `window.refresh`, `sidebar.toggle`, `statusbar.toggle`, `help.about`, `app.navigate`, `app.event`. Bukan JavaScript mentah; action diberikan ke handler Alpine yang terdaftar. Field **Shortcut** adalah *label*, bukan global keyboard registration.

Untuk `app.event`, aplikasi bisa mendengarkan event dari menu:

```blade
<div @minios-app-action.window="
    if ($event.detail.id === 'todo' && $event.detail.event === 'open-settings') {
        $wire.openSettings();
    }
">
    ...
</div>
```

Untuk `app.navigate`, event `minios-app-navigate` membawa `{id, path}` kepada view Livewire; desktop juga memperbarui URL window. Developer harus merespons event dalam aplikasinya dan melakukan authorization di server untuk halaman/aksi sensitif. *Hide menu by permission bukan otorisasi untuk API server.*

## Batasan Fase 2

- **CRUD** dan item sidebar terkelola belum dibuat (Fase 3).
- Editor belum memakai drag-and-drop; pengurutan memakai Up/Down.
- Window Manager UI bukan editor file Blade/PHP, tetapi konfigurasi dan action dispatch.
- Tidak ada Laravel host application/vendor dalam lampiran: pengujian browser dan database Laravel sesungguhnya harus dilakukan di integrasi project pengguna. Smoke test framework-free dan PHP syntax lint disertakan.
