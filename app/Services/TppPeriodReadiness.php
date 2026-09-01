<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Tpp;
use Illuminate\Support\Collection;

class TppPeriodReadiness
{
    public function analyze(int $unitKerjaId, int $bulan, int $tahun): array
    {
        $pegawais = Pegawai::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->activeForPeriod($bulan, $tahun)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip', 'kelas_jabatan_id']);

        $calculatedIds = Tpp::query()
            ->forUnit($unitKerjaId)
            ->whereIn('pegawai_id', $pegawais->pluck('id'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->pluck('pegawai_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $missingTpp = $pegawais
            ->reject(fn (Pegawai $pegawai) => $calculatedIds->has($pegawai->id))
            ->values();
        $missingKelas = $pegawais
            ->whereNull('kelas_jabatan_id')
            ->values();
        $readyPegawais = $pegawais
            ->filter(fn (Pegawai $pegawai) => $pegawai->kelas_jabatan_id && $calculatedIds->has($pegawai->id))
            ->count();

        return [
            'ready' => $pegawais->isNotEmpty() && $missingTpp->isEmpty() && $missingKelas->isEmpty(),
            'total' => $pegawais->count(),
            'calculated' => $calculatedIds->count(),
            'ready_count' => $readyPegawais,
            'percentage' => $pegawais->isNotEmpty() ? (int) round(($readyPegawais / $pegawais->count()) * 100) : 0,
            'missing_tpp' => $missingTpp,
            'missing_kelas' => $missingKelas,
            'message' => $this->message($pegawais, $missingTpp, $missingKelas),
        ];
    }

    private function message(Collection $pegawais, Collection $missingTpp, Collection $missingKelas): string
    {
        if ($pegawais->isEmpty()) {
            return 'Periode belum siap karena tidak ada pegawai aktif pada unit kerja ini.';
        }

        $issues = [];
        if ($missingTpp->isNotEmpty()) {
            $issues[] = $missingTpp->count() . ' pegawai belum memiliki rincian TPP';
        }
        if ($missingKelas->isNotEmpty()) {
            $issues[] = $missingKelas->count() . ' pegawai belum memiliki kelas jabatan';
        }

        return $issues
            ? 'Periode belum siap: ' . implode('; ', $issues) . '.'
            : 'Seluruh pegawai aktif sudah memiliki kelas jabatan dan rincian TPP.';
    }
}
