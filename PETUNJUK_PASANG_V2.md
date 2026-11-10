# Petunjuk Pasang Paket v2 — Tombol Kirim WA Massal via Gateway di Halaman TPP

Paket ini menambahkan pengiriman WhatsApp massal **dari dalam aplikasi**
(tanpa terminal), melanjutkan paket v1 (OpenWA + `tpp:send-wa`) yang harus
sudah terpasang dan terbukti jalan terlebih dahulu.

## Isi paket

| File | Status |
|---|---|
| `app/Http/Controllers/WhatsappGatewayController.php` | Baru |
| `routes/web.php` | Berubah (+1 use, +2 route di grup admin/operator) |
| `resources/views/tpp/index.blade.php` | Berubah (tombol + panel kemajuan di modal WA massal, +1 blok script) |
| `tests/Unit/WhatsappGatewayViewStructureTest.php` | Baru |
| `PANDUAN_OPENWA.md` | Berubah (ditambah bagian "Kirim lewat halaman TPP") |

Tidak ada perubahan `.env`, tidak ada migration, tidak ada perubahan pada
fitur wa.me yang lama.

## Cara pasang

1. Ekstrak zip ke folder `C:\laragon\www\tpp-app`, pertahankan struktur
   folder, timpa file bila ditanya.
2. **Periksa dulu sebelum menjalankan apa pun:**

   ```bat
   cd C:\laragon\www\tpp-app
   git diff --stat
   ```

   Hasil yang sehat: `routes/web.php` berubah ±3 baris dan
   `resources/views/tpp/index.blade.php` berubah ±154 baris, semuanya
   penambahan. **Bila diff menunjukkan penghapusan besar di luar itu**
   (tanda salinan lokal Anda berbeda dari versi yang dipakai membuat paket
   ini), berhenti dan jangan dipakai dulu — hubungi pembuat paket; dua file
   itu bisa juga dipasang manual (lihat bagian bawah).

3. Segarkan dan uji:

   ```bat
   php artisan route:list | findstr whatsapp-gateway
   php artisan test --filter=WhatsappGatewayViewStructureTest
   ```

   Diharapkan: 2 route `tpp.whatsapp.gateway.*` tampil, dan 2 test PASS.

4. Buka menu **TPP** di aplikasi (login sebagai admin/operator), atur
   filter periode, klik **Kirim WA Massal** → tombol **Kirim via Gateway**
   kini ada di jendela yang sama, di sebelah "Buka Semua Chat".

## Cara kerja pengiriman dari halaman

- Browser memanggil endpoint kirim **satu pegawai per request** secara
  berurutan dengan jeda 8 detik — jadi tidak ada request web yang berjalan
  lama dan tidak perlu queue/worker tambahan.
- Sebelum mulai, halaman memeriksa `GET /whatsapp-gateway/status`; bila
  OpenWA mati atau sesi tidak terhubung, pengiriman tidak dimulai.
- Halaman harus tetap terbuka selama pengiriman. Tombol **Hentikan**
  menghentikan antrean setelah pesan yang sedang berjalan selesai.
- Hak akses mengikuti pola aplikasi: route hanya untuk admin/operator,
  super admin ditolak (sama seperti Export Excel WA), dan controller
  memeriksa `canAccessUnit` per data TPP.

## Pasang manual (bila diff tidak sehat)

1. Salin file baru `WhatsappGatewayController.php` dan file test apa adanya.
2. Di `routes/web.php` tambahkan use
   `App\Http\Controllers\WhatsappGatewayController` dan dua route berikut
   di dalam grup `role:admin,operator` (sejajar route export-whatsapp):

   ```php
   Route::get('/whatsapp-gateway/status', [WhatsappGatewayController::class, 'status'])->name('tpp.whatsapp.gateway.status');
   Route::post('/tpp/{tpp}/whatsapp-gateway', [WhatsappGatewayController::class, 'send'])->name('tpp.whatsapp.gateway.send');
   ```

3. Di `resources/views/tpp/index.blade.php` terapkan tiga sisipan dari file
   versi paket: (a) catatan + panel `#gatewaySection` di badan modal
   `whatsappMassalModal`, (b) tombol `#btnHentikanGateway` dan
   `#btnKirimWhatsappGateway` di footer modal yang sama, (c) blok `<script>`
   gateway setelah blok script WhatsApp massal yang lama di `@push('scripts')`.

## Masalah yang mungkin muncul

| Gejala | Penyebab & cara mengatasi |
|---|---|
| Tombol "Kirim via Gateway" tidak muncul | File view belum tertimpa, atau Anda login sebagai super admin/viewer — tombol mengikuti aturan tombol Kirim WA Massal yang lama |
| Klik tombol → pesan "Sesi OpenWA tidak terhubung" | Docker Desktop/OpenWA belum berjalan atau sesi putus — nyalakan Docker, buka dashboard OpenWA, pastikan sesi Terhubung |
| Klik tombol → pesan "OpenWA belum aktif" | Konfigurasi `.env` dari paket v1 belum terisi / config ter-cache — jalankan `php artisan config:clear` |
| Sebagian pegawai gagal dengan HTTP 422 | Kegagalan per pegawai dari gateway (mis. nomor tidak terdaftar di WhatsApp) — lihat alasan di catatan modal dan `storage/logs/laravel.log` |
| Pengiriman berhenti di tengah | Tab/halaman tertutup atau koneksi terputus — jalankan ulang; pegawai yang sudah terkirim akan menerima lagi karena tidak ada penanda terkirim, jadi pertimbangkan mengulang hanya untuk yang gagal lewat terminal (`tpp:send-wa --unit`) atau per baris tombol WA |
