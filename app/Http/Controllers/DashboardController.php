<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $bulan = (int) ($request->bulan ?? $defaultPeriod->month);
        $tahun = (int) ($request->tahun ?? $defaultPeriod->year);
        $viewerMode = $request->user()?->role === 'viewer';
        $viewerPegawai = $viewerMode ? $request->user()->pegawai : null;
        $viewerNeedsPegawaiMapping = $viewerMode && !$viewerPegawai;

        $viewerProfileCompletion = 0;
        $viewerProfileFieldsFilled = 0;
        $viewerProfileFieldsTotal = 0;
        $viewerSelectedTpp = null;
        $viewerLatestTpp = null;
        $viewerRecentPeriods = collect();
        $viewerAverageProduktivitas = 0;
        $viewerAverageKehadiran = 0;
        $viewerAveragePerilaku = 0;
        $viewerProfileChecklist = collect();
        $availableUnitKerjas = collect();
        $selectedUnitKerjaId = null;
        $activeUnitKerja = $request->user()?->unitKerja;

        if ($viewerMode) {
            $baseQuery = Tpp::query()
                ->when($viewerPegawai, fn ($query) => $query->where('pegawai_id', $viewerPegawai->id))
                ->where('bulan', $bulan)
                ->where('tahun', $tahun);

            $historyQuery = Tpp::query()->when($viewerPegawai, fn ($query) => $query->where('pegawai_id', $viewerPegawai->id));
            $jumlahPerhitungan = $viewerNeedsPegawaiMapping ? 0 : (int) (clone $baseQuery)->count();
            $totalTppKotor = $viewerNeedsPegawaiMapping ? 0 : (float) (clone $baseQuery)->sum('tpp_kotor');
            $totalBpjs = $viewerNeedsPegawaiMapping ? 0 : (float) (clone $baseQuery)->sum('iuran_wajib');
            $totalPajak = $viewerNeedsPegawaiMapping ? 0 : (float) (clone $baseQuery)->sum('pajak');
            $totalZakat = $viewerNeedsPegawaiMapping ? 0 : (float) (clone $baseQuery)->sum('zakat');
            $totalDiterima = $viewerNeedsPegawaiMapping ? 0 : (float) (clone $baseQuery)->sum('total_diterima');
            $rataDiterima = $jumlahPerhitungan > 0 ? $totalDiterima / $jumlahPerhitungan : 0;
            $totalPegawai = $viewerPegawai ? 1 : 0;
            $pegawaiTanpaKelas = ($viewerPegawai && !$viewerPegawai->kelas_jabatan_id) ? 1 : 0;
            $pegawaiBelumDihitung = $viewerPegawai && $jumlahPerhitungan === 0 ? 1 : 0;
            $top5 = $viewerNeedsPegawaiMapping ? collect() : (clone $historyQuery)->with('pegawai')->orderByDesc('tahun')->orderByDesc('bulan')->take(5)->get();
            $periodeTerakhir = $viewerNeedsPegawaiMapping ? collect() : (clone $historyQuery)->selectRaw('tahun, bulan, COUNT(*) as jumlah, SUM(total_diterima) as total')->groupBy('tahun', 'bulan')->orderByDesc('tahun')->orderByDesc('bulan')->take(6)->get();
            $viewerSelectedTpp = $viewerNeedsPegawaiMapping
                ? null
                : (clone $baseQuery)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();
            $viewerLatestTpp = $viewerNeedsPegawaiMapping ? null : (clone $historyQuery)->orderByDesc('tahun')->orderByDesc('bulan')->orderByDesc('updated_at')->orderByDesc('id')->first();
            $viewerRecentPeriods = $viewerNeedsPegawaiMapping ? collect() : (clone $historyQuery)->select('bulan', 'tahun', 'produktivitas', 'kehadiran', 'perilaku', 'total_diterima')->orderByDesc('tahun')->orderByDesc('bulan')->orderByDesc('updated_at')->orderByDesc('id')->take(3)->get();
            $viewerAverageProduktivitas = $viewerNeedsPegawaiMapping ? 0 : round((float) ((clone $historyQuery)->avg('produktivitas') ?? 0), 2);
            $viewerAverageKehadiran = $viewerNeedsPegawaiMapping ? 0 : round((float) ((clone $historyQuery)->avg('kehadiran') ?? 0), 2);
            $viewerAveragePerilaku = $viewerNeedsPegawaiMapping ? 0 : round((float) ((clone $historyQuery)->avg('perilaku') ?? 0), 2);
            $perBulan = $viewerNeedsPegawaiMapping
                ? collect()
                : (clone $historyQuery)
                    ->selectRaw('bulan, SUM(total_diterima) as total')
                    ->where('tahun', $tahun)
                    ->groupBy('bulan')
                    ->orderBy('bulan')
                    ->get();

            $profileChecklist = [
                'NIP' => $viewerPegawai?->nip, 'NIK' => $viewerPegawai?->nik, 'No NPWP' => $viewerPegawai?->no_npwp,
                'No Rekening' => $viewerPegawai?->nomor_rekening, 'Alamat' => $viewerPegawai?->alamat, 'No HP' => $viewerPegawai?->no_hp,
                'Tanggal Lahir' => $viewerPegawai?->tanggal_lahir, 'Agama' => $viewerPegawai?->agama, 'Golongan' => $viewerPegawai?->golongan,
                'Jabatan' => $viewerPegawai?->jabatan, 'Kelas Jabatan' => optional($viewerPegawai?->kelasJabatan)->nama_kelas,
            ];
            $viewerProfileChecklist = collect($profileChecklist)->map(fn ($value, $label) => ['label' => $label, 'filled' => filled($value), 'value' => $value]);
            $viewerProfileFieldsTotal = $viewerProfileChecklist->count();
            $viewerProfileFieldsFilled = $viewerProfileChecklist->where('filled', true)->count();
            $viewerProfileCompletion = $viewerProfileFieldsTotal > 0 ? (int) round(($viewerProfileFieldsFilled / $viewerProfileFieldsTotal) * 100) : 0;
            $userAdmin = $userOperator = $userViewer = 0;
        } else {
            if ($request->user()->isSuperAdmin()) {
                $availableUnitKerjas = UnitKerja::query()->orderBy('nama_unit')->get();
                $selectedUnitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->unit_kerja_id : null;
                if ($selectedUnitKerjaId && !$availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId)) {
                    $selectedUnitKerjaId = null;
                }
                $activeUnitKerja = $selectedUnitKerjaId ? $availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId) : null;
            }

            $pegawaiScope = Pegawai::query()
                ->when(!$request->user()->isSuperAdmin(), fn ($q) => $q->where('unit_kerja_id', $request->user()->unit_kerja_id))
                ->when($request->user()->isSuperAdmin() && $selectedUnitKerjaId, fn ($q) => $q->where('unit_kerja_id', $selectedUnitKerjaId));

            $pegawaiAktifPeriodeScope = (clone $pegawaiScope)->activeForPeriod($bulan, $tahun);

            $targetUnitKerjaId = $request->user()->isSuperAdmin() ? $selectedUnitKerjaId : (int) $request->user()->unit_kerja_id;
            $tppScope = Tpp::query()
                ->when($targetUnitKerjaId, function ($q) use ($targetUnitKerjaId) {
                    $q->where(function ($inner) use ($targetUnitKerjaId) {
                        $inner->whereHas('pegawai', fn ($pegawaiQuery) => $pegawaiQuery->where('unit_kerja_id', $targetUnitKerjaId))
                            ->orWhere(function ($fallbackQuery) use ($targetUnitKerjaId) {
                                $fallbackQuery->whereNull('pegawai_id')
                                    ->where('unit_kerja_id', $targetUnitKerjaId);
                            });
                    });
                });
            $userScope = User::query()
                ->when(!$request->user()->isSuperAdmin(), fn ($q) => $q->where('unit_kerja_id', $request->user()->unit_kerja_id))
                ->when($request->user()->isSuperAdmin() && $selectedUnitKerjaId, fn ($q) => $q->where('unit_kerja_id', $selectedUnitKerjaId));

            $totalPegawai = (clone $pegawaiAktifPeriodeScope)->count();
            $pegawaiTanpaKelas = (clone $pegawaiAktifPeriodeScope)->whereNull('kelas_jabatan_id')->count();
            $jumlahPerhitungan = (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->count();
            $pegawaiBelumDihitung = max($totalPegawai - $jumlahPerhitungan, 0);
            $totalTppKotor = (float) (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->sum('tpp_kotor');
            $totalBpjs = (float) (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->sum('iuran_wajib');
            $totalPajak = (float) (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->sum('pajak');
            $totalZakat = (float) (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->sum('zakat');
            $totalDiterima = (float) (clone $tppScope)->where('bulan', $bulan)->where('tahun', $tahun)->sum('total_diterima');
            $rataDiterima = $jumlahPerhitungan > 0 ? $totalDiterima / $jumlahPerhitungan : 0;
            $top5 = (clone $tppScope)->with('pegawai')->orderByDesc('total_diterima')->where('bulan', $bulan)->where('tahun', $tahun)->take(5)->get();
            $periodeTerakhir = (clone $tppScope)->selectRaw('tahun, bulan, COUNT(*) as jumlah, SUM(total_diterima) as total')->groupBy('tahun', 'bulan')->orderByDesc('tahun')->orderByDesc('bulan')->take(6)->get();
            $userAdmin = (clone $userScope)->whereIn('role', ['admin', 'super_admin'])->count();
            $userOperator = (clone $userScope)->where('role', 'operator')->count();
            $userViewer = (clone $userScope)->where('role', 'viewer')->count();
            $perBulan = (clone $tppScope)->selectRaw('bulan, SUM(total_diterima) as total')->where('tahun', $tahun)->groupBy('bulan')->orderBy('bulan')->get();
        }

        $namaBulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $labels = [];
        $values = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $namaBulan[$i];
            $found = $perBulan->firstWhere('bulan', $i);
            $values[] = $found ? (float) $found->total : 0;
        }

        return view('dashboard.index', compact('bulan', 'tahun', 'viewerMode', 'viewerPegawai', 'viewerNeedsPegawaiMapping', 'totalPegawai', 'pegawaiTanpaKelas', 'pegawaiBelumDihitung', 'jumlahPerhitungan', 'totalTppKotor', 'totalBpjs', 'totalPajak', 'totalZakat', 'totalDiterima', 'rataDiterima', 'top5', 'periodeTerakhir', 'userAdmin', 'userOperator', 'userViewer', 'labels', 'values', 'viewerProfileCompletion', 'viewerProfileFieldsFilled', 'viewerProfileFieldsTotal', 'viewerSelectedTpp', 'viewerLatestTpp', 'viewerRecentPeriods', 'viewerAverageProduktivitas', 'viewerAverageKehadiran', 'viewerAveragePerilaku', 'viewerProfileChecklist', 'availableUnitKerjas', 'selectedUnitKerjaId', 'activeUnitKerja'));
    }
}
