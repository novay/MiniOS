# Changelog - MiniOS SoundCloud (Music Player)

Semua perubahan, penambahan fitur, dan perbaikan untuk aplikasi **SoundCloud** MiniOS didokumentasikan di berkas ini.

Format changelog ini mengacu pada prinsip [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mengikuti [Semantic Versioning](https://semver.org/).

---

## [1.0.0] - 2026-10-10

Rilis resmi pertama aplikasi **SoundCloud MiniOS** dengan integrasi pemutar musik HTML5/Alpine.js, sinkronisasi audio global master, mode Smart Mini Player, dan antarmuka desktop-grade menubar.

### ✨ Fitur Utama (Features)

#### 1. Audio Playback Engine & Kontrol
- **Pemutar Audio Native**: Mendukung pemutaran playlist SoundCloud dan file stream audio dengan kontrol Play, Pause, Next Track, dan Previous Track.
- **Seekbar Interaktif**: Penjelajahan posisi pemutaran lagu berbasis durasi aktual (`elapsed` dan `remaining time`) disertai visualisasi waveform.
- **Mode Putar Lanjutan**:
  - **Shuffle Play**: Mode acak urutan lagu dengan tombol status interaktif.
  - **Repeat Modes**: Tiga mode perulangan: *Off*, *Repeat One* (ulang lagu yang sama terus menerus), dan *Repeat All* (putar ulang playlist dari awal saat selesai).

#### 2. Sinkronisasi Volume Audio Global MiniOS
- **Master Volume Integration**: Slider volume SoundCloud kini terhubung secara dua arah (*bidirectional sync*) dengan sistem volume master MiniOS di taskbar/topbar desktop.
- **Shortcut Keyboard Global**:
  - `Cmd + F12` (atau `Ctrl + F12`): Menaikkan volume sistem.
  - `Cmd + F11` (atau `Ctrl + F11`): Menurunkan volume sistem.
- **Mute / Unmute**: Dukungan toggle bisu suara dengan indikator icon dinamis di topbar maupun jendela aplikasi.

#### 3. State Persistence (Penyimpanan Status Pemutaran)
- Menyimpan track aktif dan detik posisi audio terakhir (`currentTime`) ke dalam `localStorage`.
- Saat browser atau desktop MiniOS di-refresh/reload, posisi track lagu terakhir dan detik pemutaran tetap tersimpan tanpa ter-reset ke track pertama.

#### 4. Smart Mini Player (Floating Widget)
- Pemutar mini mengambang (`mini.blade.php`) di sudut kanan bawah desktop MiniOS.
- **Intelligent Trigger**: Mini player muncul secara otomatis **hanya ketika jendela SoundCloud sedang di-minimize**, menampilkan artwork, judul lagu, progress bar mini, kontrol putar/jeda, serta tombol untuk mengembalikan (*restore*) jendela utama.

#### 5. Desktop Application Menu (`<x-minios.menubar>`)
- Menu Bar horizontal desktop lengkap:
  - **Playlist**: Buat Baru, Impor URL Playlist SoundCloud, Playlist Contoh, Bersihkan Antrean.
  - **Playback**: Play/Pause, Trek Berikutnya/Sebelumnya, Toggle Shuffle, Toggle Repeat.
  - **Help**: Shortcut Keyboard, Tentang SoundCloud.
- Modal kustom untuk mengubah tautan playlist (`change.blade.php`) dan dialog informasi lisensi & versi (`about.blade.php`).

---

### 📌 Persiapan Rilis Berikutnya (Planned for Future Releases)
- Visualizer audio spektrum animasi real-time (Web Audio API AnalyserNode).
- Integrasi equalizer audio 5-band (Bass, Mid, Treble).
- Dukungan unduh cache offline untuk playlist favorit.
