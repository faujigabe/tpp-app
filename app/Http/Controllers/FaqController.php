<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqCategories = [
            [
                'slug' => 'login-akses',
                'title' => 'Login dan Akses',
                'icon' => 'bi-box-arrow-in-right',
                'items' => [
                    ['question' => 'Bagaimana cara login ke aplikasi TPP?', 'answer' => 'Pengguna dapat login ke aplikasi TPP dengan memasukkan email dan password pada halaman login, kemudian menekan tombol masuk.', 'solution' => 'Pastikan email dan password yang dimasukkan benar serta koneksi jaringan dalam kondisi baik.'],
                    ['question' => 'Apa yang harus dilakukan jika lupa password?', 'answer' => 'Jika pengguna lupa password, reset atau perubahan password perlu dilakukan oleh admin sesuai kewenangan pengelolaan akun.', 'solution' => 'Hubungi admin aplikasi atau pengelola sistem untuk bantuan reset password.'],
                    ['question' => 'Mengapa akun saya tidak bisa login?', 'answer' => 'Akun tidak bisa login biasanya disebabkan oleh email atau password yang salah, akun belum terdaftar, atau akun tidak memiliki hak akses yang sesuai.', 'solution' => 'Periksa kembali email dan password, lalu hubungi admin jika akun masih belum bisa digunakan.'],
                    ['question' => 'Apa perbedaan hak akses super admin, admin, operator, dan viewer?', 'answer' => 'Setiap role memiliki kewenangan yang berbeda. Super admin memiliki akses paling luas, sedangkan admin, operator, dan viewer memiliki akses sesuai tugasnya.', 'solution' => 'Gunakan akun sesuai tugas dan ajukan perubahan role apabila diperlukan.'],
                    ['question' => 'Mengapa menu pada akun saya berbeda dengan akun lain?', 'answer' => 'Perbedaan menu terjadi karena setiap akun memiliki role atau hak akses yang berbeda.', 'solution' => 'Pastikan role akun sudah sesuai dan koordinasikan dengan admin jika ada menu yang dibutuhkan tetapi tidak muncul.'],
                    ['question' => 'Bagaimana cara logout dari aplikasi?', 'answer' => 'Pengguna dapat logout melalui menu profil atau menu akun yang tersedia pada aplikasi.', 'solution' => 'Klik menu profil, lalu pilih opsi logout untuk keluar dari aplikasi dengan aman.'],
                ],
            ],
            [
                'slug' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'bi-speedometer2',
                'items' => [
                    ['question' => 'Apa fungsi menu Dashboard?', 'answer' => 'Dashboard berfungsi menampilkan ringkasan informasi utama dalam aplikasi, seperti jumlah pegawai, status perhitungan TPP, dan informasi lainnya.', 'solution' => 'Gunakan dashboard untuk memantau kondisi umum data dan progres pengelolaan TPP.'],
                    ['question' => 'Data apa saja yang ditampilkan pada Dashboard?', 'answer' => 'Dashboard menampilkan data ringkasan seperti jumlah pegawai, jumlah perhitungan TPP, total nominal TPP, dan informasi pegawai yang belum lengkap.', 'solution' => 'Periksa setiap indikator di dashboard sebagai bahan monitoring awal.'],
                    ['question' => 'Mengapa jumlah data pada Dashboard tidak sesuai?', 'answer' => 'Jumlah data dapat berbeda apabila ada data yang belum lengkap, belum dihitung, atau periode yang dilihat berbeda.', 'solution' => 'Periksa kelengkapan data pegawai, kelas jabatan, dan periode yang dipilih.'],
                    ['question' => 'Apa arti informasi pegawai tanpa kelas jabatan?', 'answer' => 'Informasi tersebut menunjukkan bahwa masih ada pegawai yang belum dihubungkan dengan data kelas jabatan.', 'solution' => 'Lengkapi data kelas jabatan pegawai terlebih dahulu.'],
                ],
            ],
            [
                'slug' => 'pegawai',
                'title' => 'Data Pegawai',
                'icon' => 'bi-people',
                'items' => [
                    ['question' => 'Bagaimana cara menambah data pegawai baru?', 'answer' => 'Data pegawai dapat ditambahkan melalui menu Pegawai dengan mengisi form data yang tersedia.', 'solution' => 'Isi data pegawai secara lengkap lalu simpan.'],
                    ['question' => 'Bagaimana cara mengubah data pegawai?', 'answer' => 'Perubahan data pegawai dapat dilakukan melalui menu Pegawai dengan memilih data yang ingin diperbarui.', 'solution' => 'Cari pegawai, buka form edit, lakukan perubahan, lalu simpan.'],
                    ['question' => 'Bagaimana cara menghapus data pegawai?', 'answer' => 'Penghapusan data pegawai dilakukan melalui menu Pegawai apabila akun memiliki hak akses untuk menghapus data.', 'solution' => 'Pilih data pegawai yang akan dihapus dan pastikan data tersebut memang tidak lagi diperlukan.'],
                    ['question' => 'Mengapa data pegawai tidak bisa dihapus?', 'answer' => 'Data pegawai tidak dapat dihapus apabila masih terkait dengan data lain, seperti akun user atau data TPP.', 'solution' => 'Periksa keterkaitan data pegawai dengan user dan riwayat TPP sebelum menghapus.'],
                    ['question' => 'Data pegawai apa saja yang wajib diisi?', 'answer' => 'Data pokok pegawai seperti nama, NIP, unit kerja, status, dan kelas jabatan perlu diisi agar dapat digunakan dalam proses perhitungan TPP.', 'solution' => 'Lengkapi seluruh kolom penting pada form pegawai sebelum menyimpan data.'],
                    ['question' => 'Mengapa data pegawai tidak muncul saat perhitungan TPP?', 'answer' => 'Data pegawai tidak muncul biasanya karena belum aktif, belum memiliki kelas jabatan, atau tidak termasuk unit kerja yang sedang dikelola.', 'solution' => 'Pastikan status pegawai aktif, kelas jabatan terisi, dan unit kerja sesuai.'],
                    ['question' => 'Bagaimana cara mengubah status pegawai menjadi aktif atau nonaktif?', 'answer' => 'Perubahan status pegawai dapat dilakukan pada menu edit data pegawai.', 'solution' => 'Buka data pegawai, ubah status sesuai kondisi terbaru, lalu simpan perubahan.'],
                    ['question' => 'Mengapa pegawai harus memiliki kelas jabatan?', 'answer' => 'Kelas jabatan merupakan dasar dalam perhitungan komponen TPP, sehingga pegawai yang belum memiliki kelas jabatan tidak dapat dihitung secara lengkap.', 'solution' => 'Tetapkan kelas jabatan pegawai terlebih dahulu sebelum melakukan perhitungan TPP.'],
                ],
            ],
            [
                'slug' => 'import-pegawai',
                'title' => 'Import Pegawai',
                'icon' => 'bi-file-earmark-arrow-up',
                'items' => [
                    ['question' => 'Bagaimana cara import data pegawai dari Excel?', 'answer' => 'Import data pegawai dilakukan melalui menu Pegawai dengan mengunggah file Excel sesuai template yang disediakan.', 'solution' => 'Unduh template, isi data sesuai format, lalu unggah file pada menu import.'],
                    ['question' => 'Di mana saya bisa mengunduh template import pegawai?', 'answer' => 'Template import pegawai tersedia pada menu import data pegawai.', 'solution' => 'Buka menu Pegawai, pilih fitur import, lalu unduh template.'],
                    ['question' => 'Mengapa file import pegawai gagal diproses?', 'answer' => 'File import biasanya gagal diproses karena format file tidak sesuai, kolom tidak lengkap, atau isi data tidak mengikuti template.', 'solution' => 'Periksa kembali format Excel, kolom, dan isi data agar sesuai template resmi.'],
                    ['question' => 'Format file apa yang didukung untuk import pegawai?', 'answer' => 'Import data pegawai umumnya menggunakan file spreadsheet sesuai template sistem.', 'solution' => 'Gunakan file Excel sesuai ketentuan pada template yang disediakan aplikasi.'],
                    ['question' => 'Apa yang terjadi jika ada data NIP yang duplikat saat import?', 'answer' => 'Data dengan NIP duplikat umumnya akan ditolak atau memicu kegagalan import karena setiap pegawai harus memiliki identitas unik.', 'solution' => 'Periksa kembali data sebelum import dan pastikan setiap NIP hanya digunakan untuk satu pegawai.'],
                ],
            ],
            [
                'slug' => 'kelas-jabatan',
                'title' => 'Kelas Jabatan',
                'icon' => 'bi-diagram-3',
                'items' => [
                    ['question' => 'Apa fungsi menu Kelas Jabatan?', 'answer' => 'Menu Kelas Jabatan digunakan untuk mengelola data kelas jabatan beserta komponen yang menjadi dasar perhitungan TPP.', 'solution' => 'Gunakan menu ini untuk memastikan data kelas jabatan tersedia sebelum perhitungan TPP dilakukan.'],
                    ['question' => 'Bagaimana cara menambah kelas jabatan?', 'answer' => 'Kelas jabatan dapat ditambahkan melalui menu Kelas Jabatan dengan mengisi nama, nomor kelas, dan komponen pendukungnya.', 'solution' => 'Isi data kelas jabatan sesuai ketentuan lalu simpan.'],
                    ['question' => 'Bagaimana cara mengubah data kelas jabatan?', 'answer' => 'Perubahan data kelas jabatan dilakukan melalui fitur edit pada menu Kelas Jabatan.', 'solution' => 'Pilih kelas jabatan yang ingin diperbarui, lakukan perubahan, lalu simpan.'],
                    ['question' => 'Apa arti beban kerja, prestasi kerja, kondisi kerja, dan kelangkaan profesi?', 'answer' => 'Komponen tersebut merupakan unsur penyusun nilai TPP berdasarkan jabatan.', 'solution' => 'Isi setiap komponen sesuai ketentuan yang berlaku pada instansi.'],
                    ['question' => 'Mengapa kelas jabatan tidak muncul saat input data pegawai?', 'answer' => 'Kelas jabatan tidak muncul biasanya karena belum dibuat pada unit kerja terkait atau data belum tersimpan dengan benar.', 'solution' => 'Pastikan data kelas jabatan sudah tersedia dan sesuai dengan unit kerja pegawai.'],
                ],
            ],
            [
                'slug' => 'import-kelas-jabatan',
                'title' => 'Import Kelas Jabatan',
                'icon' => 'bi-upload',
                'items' => [
                    ['question' => 'Bagaimana cara import kelas jabatan dari Excel?', 'answer' => 'Import kelas jabatan dilakukan melalui menu Kelas Jabatan dengan mengunggah file Excel sesuai template.', 'solution' => 'Unduh template, isi data dengan benar, lalu unggah file pada fitur import kelas jabatan.'],
                    ['question' => 'Di mana saya bisa mengunduh template import kelas jabatan?', 'answer' => 'Template import kelas jabatan tersedia pada menu import di bagian Kelas Jabatan.', 'solution' => 'Masuk ke menu Kelas Jabatan, pilih fitur import, kemudian unduh template.'],
                    ['question' => 'Mengapa file import kelas jabatan gagal diproses?', 'answer' => 'Kegagalan import dapat disebabkan oleh format file yang salah, data tidak lengkap, atau nilai komponen tidak sesuai ketentuan.', 'solution' => 'Periksa kembali file yang diunggah dan sesuaikan dengan template resmi.'],
                ],
            ],
            [
                'slug' => 'perhitungan-tpp',
                'title' => 'Perhitungan TPP',
                'icon' => 'bi-calculator',
                'items' => [
                    ['question' => 'Bagaimana cara membuat perhitungan TPP baru?', 'answer' => 'Perhitungan TPP baru dibuat melalui menu TPP dengan memilih periode bulan dan tahun, lalu mengisi komponen penilaian yang diperlukan.', 'solution' => 'Pilih periode yang benar, pastikan data pegawai lengkap, lalu lakukan input dan simpan.'],
                    ['question' => 'Bagaimana cara memilih bulan dan tahun perhitungan?', 'answer' => 'Bulan dan tahun perhitungan dipilih pada form input TPP sebelum proses perhitungan dilakukan.', 'solution' => 'Periksa kembali periode yang dipilih agar sesuai dengan bulan perhitungan.'],
                    ['question' => 'Mengapa periode perhitungan menggunakan bulan sebelumnya?', 'answer' => 'Perhitungan TPP umumnya menggunakan data kinerja periode sebelumnya sebagai dasar penilaian pada bulan berjalan.', 'solution' => 'Pahami alur periode kerja agar tidak salah memilih bulan perhitungan.'],
                    ['question' => 'Nilai apa saja yang harus diisi saat input TPP?', 'answer' => 'Nilai yang biasanya diisi meliputi produktivitas, kehadiran, perilaku, tambahan TPP, potongan TPP, dan komponen lain yang diperlukan sistem.', 'solution' => 'Lengkapi seluruh komponen penilaian sesuai data pendukung sebelum menyimpan.'],
                    ['question' => 'Apa yang dimaksud dengan produktivitas, kehadiran, dan perilaku?', 'answer' => 'Ketiga unsur tersebut merupakan komponen penilaian pegawai yang digunakan sebagai dasar perhitungan TPP.', 'solution' => 'Masukkan nilai sesuai hasil penilaian yang sah dan ketentuan yang berlaku.'],
                    ['question' => 'Mengapa nilai produktivitas, kehadiran, dan perilaku tidak boleh lebih dari 100?', 'answer' => 'Batas nilai tersebut diterapkan karena sistem menggunakan skala maksimal tertentu dalam proses penilaian.', 'solution' => 'Masukkan nilai dalam rentang yang diperbolehkan.'],
                    ['question' => 'Apa yang dimaksud dengan tambahan TPP?', 'answer' => 'Tambahan TPP adalah nilai tambahan yang diberikan sesuai kebijakan atau kondisi tertentu yang diakomodasi oleh sistem.', 'solution' => 'Isi kolom tambahan TPP hanya jika ada dasar atau ketentuan yang mendukung.'],
                    ['question' => 'Apa yang dimaksud dengan potongan TPP?', 'answer' => 'Potongan TPP adalah pengurangan nilai atau nominal TPP yang dikenakan berdasarkan kondisi tertentu sesuai aturan.', 'solution' => 'Masukkan nilai potongan berdasarkan dokumen pendukung dan ketentuan yang berlaku.'],
                    ['question' => 'Apa fungsi opsi hitung pajak?', 'answer' => 'Opsi hitung pajak dipertahankan untuk kebijakan yang mewajibkan unit kerja menghitung potongan pajak. Secara bawaan opsi ini nonaktif karena PPh 21 ditangani oleh bendahara/BKAD.', 'solution' => 'Biarkan nonaktif apabila pajak dihitung di luar aplikasi. Aktifkan hanya jika kebijakan resmi kembali mewajibkan perhitungan pajak pada rincian TPP unit kerja.'],
                    ['question' => 'Bagaimana cara menyimpan perhitungan TPP?', 'answer' => 'Setelah seluruh komponen terisi, pengguna dapat menyimpan data perhitungan melalui tombol simpan pada form atau tabel input TPP.', 'solution' => 'Pastikan semua kolom wajib telah terisi dengan benar sebelum menekan tombol simpan.'],
                    ['question' => 'Mengapa data TPP tidak tersimpan?', 'answer' => 'Data TPP tidak tersimpan biasanya karena ada komponen yang belum lengkap, nilai tidak valid, atau terjadi gangguan saat proses penyimpanan.', 'solution' => 'Periksa seluruh isian, pastikan nilainya sesuai ketentuan, lalu coba simpan kembali.'],
                    ['question' => 'Mengapa sebagian pegawai tidak ikut terhitung?', 'answer' => 'Sebagian pegawai tidak ikut terhitung apabila data pegawainya belum lengkap, belum memiliki kelas jabatan, atau statusnya tidak aktif.', 'solution' => 'Lakukan pengecekan terhadap data pegawai yang tidak terhitung lalu lengkapi data yang masih kurang.'],
                ],
            ],
            [
                'slug' => 'import-ekinerja',
                'title' => 'Import PDF e-Kinerja',
                'icon' => 'bi-file-earmark-pdf',
                'items' => [
                    ['question' => 'Bagaimana cara import PDF e-Kinerja?', 'answer' => 'Import PDF e-Kinerja dilakukan melalui fitur unggah dokumen pada menu TPP untuk membantu pengisian data perhitungan.', 'solution' => 'Unggah file PDF sesuai format yang didukung dan pastikan data pada file sesuai dengan identitas pegawai dalam sistem.'],
                    ['question' => 'Mengapa file PDF e-Kinerja gagal diimport?', 'answer' => 'Kegagalan import dapat terjadi karena format file tidak sesuai, ukuran file terlalu besar, atau isi dokumen tidak terbaca sistem.', 'solution' => 'Gunakan file PDF yang sesuai ketentuan, periksa ukuran file, dan pastikan dokumen tidak rusak.'],
                    ['question' => 'Bagaimana sistem mencocokkan data PDF dengan data pegawai?', 'answer' => 'Sistem mencocokkan data berdasarkan identitas pegawai yang tersedia dalam file dan data pegawai yang sudah tersimpan dalam aplikasi.', 'solution' => 'Pastikan data identitas pada PDF sesuai dengan data pegawai di aplikasi agar pencocokan berhasil.'],
                ],
            ],
            [
                'slug' => 'daftar-tpp',
                'title' => 'Daftar TPP',
                'icon' => 'bi-table',
                'items' => [
                    ['question' => 'Bagaimana cara melihat daftar hasil perhitungan TPP?', 'answer' => 'Daftar hasil perhitungan TPP dapat dilihat melalui menu TPP pada bagian daftar atau riwayat perhitungan.', 'solution' => 'Pilih periode yang sesuai lalu buka daftar TPP.'],
                    ['question' => 'Bagaimana cara mencari TPP pegawai tertentu?', 'answer' => 'Pencarian dapat dilakukan melalui kolom pencarian atau filter yang tersedia pada daftar TPP.', 'solution' => 'Masukkan nama pegawai, NIP, atau parameter lain yang tersedia untuk mempercepat pencarian data.'],
                    ['question' => 'Bagaimana cara memfilter TPP berdasarkan bulan dan tahun?', 'answer' => 'Filter bulan dan tahun tersedia pada halaman daftar TPP untuk menampilkan data sesuai periode tertentu.', 'solution' => 'Pilih bulan dan tahun yang diinginkan lalu terapkan filter.'],
                    ['question' => 'Mengapa data TPP periode tertentu tidak muncul?', 'answer' => 'Data periode tertentu tidak muncul apabila perhitungan belum dibuat, belum disimpan, atau filter periode tidak sesuai.', 'solution' => 'Periksa kembali periode yang dipilih dan pastikan data perhitungannya tersedia.'],
                    ['question' => 'Bagaimana cara mengedit data TPP pegawai?', 'answer' => 'Pengeditan data TPP dapat dilakukan melalui menu edit pada daftar TPP, selama periode tersebut belum dikunci atau disubmit final.', 'solution' => 'Pilih data yang akan diubah, lakukan perbaikan, lalu simpan kembali.'],
                    ['question' => 'Bagaimana cara menghapus data TPP pegawai?', 'answer' => 'Penghapusan data TPP dilakukan melalui menu hapus pada daftar TPP, apabila hak akses pengguna mengizinkan.', 'solution' => 'Pastikan data yang dihapus memang tidak diperlukan lagi dan belum masuk tahap finalisasi.'],
                ],
            ],
            [
                'slug' => 'submit-lock',
                'title' => 'Submit dan Lock Periode',
                'icon' => 'bi-lock',
                'items' => [
                    ['question' => 'Apa fungsi submit periode?', 'answer' => 'Submit periode digunakan untuk menandai bahwa data TPP pada periode tertentu telah selesai diinput dan siap untuk tahap berikutnya.', 'solution' => 'Lakukan submit hanya setelah seluruh data selesai dicek dan dipastikan benar.'],
                    ['question' => 'Kapan periode TPP harus di-submit?', 'answer' => 'Periode TPP di-submit setelah proses input, koreksi, dan pengecekan data selesai dilakukan.', 'solution' => 'Pastikan tidak ada lagi data yang perlu diperbaiki sebelum melakukan submit periode.'],
                    ['question' => 'Apa yang terjadi setelah periode di-submit?', 'answer' => 'Setelah periode di-submit, data umumnya masuk ke tahap validasi atau pembatasan perubahan sesuai mekanisme aplikasi.', 'solution' => 'Pastikan data sudah final sebelum submit agar tidak perlu pembukaan ulang periode.'],
                    ['question' => 'Apa perbedaan submit periode dengan lock periode?', 'answer' => 'Submit periode menunjukkan bahwa data siap diproses lebih lanjut, sedangkan lock periode berarti periode tersebut dikunci sehingga tidak dapat diubah lagi.', 'solution' => 'Pahami tahapan kerja periode agar tidak keliru melakukan submit atau lock.'],
                    ['question' => 'Mengapa periode yang sudah di-submit tidak bisa diedit?', 'answer' => 'Hal ini terjadi karena sistem membatasi perubahan pada data yang sudah memasuki tahap finalisasi.', 'solution' => 'Jika masih ada kesalahan data, koordinasikan dengan pihak yang berwenang untuk pembukaan periode.'],
                    ['question' => 'Bagaimana cara unlock periode yang sudah terkunci?', 'answer' => 'Unlock periode hanya dapat dilakukan oleh pengguna dengan hak akses tertentu.', 'solution' => 'Hubungi super admin atau pengelola yang berwenang untuk membuka kembali periode tersebut.'],
                ],
            ],
            [
                'slug' => 'cetak-export',
                'title' => 'Cetak dan Export',
                'icon' => 'bi-printer',
                'items' => [
                    ['question' => 'Bagaimana cara mencetak data TPP?', 'answer' => 'Data TPP dapat dicetak melalui menu cetak pada daftar hasil perhitungan atau rekap.', 'solution' => 'Pilih data atau periode yang diinginkan lalu gunakan fitur cetak.'],
                    ['question' => 'Bagaimana cara export data TPP ke Excel?', 'answer' => 'Export data dilakukan melalui tombol export pada halaman daftar TPP atau rekap.', 'solution' => 'Terapkan filter periode atau unit kerja terlebih dahulu lalu klik tombol export.'],
                    ['question' => 'Apa perbedaan export TPP, rekap, rekap SIPD, dan export WhatsApp?', 'answer' => 'Masing-masing fitur export memiliki tujuan yang berbeda, seperti export data rinci, rekapitulasi, format integrasi tertentu, atau pengiriman informasi melalui WhatsApp.', 'solution' => 'Pilih jenis export sesuai kebutuhan pelaporan atau distribusi informasi.'],
                    ['question' => 'Mengapa hasil export tidak sesuai filter yang dipilih?', 'answer' => 'Hal ini dapat terjadi jika filter belum diterapkan dengan benar atau data belum dimuat ulang setelah filter dipilih.', 'solution' => 'Pastikan filter sudah dipilih dengan tepat sebelum melakukan export.'],
                    ['question' => 'Mengapa ada pegawai yang tidak masuk daftar export WhatsApp?', 'answer' => 'Pegawai tidak masuk daftar export WhatsApp apabila nomor telepon belum tersedia atau format nomor tidak sesuai.', 'solution' => 'Lengkapi data nomor HP pegawai dan pastikan formatnya valid.'],
                ],
            ],
            [
                'slug' => 'manajemen-user',
                'title' => 'Manajemen User',
                'icon' => 'bi-person-gear',
                'items' => [
                    ['question' => 'Bagaimana cara menambah user baru?', 'answer' => 'User baru dapat ditambahkan melalui menu User dengan mengisi nama, email, password, role, dan data pendukung lainnya.', 'solution' => 'Isi seluruh data user dengan benar, pilih role sesuai kebutuhan, lalu simpan.'],
                    ['question' => 'Bagaimana cara mengubah data user?', 'answer' => 'Perubahan data user dilakukan melalui fitur edit pada menu User.', 'solution' => 'Pilih akun yang akan diubah, lakukan pembaruan data, lalu simpan perubahan.'],
                    ['question' => 'Bagaimana cara menghapus user?', 'answer' => 'Penghapusan user dilakukan melalui menu User, apabila akun pengguna memiliki hak akses yang sesuai.', 'solution' => 'Pastikan user yang dihapus tidak sedang digunakan dan tidak menimbulkan gangguan pada pengelolaan data.'],
                    ['question' => 'Mengapa akun yang sedang login tidak dapat dihapus?', 'answer' => 'Sistem membatasi penghapusan akun yang sedang aktif digunakan untuk menjaga keamanan dan kestabilan akses.', 'solution' => 'Gunakan akun lain yang memiliki hak akses setara apabila ingin menonaktifkan akun tertentu.'],
                    ['question' => 'Mengapa akun viewer wajib dikaitkan dengan data pegawai?', 'answer' => 'Akun viewer dikaitkan dengan data pegawai agar informasi yang ditampilkan sesuai dengan identitas dan hak akses pengguna tersebut.', 'solution' => 'Pastikan akun viewer terhubung dengan data pegawai yang benar saat pembuatan user.'],
                    ['question' => 'Mengapa email user harus unik?', 'answer' => 'Email digunakan sebagai identitas login sehingga tidak boleh sama antara satu user dengan user lainnya.', 'solution' => 'Gunakan alamat email yang berbeda untuk setiap user.'],
                ],
            ],
            [
                'slug' => 'unit-kerja',
                'title' => 'Unit Kerja',
                'icon' => 'bi-building',
                'items' => [
                    ['question' => 'Apa fungsi menu Unit Kerja?', 'answer' => 'Menu Unit Kerja digunakan untuk mengelola data struktur unit kerja sebagai dasar pengelompokan pegawai dan pengelolaan TPP.', 'solution' => 'Pastikan data unit kerja tersusun dengan benar agar pengelolaan berjalan tertib.'],
                    ['question' => 'Siapa yang dapat menambah unit kerja?', 'answer' => 'Penambahan unit kerja biasanya hanya dapat dilakukan oleh pengguna dengan hak akses tertinggi, seperti super admin.', 'solution' => 'Jika membutuhkan unit kerja baru, ajukan kepada super admin atau pengelola sistem.'],
                    ['question' => 'Mengapa menu Unit Kerja hanya muncul untuk super admin?', 'answer' => 'Karena pengelolaan unit kerja merupakan kewenangan administratif tingkat sistem yang tidak diberikan kepada seluruh role pengguna.', 'solution' => 'Gunakan akun dengan hak akses sesuai apabila perlu mengelola unit kerja.'],
                ],
            ],
            [
                'slug' => 'profil',
                'title' => 'Profil',
                'icon' => 'bi-person-circle',
                'items' => [
                    ['question' => 'Bagaimana cara mengubah profil akun?', 'answer' => 'Profil akun dapat diubah melalui menu Profil yang tersedia pada aplikasi.', 'solution' => 'Buka menu profil, lakukan perubahan data yang diperlukan, lalu simpan.'],
                    ['question' => 'Bagaimana cara mengganti password?', 'answer' => 'Password dapat diganti melalui menu Profil atau Pengaturan Akun.', 'solution' => 'Masukkan password lama, isi password baru sesuai ketentuan, lalu simpan perubahan.'],
                    ['question' => 'Bagaimana cara memperbarui foto profil?', 'answer' => 'Foto profil dapat diperbarui melalui menu Profil dengan mengunggah gambar baru.', 'solution' => 'Gunakan file gambar yang sesuai ketentuan ukuran dan format, lalu simpan perubahan.'],
                ],
            ],
            [
                'slug' => 'kendala-umum',
                'title' => 'Kendala Umum',
                'icon' => 'bi-tools',
                'items' => [
                    ['question' => 'Mengapa muncul pesan akses ditolak?', 'answer' => 'Pesan akses ditolak muncul karena pengguna tidak memiliki hak akses terhadap menu atau tindakan tertentu.', 'solution' => 'Gunakan akun dengan role yang sesuai atau hubungi admin untuk pengecekan hak akses.'],
                    ['question' => 'Mengapa halaman tertentu tidak bisa dibuka?', 'answer' => 'Halaman tidak bisa dibuka dapat disebabkan oleh keterbatasan hak akses, gangguan sistem, atau data yang belum tersedia.', 'solution' => 'Segarkan halaman, periksa kembali akses akun, dan hubungi admin jika masalah berlanjut.'],
                    ['question' => 'Mengapa data yang saya input tidak muncul?', 'answer' => 'Data yang diinput tidak muncul biasanya karena belum tersimpan, filter belum sesuai, atau terjadi kesalahan saat pemrosesan data.', 'solution' => 'Periksa kembali apakah data sudah berhasil disimpan dan pastikan filter tampilan sudah benar.'],
                    ['question' => 'Mengapa aplikasi error saat simpan, import, atau export?', 'answer' => 'Error dapat terjadi karena data tidak valid, format file tidak sesuai, atau ada kendala teknis pada sistem.', 'solution' => 'Periksa ulang data atau file yang digunakan, lalu coba ulang prosesnya. Jika masih gagal, laporkan kepada admin aplikasi.'],
                    ['question' => 'Siapa yang harus dihubungi jika terjadi masalah pada aplikasi TPP?', 'answer' => 'Jika terjadi masalah, pengguna dapat menghubungi admin aplikasi, operator yang berwenang, atau pengelola sistem pada instansi.', 'solution' => 'Sampaikan kendala secara rinci, termasuk menu yang digunakan, langkah yang dilakukan, dan pesan error yang muncul agar penanganan lebih cepat.'],
                ],
            ],
        ];

        return view('faq.index', [
            'faqCategories' => $faqCategories,
            'totalQuestions' => collect($faqCategories)->sum(fn ($category) => count($category['items'])),
        ]);
    }
}
