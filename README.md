# Website Undangan Syukuran — Dipta & Frengky

Website satu halaman, responsive, dengan foto asli pasangan, opening screen, countdown, rundown, lokasi Google Maps, kalender, RSVP, dan ucapan tamu.

## Menjalankan

Buka Terminal dan jalankan:

```sh
cd ~/Downloads/website-undangan-syukuran
python3 -m http.server 5173
```

Kemudian buka http://localhost:5173 pada Chrome. Tidak perlu npm install.

## Pengaturan

- Edit teks dan section pada `index.html`.
- Edit rundown di bagian atas `script.js` pada variabel `timeline`.
- Edit warna dan layout di `style.css`.
- Foto berada di `assets/couple-portrait.jpg` dan `assets/couple-candid.jpg`.
- Untuk musik opsional, tempatkan file audio berlisensi `assets/background-music.mp3`; tombol musik muncul otomatis jika file tersedia.

## Penting: RSVP dan ucapan

Saat ini data tersimpan menggunakan localStorage **hanya pada browser yang mengisi**. Data tidak terkirim ke penyelenggara dan tidak tersinkronisasi antar perangkat. Untuk penggunaan undangan publik, hubungkan endpoint backend/database (misalnya Supabase) dengan keamanan, validasi, dan moderasi sebelum menyebarkan tautan. Jangan menganggap konfirmasi lokal sebagai RSVP yang diterima penyelenggara.

## Verifikasi

Syntax JavaScript telah diperiksa dengan `node --check`. Peta menggunakan pencarian alamat; verifikasi pin lokasi dengan penyelenggara sebelum publikasi. Rundown masih contoh tentatif. Waktu akhir pada Google Calendar adalah estimasi pukul 13.00 WIB karena acara berakhir 'selesai'.
