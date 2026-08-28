<?php

namespace App\Support;

use App\Models\Tpp;

class TppRekapBuilder
{
    public static function rowFromTpp(Tpp $tpp): array
    {
        $produktivitas = (float) $tpp->produktivitas;
        $kehadiran = (float) $tpp->kehadiran;
        $perilaku = (float) $tpp->perilaku;

        $potonganInput = max(0, min(100, (float) ($tpp->potongan_tpp ?? 0)));
        $faktorEfektif = (100 - $potonganInput) / 100;
        $bebanDasar = (float) $tpp->referensi_beban_kerja + max(0, (float) ($tpp->tambahan_tpp ?? 0));
        $prestasiDasar = (float) $tpp->referensi_prestasi_kerja;
        $kondisiDasar = (float) $tpp->referensi_kondisi_kerja;
        $kelangkaanDasar = (float) $tpp->referensi_kelangkaan_profesi;

        $breakdown = static function (float $nilai) use ($produktivitas, $kehadiran, $perilaku, $faktorEfektif): array {
            if ($nilai > 0) {
                $nilai *= $faktorEfektif;
            }

            $pk = (float) floor(($produktivitas / 100) * (0.40 * $nilai));
            $dk = (float) floor(($kehadiran / 100) * (0.18 * $nilai));
            $pr = (float) floor(($perilaku / 100) * (0.42 * $nilai));

            return ['pk' => $pk, 'dk' => $dk, 'pr' => $pr, 'jml' => $pk + $dk + $pr];
        };

        $beban = $breakdown($bebanDasar);
        $prestasi = $breakdown($prestasiDasar);
        $kondisi = $breakdown($kondisiDasar);
        $kelangkaan = $breakdown($kelangkaanDasar);

        $jumlahBesaran = 0.0;
        foreach ([$bebanDasar, $prestasiDasar, $kondisiDasar, $kelangkaanDasar] as $nilai) {
            if ((float) $nilai > 0) {
                $jumlahBesaran += (float) $nilai * $faktorEfektif;
            }
        }

        // Sama dengan TppCalculator: iuran tidak boleh membuat dasar pajak negatif.
        $setelahBpjs = (float) max(0, round((float) $tpp->tpp_kotor - (float) $tpp->iuran_wajib, 2));
        $setelahPajak = round($setelahBpjs - (float) $tpp->pajak, 2);

        return [
            'beban_pk' => $beban['pk'], 'beban_dk' => $beban['dk'], 'beban_pr' => $beban['pr'], 'beban_jml' => $beban['jml'],
            'pres_pk' => $prestasi['pk'], 'pres_dk' => $prestasi['dk'], 'pres_pr' => $prestasi['pr'], 'pres_jml' => $prestasi['jml'],
            'kond_pk' => $kondisi['pk'], 'kond_dk' => $kondisi['dk'], 'kond_pr' => $kondisi['pr'], 'kond_jml' => $kondisi['jml'],
            'lang_pk' => $kelangkaan['pk'], 'lang_dk' => $kelangkaan['dk'], 'lang_pr' => $kelangkaan['pr'], 'lang_jml' => $kelangkaan['jml'],
            'jumlah_besaran' => (float) $jumlahBesaran,
            'tpp_kotor' => (float) $tpp->tpp_kotor,
            'bpjs1' => (float) $tpp->iuran_wajib,
            'bpjs4' => (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),
            'setelah_bpjs' => $setelahBpjs,
            'pajak' => (float) $tpp->pajak,
            'setelah_pajak' => $setelahPajak,
            'zakat' => (float) $tpp->zakat,
            'diterima' => (float) $tpp->total_diterima,
        ];
    }

    public static function emptyTotals(): array
    {
        return array_fill_keys([
            'beban_pk', 'beban_dk', 'beban_pr', 'beban_jml',
            'pres_pk', 'pres_dk', 'pres_pr', 'pres_jml',
            'kond_pk', 'kond_dk', 'kond_pr', 'kond_jml',
            'lang_pk', 'lang_dk', 'lang_pr', 'lang_jml',
            'jumlah_besaran', 'tpp_kotor', 'bpjs1', 'bpjs4',
            'setelah_bpjs', 'pajak', 'setelah_pajak', 'zakat', 'diterima',
        ], 0);
    }

    public static function totals(iterable $tpps): array
    {
        $totals = self::emptyTotals();

        foreach ($tpps as $tpp) {
            foreach (self::rowFromTpp($tpp) as $key => $value) {
                $totals[$key] += (float) $value;
            }
        }

        return $totals;
    }
}
