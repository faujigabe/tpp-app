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
