<?php

namespace App\Services;

use App\Models\Pegawai;

class TppCalculator
{
    /**
     * Hitung semua nilai TPP berdasarkan pegawai + input persentase + iuran.
     * Mengembalikan array siap simpan ke tabel tpps (kecuali bulan/tahun/pegawai_id).
     */
    public function calculate(Pegawai $pegawai, float $produktivitas, float $kehadiran, float $perilaku, float $iuranWajib): array
{
    $kelas = $pegawai->kelasJabatan;
    if (!$kelas) {
        return [
            'produktivitas'   => $produktivitas,
            'kehadiran'       => $kehadiran,
            'perilaku'        => $perilaku,
            'iuran_wajib'     => $this->roundMoney($iuranWajib),
            'tpp_kotor'       => 0.00,
            'pajak'           => 0.00,
            'zakat'           => 0.00,
            'total_diterima'  => 0.00,
        ];
    }

    $komponen = [
        (float) $kelas->beban_kerja,
        (float) $kelas->prestasi_kerja,
        (float) $kelas->kondisi_kerja,
        (float) $kelas->kelangkaan_profesi,
    ];

    // 1) Hitung TPP Kotor (40% / 18% / 42%)
    $totalTppKotor = 0.0;
    foreach ($komponen as $nilai) {
        $totalTppKotor +=
            ($produktivitas / 100) * (0.40 * $nilai) +
            ($kehadiran     / 100) * (0.18 * $nilai) +
            ($perilaku      / 100) * (0.42 * $nilai);
    }

    // 2) TPP Kotor boleh 2 desimal
    $tppKotor = $this->roundMoney($totalTppKotor);

    // 3) Tentukan golongan untuk pajak
    $g = strtoupper(trim((string) $pegawai->golongan));
    if (is_numeric($g)) {
        $gol = (int) $g;
    } elseif (str_starts_with($g, 'II')) {
        $gol = 2;
    } elseif (str_starts_with($g, 'III')) {
        $gol = 3;
    } elseif (str_starts_with($g, 'IV')) {
        $gol = 4;
    } else {
        $gol = 3;
    }
    $pajakRate = ($gol <= 3) ? 0.05 : 0.15;

    // 4) Dasar pajak = TPP Kotor - Iuran (2 desimal), minimal 0
    $setelahIuran = $this->roundMoney($tppKotor - $iuranWajib);
    if ($setelahIuran < 0) $setelahIuran = 0.00;

    // 5) Pajak dibulatkan ke rupiah (0 desimal) lalu simpan 2 desimal (.00)
    $pajak = round($setelahIuran * $pajakRate, 0);
    $setelahPajak = $this->roundMoney($setelahIuran - $pajak);

    // 6) Zakat dibulatkan ke rupiah (0 desimal) jika Islam
    $zakat = (strtolower((string) $pegawai->agama) === 'islam')
        ? round($setelahPajak * 0.025, 0)
        : 0.00;

    // 7) Total diterima (2 desimal)
    $totalDiterima = $this->roundMoney($setelahPajak - $zakat);

    return [
        'produktivitas'   => $produktivitas,
        'kehadiran'       => $kehadiran,
        'perilaku'        => $perilaku,
        'iuran_wajib'     => $this->roundMoney($iuranWajib),

        'tpp_kotor'       => $tppKotor,                    // bisa xxx.xx
        'pajak'           => $this->roundMoney($pajak),    // jadi xxx.00
        'zakat'           => $this->roundMoney($zakat),    // jadi xxx.00
        'total_diterima'  => $totalDiterima,              // 2 desimal
    ];
}

    private function roundMoney(float $value): float
    {
        // simpan 2 desimal sesuai kolom decimal(15,2)
        return round($value, 2);
    }
}