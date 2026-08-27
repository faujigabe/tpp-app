<?php

namespace App\Services;

use App\Models\Pegawai;
use Illuminate\Support\Arr;

class TppCalculator
{
    /**
     * Hitung semua nilai TPP berdasarkan pegawai + input persentase + iuran.
     * Potongan TPP diinput dalam persen potongan. Nilai efektif = 100 - potongan.
     * Mengembalikan array siap simpan ke tabel tpps (kecuali bulan/tahun/pegawai_id).
     */
    public function calculate(
        Pegawai $pegawai,
        float $produktivitas,
        float $kehadiran,
        float $perilaku,
        float $iuranWajib,
        float $tambahanTpp = 0,
        float $potonganTpp = 0,
        bool $hitungPajak = false
    ): array {
        $kelas = $pegawai->kelasJabatan;

        $bebanKerja = (float) optional($kelas)->beban_kerja + max(0, $tambahanTpp);
        $prestasiKerja = (float) optional($kelas)->prestasi_kerja;
        $kondisiKerja = (float) optional($kelas)->kondisi_kerja;
        $kelangkaanProfesi = (float) optional($kelas)->kelangkaan_profesi;

        $potonganInput = max(0, min(100, $potonganTpp));
        $persenEfektif = max(0, 100 - $potonganInput);
        $faktorEfektif = $persenEfektif / 100;

        $komponen = [
            'beban_kerja' => $bebanKerja,
            'prestasi_kerja' => $prestasiKerja,
            'kondisi_kerja' => $kondisiKerja,
            'kelangkaan_profesi' => $kelangkaanProfesi,
        ];

        foreach ($komponen as $key => $nilai) {
            if ($nilai > 0) {
                $komponen[$key] = $nilai * $faktorEfektif;
            }
        }

        $totalTppKotor = 0.0;
        foreach ($komponen as $nilai) {
            $breakdown = $this->componentBreakdown($nilai, $produktivitas, $kehadiran, $perilaku);
            $totalTppKotor += $breakdown['jml'];
        }

        $tppKotor = $this->roundMoney($totalTppKotor);

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

        $setelahIuran = $this->roundMoney($tppKotor - $iuranWajib);
        if ($setelahIuran < 0) {
            $setelahIuran = 0.00;
        }

        $pajak = $hitungPajak ? round($setelahIuran * $pajakRate, 0) : 0.0;
        $setelahPajak = $this->roundMoney($setelahIuran - $pajak);

        $zakat = (strtolower((string) $pegawai->agama) === 'islam')
            ? round($setelahPajak * 0.025, 0)
            : 0.00;

        $totalDiterima = $this->roundMoney($setelahPajak - $zakat);

        return [
            'produktivitas'   => $produktivitas,
            'kehadiran'       => $kehadiran,
            'perilaku'        => $perilaku,
            'iuran_wajib'     => $this->roundMoney($iuranWajib),
            'tambahan_tpp'    => $this->roundMoney($tambahanTpp),
            'potongan_tpp'    => $this->roundMoney($potonganInput),

            'tpp_kotor'       => $tppKotor,
            'hitung_pajak'    => $hitungPajak,
            'pajak'           => $this->roundMoney($pajak),
            'zakat'           => $this->roundMoney($zakat),
            'total_diterima'  => $totalDiterima,
        ];
    }


    public function calculateFromSnapshot(
        array $snapshot,
        float $produktivitas,
        float $kehadiran,
        float $perilaku,
        float $iuranWajib,
        float $tambahanTpp = 0,
        float $potonganTpp = 0,
        bool $hitungPajak = false
    ): array {
        $bebanKerja = (float) Arr::get($snapshot, 'kelas_jabatan.beban_kerja', 0) + max(0, $tambahanTpp);
        $prestasiKerja = (float) Arr::get($snapshot, 'kelas_jabatan.prestasi_kerja', 0);
        $kondisiKerja = (float) Arr::get($snapshot, 'kelas_jabatan.kondisi_kerja', 0);
        $kelangkaanProfesi = (float) Arr::get($snapshot, 'kelas_jabatan.kelangkaan_profesi', 0);

        $potonganInput = max(0, min(100, $potonganTpp));
        $persenEfektif = max(0, 100 - $potonganInput);
        $faktorEfektif = $persenEfektif / 100;

        $komponen = [
            'beban_kerja' => $bebanKerja,
            'prestasi_kerja' => $prestasiKerja,
            'kondisi_kerja' => $kondisiKerja,
            'kelangkaan_profesi' => $kelangkaanProfesi,
        ];

        foreach ($komponen as $key => $nilai) {
            if ($nilai > 0) {
                $komponen[$key] = $nilai * $faktorEfektif;
            }
        }

        $totalTppKotor = 0.0;
        foreach ($komponen as $nilai) {
            $breakdown = $this->componentBreakdown($nilai, $produktivitas, $kehadiran, $perilaku);
            $totalTppKotor += $breakdown['jml'];
        }

        $tppKotor = $this->roundMoney($totalTppKotor);

        $g = strtoupper(trim((string) Arr::get($snapshot, 'golongan', '')));
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

        $setelahIuran = $this->roundMoney($tppKotor - $iuranWajib);
        if ($setelahIuran < 0) {
            $setelahIuran = 0.00;
        }

        $pajak = $hitungPajak ? round($setelahIuran * $pajakRate, 0) : 0.0;
        $setelahPajak = $this->roundMoney($setelahIuran - $pajak);

        $zakat = (strtolower((string) Arr::get($snapshot, 'agama', '')) === 'islam')
            ? round($setelahPajak * 0.025, 0)
            : 0.00;

        $totalDiterima = $this->roundMoney($setelahPajak - $zakat);

        return [
            'produktivitas'   => $produktivitas,
            'kehadiran'       => $kehadiran,
            'perilaku'        => $perilaku,
            'iuran_wajib'     => $this->roundMoney($iuranWajib),
            'tambahan_tpp'    => $this->roundMoney($tambahanTpp),
            'potongan_tpp'    => $this->roundMoney($potonganInput),
            'tpp_kotor'       => $tppKotor,
            'hitung_pajak'    => $hitungPajak,
            'pajak'           => $this->roundMoney($pajak),
            'zakat'           => $this->roundMoney($zakat),
            'total_diterima'  => $totalDiterima,
        ];
    }

    private function componentBreakdown(float $nilai, float $produktivitas, float $kehadiran, float $perilaku): array
    {
        $pk = $this->floorMoney(($produktivitas / 100) * (0.40 * $nilai));
        $dk = $this->floorMoney(($kehadiran / 100) * (0.18 * $nilai));
        $pr = $this->floorMoney(($perilaku / 100) * (0.42 * $nilai));

        return [
            'pk' => $pk,
            'dk' => $dk,
            'pr' => $pr,
            'jml' => $pk + $dk + $pr,
        ];
    }

    private function floorMoney(float $value): float
    {
        return (float) floor($value);
    }

    private function roundMoney(float $value): float
    {
        return round($value, 2);
    }
}
