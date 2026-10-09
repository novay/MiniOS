# MiniOS Notifications & Windows 11 Action Center

MiniOS hadir dengan sistem notifikasi bawaan yang mengadopsi tampilan dan perilaku modern **Windows 11 Action & Notification Center**. Sistem ini menggunakan **Windows 11 Toast Hub** mandiri untuk pop-up mengambang secara real-time (lengkap dengan ikon aplikasi pengirim, animasi tumpukan kartu 3D ala Flux/Sonner, serta progress loader interaktif) dan **Notification Center Flyout** untuk menampung riwayat notifikasi, dilengkapi efek audio **Chime sintetis halus** (Web Audio API).

---

## 🚀 Fitur Utama

1. **Windows 11 Toast Stack (`<x-minios.toast.group>` & `<x-minios.toast />`)**:
   - Komponen mengambang mandiri tanpa ketergantungan Flux UI.
   - Menggunakan pola `@persist('toast')` agar container notifikasi tetap persisten di DOM saat terjadi transisi halaman Livewire (`wire:navigate`).
   - **Stacked Cards Effect**: Ketika terdapat lebih dari satu notifikasi aktif, kartu notifikasi lama akan saling menumpuk secara berdimensi di belakang kartu utama (dengan offset vertikal dan pengecilan skala proporsional).
   - **Expand on Hover**: Saat pengguna mengarahkan kursor mouse ke area toast, tumpukan kartu otomatis mekar (*expand*) menjadi daftar vertikal lengkap sehingga semua notifikasi dapat dibaca dan ditutup secara individual.
   - **Smooth Slide Animation**: Menggunakan kurva akselerasi otentik WinUI (`cubic-bezier(0.16, 1, 0.3, 1)`) untuk animasi meluncur masuk dan keluar secara mulus.
2. **Interactive Progress Loader**:
   - Garis progres dinamis di bagian bawah kartu notifikasi yang menghitung mundur durasi tampil (default 5 detik).
   - **Smart Pause**: Saat kursor mouse masuk (`mouseenter`), progress bar dan countdown otomatis berhenti (*pause*). Saat kursor keluar (`mouseleave`), progres berlanjut (*resume* dengan minimal buffer 2,5 detik).
3. **App Icon Branding**:
   - Menampilkan ikon dan nama aplikasi pengirim secara otomatis (misal Files, Editor, Player, Settings, dsb) baik pada pop-up Toast maupun di Notification Center Flyout.
4. **Statusbar Audio Toggle**: Tombol speaker interaktif di system tray statusbar yang memungkinkan pengguna mengaktifkan/membisukan suara kapan saja.
5. **Windows 11 Notification Center**: Flyout riwayat notifikasi di system tray (ikon lonceng) yang menampilkan daftar notifikasi berikon aplikasi, waktu, badge jumlah yang belum dibaca, tombol hapus per item, dan *Clear all*.
6. **Harmonic Sound Chime**: Sintesis audio dua nada lembut (D5 $\rightarrow$ A5) menggunakan Web Audio API tanpa perlu memuat file MP3/WAV eksternal.
7. **Developer-Friendly Trait**: Trait `Novay\MiniOS\Concerns\HasNotifications` yang siap dipakai di seluruh Livewire Component.

---

## 🧩 Blade Component Setup

Untuk memasang sistem Toast di layout MiniOS, cukup gunakan sintaks Blade persisten:

```blade
@persist('toast')
    <x-minios.toast.group>
        <x-minios.toast />
    </x-minios.toast.group>
@endpersist
```

*(Atau gunakan tag ringkas `<x-minios.toast-hub />` yang sudah membungkus komponen di atas).*

---

## 🛠️ Penggunaan untuk Developer

### 1. Menggunakan Trait `HasNotifications` di Livewire Component

Cukup tambahkan trait `Novay\MiniOS\Concerns\HasNotifications` pada komponen Livewire aplikasi Anda:

```php
namespace App\Livewire;

use Livewire\Component;
use Novay\MiniOS\Concerns\HasNotifications;

class CashierApp extends Component
{
    use HasNotifications;

    public function checkout(): void
    {
        // ... proses transaksi ...

        // Notifikasi Berhasil (Success)
        $this->success('Transaksi #1042 berhasil dibayar.', 'Pembayaran Selesai');
    }

    public function cancelOrder(): void
    {
        // Notifikasi Peringatan (Warning)
        $this->warning('Pesanan dibatalkan oleh kasir.', 'Pembatalan');
    }

    public function handleError(): void
    {
        // Notifikasi Error (Danger)
        $this->error('Gagal mencetak struk belanja. Periksa printer.', 'Kesalahan Perangkat');
    }

    public function syncData(): void
    {
        // Notifikasi Info
        $this->info('Sinkronisasi data cloud sedang berjalan di latar belakang.');
    }
}
```

### 2. Shorthand API Methods

| Method | Parameter | Keterangan |
| :--- | :--- | :--- |
| `$this->success($text, $title = null)` | `string $text, ?string $title` | Menampilkan toast hijau & log sukses di Notification Center |
| `$this->error($text, $title = null)` | `string $text, ?string $title` | Menampilkan toast merah (`danger`) & log galat di Notification Center |
| `$this->warning($text, $title = null)` | `string $text, ?string $title` | Menampilkan toast kuning (`warning`) di Notification Center |
| `$this->info($text, $title = null)` | `string $text, ?string $title` | Menampilkan toast biru (`info`) di Notification Center |
| `$this->toast($text, $title = null, $variant = 'info')` | `string, ?string, string` | Notifikasi kustom dengan opsi varian (`success`, `danger`, `warning`, `info`) |
| `$this->notify($text, $title = null, $variant = 'info')` | `string, ?string, string` | Alias dari `$this->toast(...)` |

---

## 🌐 Memanggil Notifikasi dari JavaScript

Anda juga dapat memicu notifikasi MiniOS langsung dari browser (Alpine.js atau vanilla JS):

```javascript
// Browser CustomEvent
window.dispatchEvent(new CustomEvent('os-notify', {
    detail: {
        title: 'Download Selesai',
        text: 'Berkas laporan-keuangan.pdf berhasil diunduh.',
        variant: 'success' // 'success' | 'danger' | 'warning' | 'info'
    }
}));
```

Atau menggunakan Livewire JavaScript API:

```javascript
Livewire.dispatch('os-notify', {
    title: 'Backup Sistem',
    text: 'Pencadangan database otomatis berhasil dibuat.',
    variant: 'info'
});
```

---

## ⚙️ Pengaturan Pengguna

Pengaturan notifikasi disimpan secara persisten di database/storage setting MiniOS:

```php
// Mengambil konfigurasi posisi toast
$position = os_setting('notifications.position', 'bottom end'); 
// Opsi yang didukung: 'bottom end', 'top end', 'bottom start', 'top start'

// Mengambil konfigurasi efek suara
$soundEnabled = os_setting('notifications.sound', true);
```

Pengguna dapat menguji langsung tampilan notifikasi dan suara chime dengan menekan tombol **"Kirim Notifikasi Uji Coba"** di aplikasi **Settings > Notifications**.
