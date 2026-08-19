<?php

namespace App\Http\Controllers;

use App\Exports\TppExport;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Services\TppCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class TppController extends Controller
{
    public function __construct(private TppCalculator $calculator)
    {
    }

    public function create()
    {
        $pegawais = Pegawai::with('kelasJabatan')->orderBy('nama')->get();
        $pegawaiTanpaKelas = $pegawais->filter(fn ($pegawai) => !$pegawai->kelasJabatan)->values();

        return view('tpp.create', compact('pegawais', 'pegawaiTanpaKelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'produktivitas' => 'required|array',
            'kehadiran' => 'required|array',
            'perilaku' => 'required|array',
            'bpjs_kesehatan' => 'required|array',
        ]);

        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $pegawaiIds = array_map('intval', array_keys($validated['produktivitas']));

        $pegawais = Pegawai::with('kelasJabatan')
            ->whereIn('id', $pegawaiIds)
            ->get()
            ->keyBy('id');

        $pegawaiTanpaKelas = $pegawais->filter(fn ($pegawai) => !$pegawai->kelasJabatan)->pluck('nama')->all();
        if (!empty($pegawaiTanpaKelas)) {
            return back()
                ->withInput()
                ->with('error', 'Ada pegawai yang belum memiliki Kelas Jabatan: ' . implode(', ', $pegawaiTanpaKelas));
        }

        DB::transaction(function () use ($pegawaiIds, $pegawais, $validated, $bulan, $tahun) {
            foreach ($pegawaiIds as $pid) {
                if (!isset($pegawais[$pid])) {
                    continue;
                }

                $prod = (float) ($validated['produktivitas'][$pid] ?? 0);
                $keh = (float) ($validated['kehadiran'][$pid] ?? 0);
                $per = (float) ($validated['perilaku'][$pid] ?? 0);
                $bpjs = (float) ($validated['bpjs_kesehatan'][$pid] ?? 0);

                if ($prod < 0 || $prod > 100 || $keh < 0 || $keh > 100 || $per < 0 || $per > 100 || $bpjs < 0) {
                    continue;
                }

                $pegawai = $pegawais[$pid];
                $hasil = $this->calculator->calculate($pegawai, $prod, $keh, $per, $bpjs);

                Tpp::updateOrCreate(
                    ['pegawai_id' => $pid, 'bulan' => $bulan, 'tahun' => $tahun],
                    [
                        'produktivitas' => $prod,
                        'kehadiran' => $keh,
                        'perilaku' => $per,
                        'iuran_wajib' => $bpjs,
                        'tpp_kotor' => (float) ($hasil['tpp_kotor'] ?? 0),
                        'pajak' => (float) ($hasil['pajak'] ?? 0),
                        'zakat' => isset($hasil['zakat']) ? (float) $hasil['zakat'] : null,
                        'total_diterima' => (float) ($hasil['total_diterima'] ?? 0),
                    ]
                );
            }
        });

        return redirect()->route('tpp.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Perhitungan TPP berhasil disimpan untuk seluruh pegawai (Bulan {$bulan} / Tahun {$tahun}).");
    }

    public function index(Request $request)
    {
        [$bulan, $tahun] = $this->resolvePeriod($request);

        $tpps = Tpp::with('pegawai')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('pegawai_id')
            ->paginate(25)
            ->appends(['bulan' => $bulan, 'tahun' => $tahun]);

        return view('tpp.index', [
            'tpps' => $tpps,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jumlahDataFilter' => $tpps->total(),
        ]);
    }

    public function destroy(Tpp $tpp)
    {
        $tpp->delete();
        return back()->with('success', 'Data TPP berhasil dihapus.');
    }

    public function destroyMassal(Request $request)
    {
        $data = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'konfirmasi_hapus' => ['required', function ($attribute, $value, $fail) {
                if (strtoupper(trim((string) $value)) !== 'HAPUS') {
                    $fail('Konfirmasi hapus harus diisi dengan kata HAPUS.');
                }
            }],
            'password_konfirmasi' => 'required|string',
        ]);

        if (!Hash::check($data['password_konfirmasi'], (string) optional($request->user())->password)) {
            return redirect()->route('tpp.index', [
                'bulan' => (int) $data['bulan'],
                'tahun' => (int) $data['tahun'],
            ])->with('error', 'Password akun yang Anda masukkan tidak sesuai.');
        }

        $deleted = Tpp::where('bulan', (int) $data['bulan'])
            ->where('tahun', (int) $data['tahun'])
            ->delete();

        if ($deleted === 0) {
            return redirect()->route('tpp.index', [
                'bulan' => (int) $data['bulan'],
                'tahun' => (int) $data['tahun'],
            ])->with('error', 'Tidak ada data TPP pada periode tersebut untuk dihapus.');
        }

        return redirect()->route('tpp.index', [
            'bulan' => (int) $data['bulan'],
            'tahun' => (int) $data['tahun'],
        ])->with('success', "Data TPP periode berhasil dihapus: {$deleted} data.");
    }

    public function cetak(Request $request)
    {
        [$bulan, $tahun] = $this->resolvePeriod($request);
        $tpps = Tpp::with('pegawai')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $pdf = Pdf::loadView('tpp.pdf', [
            'tpps' => $tpps,
            'request' => new Request(['bulan' => $bulan, 'tahun' => $tahun]),
        ]);

        return $pdf->download("Laporan_TPP_{$bulan}_{$tahun}.pdf");
    }

    public function exportExcel(Request $request)
    {
        [$bulan, $tahun] = $this->resolvePeriod($request);
        return Excel::download(new TppExport($bulan, $tahun), "Laporan_TPP_{$bulan}_{$tahun}.xlsx");
    }

    public function editMassal(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $tpps = Tpp::with('pegawai')
            ->where('bulan', (int) $request->bulan)
            ->where('tahun', (int) $request->tahun)
            ->get();

        return view('tpp.edit-massal', compact('tpps', 'request'));
    }

    public function updateMassal(Request $request)
    {
        $request->validate([
            'tpp' => 'required|array',
            'tpp.*.produktivitas' => 'required|numeric|min:0|max:100',
            'tpp.*.kehadiran' => 'required|numeric|min:0|max:100',
            'tpp.*.perilaku' => 'required|numeric|min:0|max:100',
            'tpp.*.iuran_wajib' => 'required|numeric|min:0',
        ]);

        foreach ($request->tpp as $id => $row) {
            $tpp = Tpp::with('pegawai.kelasJabatan')->findOrFail($id);

            $hasil = $this->calculator->calculate(
                $tpp->pegawai,
                (float) $row['produktivitas'],
                (float) $row['kehadiran'],
                (float) $row['perilaku'],
                (float) $row['iuran_wajib']
            );

            $tpp->update(array_merge($hasil, [
                'produktivitas' => (float) $row['produktivitas'],
                'kehadiran' => (float) $row['kehadiran'],
                'perilaku' => (float) $row['perilaku'],
                'iuran_wajib' => (float) $row['iuran_wajib'],
            ]));
        }

        return redirect()->route('tpp.index')->with('success', 'Update massal berhasil.');
    }

    public function edit(Tpp $tpp)
    {
        $tpp->load('pegawai.kelasJabatan');
        return view('tpp.edit', compact('tpp'));
    }

    public function update(Request $request, Tpp $tpp)
    {
        $validated = $request->validate([
            'produktivitas' => 'required|numeric|min:0|max:100',
            'kehadiran' => 'required|numeric|min:0|max:100',
            'perilaku' => 'required|numeric|min:0|max:100',
            'bpjs_kesehatan' => 'required|numeric|min:0',
        ]);

        $tpp->load('pegawai.kelasJabatan');

        $prod = (float) $validated['produktivitas'];
        $keh = (float) $validated['kehadiran'];
        $per = (float) $validated['perilaku'];
        $bpjs = (float) $validated['bpjs_kesehatan'];

        $hasil = $this->calculator->calculate($tpp->pegawai, $prod, $keh, $per, $bpjs);

        $tpp->update([
            'produktivitas' => $prod,
            'kehadiran' => $keh,
            'perilaku' => $per,
            'iuran_wajib' => $bpjs,
            'tpp_kotor' => (float) ($hasil['tpp_kotor'] ?? 0),
            'pajak' => (float) ($hasil['pajak'] ?? 0),
            'zakat' => isset($hasil['zakat']) ? (float) $hasil['zakat'] : null,
            'total_diterima' => (float) ($hasil['total_diterima'] ?? 0),
        ]);

        return redirect()->route('tpp.index', ['bulan' => $tpp->bulan, 'tahun' => $tpp->tahun])
            ->with('success', 'Data TPP berhasil diperbarui.');
    }

    public function rekap(Request $request)
    {
        [$bulan, $tahun] = $this->resolvePeriod($request);

        $rows = Tpp::with(['pegawai.kelasJabatan'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy(Pegawai::select('nama')->whereColumn('pegawais.id', 'tpps.pegawai_id'))
            ->get();

        return view('tpp.rekap', compact('rows', 'bulan', 'tahun'));
    }

    private function resolvePeriod(Request $request): array
    {
        $bulan = $request->filled('bulan') ? (int) $request->bulan : (int) now()->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) now()->year;

        return [$bulan, $tahun];
    }
}
