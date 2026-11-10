# Panduan Integrasi OpenWA di E-TPP

OpenWA **bukan** library Laravel dan tidak dipasang di dalam folder `tpp-app`.
OpenWA adalah layanan terpisah (Node.js, berjalan di Docker) yang menyediakan
REST API WhatsApp. E-TPP memanggil API tersebut lewat HTTP.

```
Pegawai (WhatsApp)
      ▲
      │ scan QR / pesan
┌─────┴───────────────┐        HTTP + X-API-Key       ┌──────────────────┐
│ OpenWA (Docker)     │ ◄─────────────────────────── │ E-TPP (Laravel)  │
│ port 2785           │   POST /sessions/{id}/        │ OpenWaService    │
│ dashboard + API     │        messages/send-text     │ tpp:send-wa      │
└─────────────────────┘                               └──────────────────┘
```

Yang ditambahkan patch ini ke E-TPP (semua baru, fitur lama tidak diubah):

| File | Fungsi |
|---|---|
| `app/Services/OpenWaService.php` | Client HTTP ke OpenWA |
| `app/Services/WhatsappMessageBuilder.php` | Nomor & isi pesan — logika disalin persis dari pesan wa.me yang sudah ada, jadi isi pesannya identik |
| `app/Console/Commands/SendTppWhatsapp.php` | Perintah `php artisan tpp:send-wa` |
| `config/services.php` | Grup konfigurasi `openwa` |
| `.env.example` | Template variabel `OPENWA_*` |
| `tests/Unit/WhatsappMessageBuilderTest.php` | Uji format nomor |

Fitur WhatsApp lama (tombol wa.me satu per satu dan export Excel WhatsApp)
**tetap berjalan seperti semula** — integrasi ini hanya menambah jalur kirim
otomatis.

---

## 1. Pasang OpenWA (satu kali)

Prasyarat di Windows: **Docker Desktop** terpasang dan berjalan (WSL 2 aktif).

Buka terminal (di luar folder tpp-app, misalnya `C:\`):

```bat
git clone https://github.com/rmyndharis/OpenWA.git openwa
cd openwa
docker compose -f docker-compose.dev.yml up -d
```

Tunggu ±1 menit, lalu ambil API key admin yang digenerate saat pertama jalan:

```bat
docker exec openwa-api cat /app/data/.api-key
```

Buka dashboard: <http://localhost:2785> — masuk dengan API key tersebut.

### Buat sesi WhatsApp

1. Di dashboard, buat session baru (mis. nama: `etpp`).
2. Start session, lalu scan QR code dengan WhatsApp di nomor yang akan
   menjadi pengirim.
3. Setelah status session **connected**, catat **session id**-nya
   (terlihat di daftar session / detail session dashboard).

> **Gunakan nomor khusus untuk gateway ini, jangan nomor pribadi utama.**
> OpenWA adalah gateway tidak resmi (memakai whatsapp-web.js), sehingga
> selalu ada risiko nomor dibatasi/di-ban oleh WhatsApp.

Alternatif membuat sesi lewat API:

```bat
curl -X POST http://localhost:2785/api/sessions -H "Content-Type: application/json" -H "X-API-Key: <API_KEY>" -d "{\"name\": \"etpp\"}"
curl -X POST http://localhost:2785/api/sessions/<SESSION_ID>/start -H "X-API-Key: <API_KEY>"
curl http://localhost:2785/api/sessions/<SESSION_ID>/qr -H "X-API-Key: <API_KEY>"
```

---

## 2. Konfigurasi E-TPP

Tambahkan ke file `.env` di folder `tpp-app`:

```env
OPENWA_ENABLED=true
OPENWA_BASE_URL=http://localhost:2785/api
OPENWA_API_KEY=<api key dari langkah 1>
OPENWA_SESSION_ID=<session id dari langkah 1>
OPENWA_TIMEOUT=30
```

Lalu:

```bat
php artisan config:clear
```

---

## 3. Cara pakai

### Simulasi dulu (dry-run, tidak ada pesan terkirim)

```bat
php artisan tpp:send-wa 10 2026
```

Output menampilkan daftar pegawai dan chatId tujuan yang akan dikirimi,
termasuk siapa saja yang dilewati karena nomornya kosong/tidak valid.

### Uji kirim ke 1 orang dulu

```bat
php artisan tpp:send-wa 10 2026 --send --limit=1
```

Pastikan pesan benar-benar sampai dan isinya benar sebelum kirim massal.

### Kirim massal satu periode

```bat
php artisan tpp:send-wa 10 2026 --send
php artisan tpp:send-wa 10 2026 --send --unit=3
```

- Jeda antar pesan bawaan **8 detik** (`--delay=8`). Jangan diperkecil
  apalagi 0 — pengiriman terlalu cepat adalah pola yang paling mudah
  memicu pembatasan nomor oleh WhatsApp.
- Perintah menampilkan ringkasan: terkirim, gagal, dan tanpa nomor valid.
- Pesan yang dikirim isinya sama persis dengan rincian TPP yang selama ini
  dikirim manual lewat wa.me (indikator, rincian kotor, potongan, total
  diterima).

### Menonaktifkan

Cukup set `OPENWA_ENABLED=false` di `.env` lalu `php artisan config:clear`.
Tidak ada data yang berubah; fitur wa.me tetap bisa dipakai.

---

## 4. Troubleshooting

| Gejala | Penyebab & cek |
|---|---|
| `OpenWA tidak terjangkau` / connection refused | Container belum jalan — cek `docker ps`, jalankan ulang `docker compose -f docker-compose.dev.yml up -d` di folder openwa |
| Perintah menolak: "OpenWA belum aktif" | `OPENWA_API_KEY` / `OPENWA_SESSION_ID` kosong, `OPENWA_ENABLED` belum `true`, atau config masih ter-cache — jalankan `php artisan config:clear` |
| HTTP 401/403 dari OpenWA | API key salah — ambil lagi dari `docker exec openwa-api cat /app/data/.api-key` |
| HTTP 404 saat kirim | `OPENWA_SESSION_ID` salah — samakan dengan id session di dashboard |
| Sesi terhubung tapi pesan gagal | Session belum `connected` (QR belum di-scan / putus) — buka dashboard, start ulang session, scan QR lagi |
| Pegawai dilewati | Kolom `no_hp` pegawai kosong atau formatnya tidak bisa dinormalisasi ke awalan 62 |

---

## 5. Catatan keamanan data

- Isi pesan memuat rincian penghasilan pegawai. Pastikan `no_hp` di data
  pegawai benar milik pegawai yang bersangkutan sebelum kirim massal —
  salah nomor berarti rincian TPP orang lain terkirim ke pihak yang salah.
- API key OpenWA adalah kredensial penuh ke nomor pengirim. Simpan hanya
  di `.env` (tidak ikut Git), dan jangan expose port 2785 ke internet
  publik tanpa reverse proxy + autentikasi tambahan.
- Untuk pengiriman resmi berskala besar/jangka panjang, jalur resmi
  Meta WhatsApp Cloud API tetap merupakan opsi yang bebas risiko ban;
  OpenWA cocok untuk pemakaian internal dengan nomor khusus.
