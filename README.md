# TPP App

Aplikasi berbasis web untuk membantu proses pengelolaan dan perhitungan **Tambahan Penghasilan Pegawai (TPP)**.

Aplikasi ini dikembangkan menggunakan Laravel dan MySQL serta dijalankan pada lingkungan pengembangan lokal menggunakan Laragon.

## Fitur

* Pengelolaan data pegawai
* Perhitungan Tambahan Penghasilan Pegawai (TPP)
* Pengelolaan komponen TPP
* Perhitungan produktivitas
* Perhitungan kehadiran
* Perhitungan perilaku
* Rekapitulasi TPP
* Edit data secara massal
* Filter data
* Penghapusan data
* Pencetakan laporan dalam format PDF
* Penyimpanan data berdasarkan periode/bulan

## Komponen TPP

Perhitungan TPP menggunakan beberapa komponen, antara lain:

* Beban Kerja
* Prestasi Kerja
* Kondisi Kerja
* Kelangkaan Profesi
* Produktivitas
* Kehadiran
* Perilaku

## Teknologi

Aplikasi ini dibangun menggunakan:

* PHP
* Laravel
* MySQL
* Blade
* HTML
* CSS
* JavaScript
* Composer
* Laragon

## Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan perangkat telah memiliki:

* PHP 8.3 atau versi yang kompatibel
* Composer
* MySQL
* Laragon
* Git

## Instalasi

### 1. Clone repository

Clone repository ke folder `www` milik Laragon:

```bash
cd C:\laragon\www
git clone https://github.com/faujigabe/tpp-app.git
cd tpp-app
```

### 2. Install dependency Laravel

Jalankan:

```bash
composer install
```

### 3. Membuat file environment

Salin `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

Pada Linux/macOS, gunakan:

```bash
cp .env.example .env
```

### 4. Generate application key

Jalankan:

```bash
php artisan key:generate
```

### 5. Konfigurasi database

Buat database MySQL dengan nama:

```text
tpp_db
```

Kemudian pastikan konfigurasi `.env` sesuai dengan database lokal:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tpp_db
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan konfigurasi MySQL pada komputer masing-masing.

### 6. Menjalankan migration

Setelah database siap, jalankan:

```bash
php artisan migrate
```

Jika aplikasi memiliki data awal yang menggunakan seeder, jalankan:

```bash
php artisan db:seed
```

atau:

```bash
php artisan migrate --seed
```

### 7. Menjalankan aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Kemudian buka:

```text
http://127.0.0.1:8000
```

Jika menggunakan konfigurasi virtual host Laragon, aplikasi juga dapat diakses melalui domain lokal yang disediakan Laragon.

## Pengujian

PHPUnit dikunci menggunakan database MySQL khusus bernama `tpp_app_testing` agar pengujian yang memakai `RefreshDatabase` tidak pernah menghapus data pada `tpp_db`.

Buat database pengujian satu kali:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS tpp_app_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Kemudian jalankan:

```bash
php artisan test
```

Jangan mengubah `DB_DATABASE` pada `phpunit.xml` menjadi database aplikasi utama.

## Audit Log dan Retensi

Perubahan pada TPP, pegawai, akun pengguna, kelas jabatan, unit kerja, dan persetujuan periode dicatat pada menu **Jejak Perubahan** yang hanya dapat diakses super admin. Catatan mencakup pelaku, waktu, unit, alamat IP, serta nilai sebelum dan sesudah. Password, token, dan foto tidak dicatat.

Audit log disimpan selama lima tahun. Pembersihan otomatis dijalankan oleh Laravel Scheduler setiap awal bulan dan dapat dijalankan manual dengan:

```bash
php artisan audit:prune
```

## Backup dan Restore

Atur lokasi backup pada `.env`. Gunakan drive yang dilindungi BitLocker atau enkripsi penyimpanan. Lokasi mingguan harus berada pada media atau perangkat yang berbeda dari database utama.

```env
BACKUP_LOCAL_PATH="C:\laragon\backups\tpp-daily"
BACKUP_WEEKLY_PATH="D:\tpp-backups-weekly"
MYSQLDUMP_BINARY=mysqldump
MYSQL_BINARY=mysql
BACKUP_LOCAL_RETENTION_DAYS=14
BACKUP_WEEKLY_RETENTION_DAYS=365
AUDIT_RETENTION_YEARS=5
```

Uji backup secara manual:

```bash
php artisan database:backup
php artisan database:backup --weekly
```

Setiap arsip `.sql.gz` disertai file `.sha256`. Restore menolak arsip tanpa checksum yang valid. Restore bersifat destruktif dan hanya boleh dilakukan setelah membuat backup kondisi database saat ini:

```bash
php artisan database:restore "D:\tpp-backups-weekly\tpp_20260828_020000.sql.gz" --confirm=RESTORE
php artisan optimize:clear
```

Laravel Scheduler harus dipanggil setiap menit. Pada Windows, buat satu tugas di **Task Scheduler** yang menjalankan perintah berikut dari folder aplikasi:

```bat
php artisan schedule:run
```

Jadwal aplikasi:

- backup lokal setiap hari pukul 01.00;
- backup mingguan ke lokasi terpisah setiap Minggu pukul 02.00;
- pembersihan audit log setiap tanggal 1 pukul 03.00.

## Struktur Project

Struktur utama aplikasi:

```text
tpp-app/
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   └── views/
├── routes/
├── storage/
├── artisan
├── composer.json
├── package.json
├── .env.example
└── README.md
```

## Database

Aplikasi menggunakan MySQL sebagai database.

Nama database yang digunakan pada lingkungan pengembangan:

```text
tpp_db
```

Struktur database dikelola menggunakan Laravel Migration sehingga struktur database dapat dibuat kembali pada lingkungan baru tanpa harus menyimpan database lokal ke dalam repository.

## Environment

File `.env` digunakan untuk konfigurasi lokal dan **tidak disimpan di repository GitHub**.

Gunakan `.env.example` sebagai template konfigurasi.

Jangan memasukkan informasi sensitif seperti:

* Password database
* Application key
* API key
* Access token
* Secret key

ke dalam repository publik.

## Git Workflow

Setelah melakukan perubahan pada aplikasi, periksa perubahan:

```bash
git status
```

Tambahkan perubahan:

```bash
git add .
```

Buat commit:

```bash
git commit -m "Deskripsi perubahan"
```

Kemudian kirim ke GitHub:

```bash
git push
```

Untuk mengambil perubahan terbaru dari GitHub:

```bash
git pull
```

## Pengembangan

Repository ini digunakan sebagai pusat penyimpanan source code dan version control untuk pengembangan TPP App.

Setiap fitur baru atau perbaikan bug sebaiknya dibuat dalam commit yang terpisah dan menggunakan pesan commit yang menjelaskan perubahan.

Contoh:

```bash
git add .
git commit -m "Tambah fitur edit massal pegawai"
git push
```

## Status Project

Project ini masih dalam tahap pengembangan.

Fitur dan struktur aplikasi dapat berubah seiring dengan proses pengembangan dan kebutuhan pengguna.

## Lisensi

Lisensi project dapat ditentukan kemudian sesuai dengan kebutuhan dan kebijakan pemilik aplikasi.
