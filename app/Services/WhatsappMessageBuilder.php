<?php

namespace App\Services;

use App\Models\Tpp;
use App\Support\TppRekapBuilder;

/**
 * Pembangun nomor & pesan WhatsApp TPP.
 *
 * Logika di kelas ini disalin persis dari TppController
 * (formatWhatsappNumber, buildWhatsappMessage, whatsappGreeting,
 * whatsappTaxLabel) agar pesan yang dikirim lewat gateway OpenWA
 * identik dengan pesan yang selama ini dikirim manual lewat wa.me.
 * TppController tidak diubah, jadi fitur wa.me tetap berjalan seperti semula.
 */
class WhatsappMessageBuilder
{
    public function formatNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        } elseif (!str_starts_with($digits, '62')) {
            return null;
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    /** Format chatId untuk OpenWA, contoh: 6281234567890@c.us */
    public function chatId(?string $phone): ?string
    {
        $digits = $this->formatNumber($phone);

        return $digits ? $digits . '@c.us' : null;
    }

    public function buildMessage(Tpp $tpp): string
    {
        $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        $periode = ($bulanNama[(int) $tpp->bulan] ?? $tpp->bulan) . '/' . $tpp->tahun;
        $rupiah = fn ($angka) => 'Rp. ' . number_format((float) $angka, 0, ',', '.');
        $persen = fn ($angka) => rtrim(rtrim(number_format((float) $angka, 2, '.', ''), '0'), '.') . '%';

        $sapaan = $this->greeting();
        $namaPegawai = trim((string) $tpp->referensi_nama);
        $nip = trim((string) $tpp->referensi_nip);
        $pajakLabel = $this->taxLabel($tpp);
        $breakdown = TppRekapBuilder::rowFromTpp($tpp);

        return collect([
            $sapaan . ' Bpk/Ibu,',
            null,
            'Berikut kami sampaikan rincian perhitungan TPP periode *' . $periode . '*:',
            null,
            '*' . $namaPegawai . '*',
            $nip !== '' ? 'NIP. ' . $nip : null,
            null,
            '*1. Indikator Kinerja*',
            '• Produktivitas : ' . $persen($tpp->produktivitas),
            '• Kehadiran : ' . $persen($tpp->kehadiran),
            '• Perilaku : ' . $persen($tpp->perilaku),
            null,
            '*2. Rincian TPP Kotor*',
            '• Beban Kerja : ' . $rupiah($breakdown['beban_jml']),
            '• Prestasi Kerja : ' . $rupiah($breakdown['pres_jml']),
            '• Kondisi Kerja : ' . $rupiah($breakdown['kond_jml']),
            '• Kelangkaan Profesi : ' . $rupiah($breakdown['lang_jml']),
            '• Total TPP Kotor : ' . $rupiah($tpp->tpp_kotor),
            null,
            '*3. Potongan*',
            '• BPJS 1% : ' . $rupiah($tpp->iuran_wajib),
            '• ' . $pajakLabel . ' : ' . $rupiah($tpp->pajak),
            '• Zakat 2,5% : ' . $rupiah($tpp->zakat),
            null,
            '*4. Total TPP Diterima : ' . $rupiah($tpp->total_diterima) . '*',
            null,
            'Apabila terdapat kekeliruan pada data kinerja maupun rincian perhitungan, silakan menghubungi *Admin E-TPP*.',
            null,
            'Terima kasih atas perhatian Bapak/Ibu.',
        ])->filter(fn ($line) => $line !== null)->implode("\n");
    }

    public function greeting(): string
    {
        $hour = now('Asia/Jakarta')->hour;

        if ($hour < 11) {
            return 'Selamat Pagi';
        }

        if ($hour < 15) {
            return 'Selamat Siang';
        }

        if ($hour < 18) {
            return 'Selamat Sore';
        }

        return 'Selamat Malam';
    }

    public function taxLabel(Tpp $tpp): string
    {
        $golongan = strtoupper(trim((string) $tpp->referensi_golongan));

        if (!$tpp->hitung_pajak) {
            return 'Pajak';
        }

        if (str_starts_with($golongan, 'II') || str_starts_with($golongan, 'III')) {
            return 'Pajak 5%';
        }

        if (str_starts_with($golongan, 'IV')) {
            return 'Pajak 15%';
        }

        $dasarPajak = max(0, (float) $tpp->tpp_kotor - (float) $tpp->iuran_wajib);
        if ($dasarPajak <= 0 || (float) $tpp->pajak <= 0) {
            return 'Pajak';
        }

        $persen = round(((float) $tpp->pajak / $dasarPajak) * 100, 2);
        $persenText = rtrim(rtrim(number_format($persen, 2, '.', ''), '0'), '.');

        return 'Pajak ' . $persenText . '%';
    }
}
