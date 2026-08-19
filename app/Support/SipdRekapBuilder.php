<?php

namespace App\Support;

use App\Models\Tpp;

class SipdRekapBuilder
{
    public static function rowFromTpp(Tpp $tpp): array
    {
        
        $potonganInput = max(0, min(100, (float) ($tpp->potongan_tpp ?? 0)));
        $faktorEfektif = (100 - $potonganInput) / 100;

        $bebanDasar = ((float) $tpp->referensi_beban_kerja) + (float) ($tpp->tambahan_tpp ?? 0);
        $prestasiDasar = (float) $tpp->referensi_prestasi_kerja;
        $kondisiDasar = (float) $tpp->referensi_kondisi_kerja;
        $kelangkaanDasar = (float) $tpp->referensi_kelangkaan_profesi;

        $bebanEfektif = $bebanDasar > 0 ? $bebanDasar * $faktorEfektif : 0;
        $prestasiEfektif = $prestasiDasar > 0 ? $prestasiDasar * $faktorEfektif : 0;
        $kondisiEfektif = $kondisiDasar > 0 ? $kondisiDasar * $faktorEfektif : 0;
        $kelangkaanEfektif = $kelangkaanDasar > 0 ? $kelangkaanDasar * $faktorEfektif : 0;

        $tppBebanKerja = self::componentValue($bebanEfektif, (float) $tpp->produktivitas, (float) $tpp->kehadiran, (float) $tpp->perilaku);
        $tppPrestasiKerja = self::componentValue($prestasiEfektif, (float) $tpp->produktivitas, (float) $tpp->kehadiran, (float) $tpp->perilaku);
        $tppKondisiKerja = self::componentValue($kondisiEfektif, (float) $tpp->produktivitas, (float) $tpp->kehadiran, (float) $tpp->perilaku);
        $tppKelangkaanProfesi = self::componentValue($kelangkaanEfektif, (float) $tpp->produktivitas, (float) $tpp->kehadiran, (float) $tpp->perilaku);

        $jumlahTpp = $tppBebanKerja
            + (float) ($tpp->tpp_tempat_bertugas ?? 0)
            + $tppKondisiKerja
            + $tppKelangkaanProfesi
            + $tppPrestasiKerja
            + (float) ($tpp->tunjangan_pph ?? 0)
            + (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0)
            + (float) ($tpp->iuran_jkk ?? 0)
            + (float) ($tpp->iuran_jkm ?? 0)
            + (float) ($tpp->iuran_tapera ?? 0)
            + (float) ($tpp->iuran_pensiun ?? 0)
            + (float) ($tpp->tunjangan_jht ?? 0);

        $jumlahPotongan = (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0)
            + (float) ($tpp->iuran_jkk ?? 0)
            + (float) ($tpp->iuran_jkm ?? 0)
            + (float) ($tpp->iuran_tapera ?? 0)
            + (float) ($tpp->iuran_pensiun ?? 0)
            + (float) ($tpp->tunjangan_jht ?? 0)
            + (float) ($tpp->iuran_wajib ?? 0)
            + (float) ($tpp->pajak ?? 0)
            + (float) ($tpp->zakat ?? 0)
            + (float) ($tpp->bulog ?? 0);

        return [
            'nama_pegawai' => (string) $tpp->referensi_nama,
            'nip_pegawai' => (string) $tpp->referensi_nip,
            'nik_pegawai' => (string) $tpp->referensi_nik,
            'tanggal_lahir_pegawai' => $tpp->referensi_tanggal_lahir ? date('d-m-Y', strtotime((string) $tpp->referensi_tanggal_lahir)) : '',
            'tipe_jabatan' => (string) $tpp->referensi_tipe_jabatan,
            'nama_jabatan' => (string) ($tpp->referensi_nama_jabatan ?: $tpp->referensi_jabatan),
            'eselon' => (string) $tpp->referensi_eselon,
            'status_asn' => (string) $tpp->referensi_status_asn,
            'golongan' => (string) $tpp->referensi_golongan,
            'masa_kerja_golongan' => (string) $tpp->referensi_masa_kerja_golongan,
            'alamat' => (string) $tpp->referensi_alamat,
            'kode_bank' => (string) $tpp->referensi_kode_bank,
            'nama_bank' => (string) $tpp->referensi_nama_bank,
            'nomor_rekening_pegawai' => (string) $tpp->referensi_nomor_rekening,
            'tpp_beban_kerja' => $tppBebanKerja,
            'tpp_tempat_bertugas' => (float) ($tpp->tpp_tempat_bertugas ?? 0),
            'tpp_kondisi_kerja' => $tppKondisiKerja,
            'tpp_kelangkaan_profesi' => $tppKelangkaanProfesi,
            'tpp_prestasi_kerja' => $tppPrestasiKerja,
            'tunjangan_pph' => (float) ($tpp->tunjangan_pph ?? 0),
            'iuran_pemberi_kerja' => (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),
            'iuran_jaminan_kecelakaan_kerja' => (float) ($tpp->iuran_jkk ?? 0),
            'iuran_jaminan_kematian' => (float) ($tpp->iuran_jkm ?? 0),
            'iuran_simpanan_tapera' => (float) ($tpp->iuran_tapera ?? 0),
            'iuran_pensiun' => (float) ($tpp->iuran_pensiun ?? 0),
            'tunjangan_jaminan_hari_tua' => (float) ($tpp->tunjangan_jht ?? 0),
            'potongan_iwp' => (float) ($tpp->iuran_wajib ?? 0),
            'potongan_pph_21' => (float) ($tpp->pajak ?? 0),
            'zakat' => (float) ($tpp->zakat ?? 0),
            'bulog' => (float) ($tpp->bulog ?? 0),
            'jumlah_tpp' => $jumlahTpp,
            'jumlah_potongan' => $jumlahPotongan,
            'jumlah_di_transfer' => $jumlahTpp - $jumlahPotongan,
        ];
    }

    public static function totals(array $rows): array
    {
        $fields = [
            'tpp_beban_kerja', 'tpp_tempat_bertugas', 'tpp_kondisi_kerja', 'tpp_kelangkaan_profesi', 'tpp_prestasi_kerja',
            'tunjangan_pph', 'iuran_pemberi_kerja', 'iuran_jaminan_kecelakaan_kerja', 'iuran_jaminan_kematian',
            'iuran_simpanan_tapera', 'iuran_pensiun', 'tunjangan_jaminan_hari_tua', 'potongan_iwp', 'potongan_pph_21',
            'zakat', 'bulog', 'jumlah_tpp', 'jumlah_potongan', 'jumlah_di_transfer'
        ];
        $totals = array_fill_keys($fields, 0);
        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $totals[$field] += (float) ($row[$field] ?? 0);
            }
        }
        return $totals;
    }

    private static function componentValue(float $amount, float $produktivitas, float $kehadiran, float $perilaku): float
    {
        $baseProduktivitas = 0.40 * $amount;
        $baseKehadiran = 0.18 * $amount;
        $basePerilaku = 0.42 * $amount;

        $nilaiProduktivitas = (float) floor(($produktivitas / 100) * $baseProduktivitas);
        $nilaiKehadiran = (float) floor(($kehadiran / 100) * $baseKehadiran);
        $nilaiPerilaku = (float) floor(($perilaku / 100) * $basePerilaku);

        return $nilaiProduktivitas + $nilaiKehadiran + $nilaiPerilaku;
    }
}
