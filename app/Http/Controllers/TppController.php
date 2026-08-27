<?php

namespace App\Http\Controllers;

use App\Models\Tpp;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\TppApproval;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TppExport;
use App\Exports\TppWhatsappExport;
use App\Exports\TppRekapExport;
use App\Exports\TppSipdExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\TppCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Support\SipdRekapBuilder;
use App\Support\PegawaiSnapshotFactory;
use App\Services\EkinerjaPdfImportService;
use Illuminate\Validation\ValidationException;

class TppController extends Controller
{
    public function __construct(private TppCalculator $calculator) {}

    public function create(Request $request)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat melakukan input TPP langsung.');

        $availableUnitKerjas = collect();
        $selectedUnitKerjaId = null;
        $activeUnitKerja = $request->user()?->unitKerja;

        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $selectedBulan = (int) $request->integer('bulan', $defaultPeriod->month);
        $selectedTahun = (int) $request->integer('tahun', $defaultPeriod->year);

        if ($selectedBulan < 1 || $selectedBulan > 12) {
            $selectedBulan = (int) $defaultPeriod->month;
        }
        if ($selectedTahun < 2000 || $selectedTahun > 2100) {
            $selectedTahun = (int) $defaultPeriod->year;
        }

        $selectedPeriod = now()->setYear($selectedTahun)->setMonth($selectedBulan)->startOfMonth();
        $prevMonth = $selectedPeriod->copy()->subMonth();

        $pegawaiQuery = $this->pegawaiScope($request, $selectedUnitKerjaId, $selectedBulan, $selectedTahun)
            ->with('kelasJabatan')
            ->orderBy('nama');
        if ($request->user()?->isSuperAdmin() && !$selectedUnitKerjaId) {
            $pegawaiQuery->whereRaw('1 = 0');
        }
        $pegawais = $pegawaiQuery->get();

        $defaultInputs = $this->tppUnitScope(Tpp::query(), $request, $selectedUnitKerjaId)
            ->where('bulan', (int) $prevMonth->month)
            ->where('tahun', (int) $prevMonth->year)
            ->get()
            ->keyBy('pegawai_id')
            ->map(fn ($tpp) => [
                'tambahan_tpp' => (float) ($tpp->tambahan_tpp ?? 0),
                'bpjs_kesehatan' => (float) ($tpp->iuran_wajib ?? 0),
                'bpjs_kesehatan_pemberi_kerja' => (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),
                'tpp_tempat_bertugas' => (float) ($tpp->tpp_tempat_bertugas ?? 0),
                'tunjangan_pph' => (float) ($tpp->tunjangan_pph ?? 0),
                'iuran_jkk' => (float) ($tpp->iuran_jkk ?? 0),
                'iuran_jkm' => (float) ($tpp->iuran_jkm ?? 0),
                'iuran_tapera' => (float) ($tpp->iuran_tapera ?? 0),
                'iuran_pensiun' => (float) ($tpp->iuran_pensiun ?? 0),
                'tunjangan_jht' => (float) ($tpp->tunjangan_jht ?? 0),
                'bulog' => (float) ($tpp->bulog ?? 0),
                // Kebijakan saat ini: pajak ditangani bendahara/BKAD, sehingga
                // periode baru selalu dimulai dengan perhitungan pajak nonaktif.
                'hitung_pajak' => false,
            ]);

        $currentPeriodInputs = $this->tppUnitScope(Tpp::query(), $request, $selectedUnitKerjaId)
            ->where('bulan', $selectedBulan)
            ->where('tahun', $selectedTahun)
            ->get()
            ->keyBy('pegawai_id');

        $periodApproval = $this->getOrInitializeApproval($activeUnitKerja?->id, $selectedBulan, $selectedTahun);
        $ekinerjaImport = session('ekinerja_import');
        if (($ekinerjaImport['bulan'] ?? null) !== $selectedBulan || ($ekinerjaImport['tahun'] ?? null) !== $selectedTahun) {
            $ekinerjaImport = null;
        }

        return view('tpp.create', compact(
            'pegawais',
            'defaultInputs',
            'currentPeriodInputs',
            'selectedBulan',
            'selectedTahun',
            'prevMonth',
            'availableUnitKerjas',
            'selectedUnitKerjaId',
            'activeUnitKerja',
            'periodApproval',
            'ekinerjaImport'
        ));
    }


    public function importEkinerjaPdf(Request $request, EkinerjaPdfImportService $pdfImportService)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat melakukan input TPP langsung.');

        $validated = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'ekinerja_pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $unitKerjaId = (int) ($request->user()->unit_kerja_id);

        $pegawais = $this->pegawaiScope($request, null, $bulan, $tahun)
            ->with('kelasJabatan')
            ->orderBy('nama')
            ->get();

        try {
            $result = $pdfImportService->import($request->file('ekinerja_pdf'), $pegawais, $bulan, $tahun);
        } catch (\Throwable $e) {
            return redirect()->route('tpp.create', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('error', $e->getMessage());
        }

        return redirect()->route('tpp.create', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('ekinerja_import', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'unit_kerja_id' => $unitKerjaId,
                'matched' => $result['matched'],
                'matched_count' => $result['matched_count'],
                'record_count' => $result['record_count'],
                'matched_by' => $result['matched_by'] ?? ['nip' => 0, 'nama' => 0],
                'unmatched' => array_slice($result['unmatched'], 0, 10),
            ])
            ->with('success', 'Import PDF e-Kinerja selesai. Berhasil dicocokkan ke pegawai unit ini: ' . $result['matched_count'] . ' dari ' . $result['record_count'] . ' baris.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'unit_kerja_id' => 'nullable|integer|exists:unit_kerjas,id',
            'produktivitas'   => 'required|array|min:1',
            'produktivitas.*' => 'required|numeric|min:0|max:100',
            'kehadiran'       => 'required|array|min:1',
            'kehadiran.*'     => 'required|numeric|min:0|max:100',
            'perilaku'        => 'required|array|min:1',
            'perilaku.*'      => 'required|numeric|min:0|max:100',
            'bpjs_kesehatan'  => 'required|array|min:1',
            'bpjs_kesehatan.*' => 'required|numeric|min:0',
            'bpjs_kesehatan_pemberi_kerja' => 'required|array|min:1',
            'bpjs_kesehatan_pemberi_kerja.*' => 'required|numeric|min:0',
            'tpp_tempat_bertugas' => 'required|array|min:1',
            'tpp_tempat_bertugas.*' => 'required|numeric|min:0',
            'tunjangan_pph' => 'required|array|min:1',
            'tunjangan_pph.*' => 'required|numeric|min:0',
            'iuran_jkk' => 'required|array|min:1',
            'iuran_jkk.*' => 'required|numeric|min:0',
            'iuran_jkm' => 'required|array|min:1',
            'iuran_jkm.*' => 'required|numeric|min:0',
            'iuran_tapera' => 'required|array|min:1',
            'iuran_tapera.*' => 'required|numeric|min:0',
            'iuran_pensiun' => 'required|array|min:1',
            'iuran_pensiun.*' => 'required|numeric|min:0',
            'tunjangan_jht' => 'required|array|min:1',
            'tunjangan_jht.*' => 'required|numeric|min:0',
            'bulog' => 'required|array|min:1',
            'bulog.*' => 'required|numeric|min:0',
            'tambahan_tpp'    => 'required|array|min:1',
            'tambahan_tpp.*'  => 'required|numeric|min:0',
            'potongan_tpp'    => 'required|array|min:1',
            'potongan_tpp.*'  => 'required|numeric|min:0|max:100',
            'hitung_pajak'    => 'nullable|array',
            'hitung_pajak.*'  => 'boolean',
        ]);

        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat melakukan input TPP langsung.');

        $selectedUnitKerjaId = null;

        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $pegawaiIds = $this->validatedRowIds($validated['produktivitas'], 'produktivitas');
        $this->assertMatchingRowIds($validated, $pegawaiIds, [
            'kehadiran',
            'perilaku',
            'bpjs_kesehatan',
            'bpjs_kesehatan_pemberi_kerja',
            'tpp_tempat_bertugas',
            'tunjangan_pph',
            'iuran_jkk',
            'iuran_jkm',
            'iuran_tapera',
            'iuran_pensiun',
            'tunjangan_jht',
            'bulog',
            'tambahan_tpp',
            'potongan_tpp',
        ]);

        $pegawais = $this->pegawaiScope($request, $selectedUnitKerjaId, $bulan, $tahun)
            ->with('kelasJabatan')
            ->whereIn('id', $pegawaiIds)
            ->get()
            ->keyBy('id');

        abort_unless(
            $pegawais->count() === count($pegawaiIds),
            403,
            'Terdapat pegawai yang tidak termasuk dalam unit kerja Anda.'
        );

        $unitKerjaId = (int) ($request->user()->unit_kerja_id);
        $this->abortIfPeriodNotEditableByUnit($unitKerjaId, $bulan, $tahun);

        $savedCount = DB::transaction(function () use ($pegawaiIds, $pegawais, $validated, $bulan, $tahun, $unitKerjaId) {
            $savedCount = 0;

            foreach ($pegawaiIds as $pid) {
                $prod = (float) ($validated['produktivitas'][$pid] ?? 0);
                $keh = (float) ($validated['kehadiran'][$pid] ?? 0);
                $per = (float) ($validated['perilaku'][$pid] ?? 0);
                $bpjs = (float) ($validated['bpjs_kesehatan'][$pid] ?? 0);
                $bpjsPemberiKerja = (float) ($validated['bpjs_kesehatan_pemberi_kerja'][$pid] ?? 0);
                $tppTempatBertugas = (float) ($validated['tpp_tempat_bertugas'][$pid] ?? 0);
                $tunjanganPph = (float) ($validated['tunjangan_pph'][$pid] ?? 0);
                $iuranJkk = (float) ($validated['iuran_jkk'][$pid] ?? 0);
                $iuranJkm = (float) ($validated['iuran_jkm'][$pid] ?? 0);
                $iuranTapera = (float) ($validated['iuran_tapera'][$pid] ?? 0);
                $iuranPensiun = (float) ($validated['iuran_pensiun'][$pid] ?? 0);
                $tunjanganJht = (float) ($validated['tunjangan_jht'][$pid] ?? 0);
                $bulog = (float) ($validated['bulog'][$pid] ?? 0);
                $tambahanTpp = (float) ($validated['tambahan_tpp'][$pid] ?? 0);
                $potonganTpp = (float) ($validated['potongan_tpp'][$pid] ?? 0);
                $hitungPajak = (bool) ((int) ($validated['hitung_pajak'][$pid] ?? 0));

                $pegawai = $pegawais[$pid];
                $hasil = $this->calculator->calculateFromSnapshot($this->buildPegawaiSnapshot($pegawai), $prod, $keh, $per, $bpjs, $tambahanTpp, $potonganTpp, $hitungPajak);

                Tpp::updateOrCreate(
                    ['pegawai_id' => $pid, 'bulan' => $bulan, 'tahun' => $tahun],
                    [
                        'unit_kerja_id' => (int) ($pegawai->unit_kerja_id ?? $unitKerjaId),
                        'produktivitas'  => $prod,
                        'kehadiran'      => $keh,
                        'perilaku'       => $per,
                        'iuran_wajib'    => $bpjs,
                        'bpjs_kesehatan_pemberi_kerja' => $bpjsPemberiKerja,
                        'tpp_tempat_bertugas' => $tppTempatBertugas,
                        'tunjangan_pph' => $tunjanganPph,
                        'iuran_jkk' => $iuranJkk,
                        'iuran_jkm' => $iuranJkm,
                        'iuran_tapera' => $iuranTapera,
                        'iuran_pensiun' => $iuranPensiun,
                        'tunjangan_jht' => $tunjanganJht,
                        'bulog' => $bulog,
                        'potongan_iwp' => $bpjs,
                        'tambahan_tpp'   => $tambahanTpp,
                        'potongan_tpp'   => $potonganTpp,
                        'hitung_pajak'   => $hitungPajak,
                        'tpp_kotor'      => (float) ($hasil['tpp_kotor'] ?? 0),
                        'pajak'          => (float) ($hasil['pajak'] ?? 0),
                        'zakat'          => isset($hasil['zakat']) ? (float) $hasil['zakat'] : null,
                        'total_diterima' => (float) ($hasil['total_diterima'] ?? 0),
                        'pegawai_snapshot' => $this->buildPegawaiSnapshot($pegawai),
                    ]
                );

                $savedCount++;
            }

            return $savedCount;
        });

        return redirect()->route('tpp.index')
            ->with('success', "Perhitungan TPP berhasil disimpan untuk {$savedCount} pegawai (Bulan $bulan / Tahun $tahun).");
    }

    public function index(Request $request)
    {
        $availableUnitKerjas = $request->user()?->isSuperAdmin()
            ? UnitKerja::query()->orderBy('nama_unit')->get()
            : collect();
        $selectedUnitKerjaId = $this->resolveSelectedUnitKerjaId($request, $availableUnitKerjas);
        $activeUnitKerja = $request->user()?->isSuperAdmin()
            ? ($selectedUnitKerjaId ? $availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId) : null)
            : $request->user()?->unitKerja;

        $query = $this->baseFilteredQuery($request)->with(['pegawai.unitKerja', 'unitKerja']);

        $orderedQuery = (clone $query)->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');

        $tpps = (clone $orderedQuery)->paginate(25)->withQueryString();
        $allFilteredTpps = (clone $orderedQuery)->get();

        $tpps->getCollection()->transform(function ($tpp) {
            $tpp->wa_link = $this->buildWhatsappLink($tpp);
            $tpp->wa_ready = !empty($tpp->wa_link);
            return $tpp;
        });

        $massWhatsappItems = $allFilteredTpps
            ->map(function ($tpp) {
                $link = $this->buildWhatsappLink($tpp);

                if (!$link) {
                    return null;
                }

                return [
                    'id' => $tpp->id,
                    'nama' => $tpp->referensi_nama,
                    'link' => $link,
                ];
            })
            ->filter()
            ->values();

        $waValidCount = $massWhatsappItems->count();
        $waMissingCount = max($allFilteredTpps->count() - $waValidCount, 0);

        $viewerMode = $request->user()?->role === 'viewer';
        $viewerPegawai = $viewerMode ? $request->user()->pegawai : null;
        $viewerNeedsPegawaiMapping = $viewerMode && !$viewerPegawai;
        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $selectedBulan = $request->filled('bulan') ? (int) $request->bulan : (int) $defaultPeriod->month;
        $selectedTahun = $request->filled('tahun') ? (int) $request->tahun : (int) $defaultPeriod->year;
        $approvalUnitKerjaId = $request->user()?->isSuperAdmin() ? $selectedUnitKerjaId : $request->user()?->unit_kerja_id;
        $periodApproval = (!$viewerMode && $approvalUnitKerjaId && $selectedBulan && $selectedTahun)
            ? $this->getOrInitializeApproval($approvalUnitKerjaId, $selectedBulan, $selectedTahun)
            : null;
        $periodStatus = $periodApproval?->normalizedStatus() ?? TppApproval::STATUS_DRAFT;
        $periodCanEdit = in_array($request->user()?->role, ['admin', 'operator'], true) && $periodStatus === TppApproval::STATUS_DRAFT;

        return view('tpp.index', compact(
            'tpps',
            'massWhatsappItems',
            'waValidCount',
            'waMissingCount',
            'viewerMode',
            'viewerPegawai',
            'viewerNeedsPegawaiMapping',
            'availableUnitKerjas',
            'selectedUnitKerjaId',
            'activeUnitKerja',
            'periodApproval',
            'periodStatus',
            'periodCanEdit'
        ));
    }

    public function destroy(Request $request, Tpp $tpp)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat menghapus TPP secara langsung.');
        $this->authorizeTpp($request, $tpp);
        $this->abortIfTppNotEditable($tpp);
        $tpp->delete();
        return back()->with('success', 'Data TPP berhasil dihapus');
    }

    public function destroyMassal(Request $request)
    {
        $data = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'konfirmasi_hapus' => 'required|in:HAPUS',
            'password_konfirmasi' => 'required|string',
        ], [
            'password_konfirmasi.required' => 'Password wajib diisi untuk menghapus massal.',
        ]);

        if (!Hash::check($data['password_konfirmasi'], (string) optional($request->user())->password)) {
            return back()
                ->withInput($request->except('password_konfirmasi'))
                ->with('error', 'Password akun yang Anda masukkan tidak sesuai.');
        }

        $this->abortIfPeriodNotEditableByRequest($request, (int) $data['bulan'], (int) $data['tahun']);

        $deleted = $this->tppUnitScope(Tpp::query(), $request)
            ->where('bulan', (int) $data['bulan'])
            ->where('tahun', (int) $data['tahun'])
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'Tidak ada data TPP pada bulan dan tahun tersebut untuk dihapus.');
        }

        return redirect()->route('tpp.index', [
            'bulan' => (int) $data['bulan'],
            'tahun' => (int) $data['tahun'],
        ])->with('success', "Data TPP berhasil dihapus massal: {$deleted} data.");
    }

    public function cetak(Request $request)
    {
        $tpps = $this->baseFilteredQuery($request)->with('pegawai')->get();
        $pdf = Pdf::loadView('tpp.pdf', compact('tpps', 'request'));
        return $pdf->download('Laporan_TPP.pdf');
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ? (int) $request->bulan : null;
        $tahun = $request->tahun ? (int) $request->tahun : null;

        $namaFile = 'Laporan_TPP' . ($bulan && $tahun ? "_{$bulan}_{$tahun}" : '') . '.xlsx';

        return Excel::download(
            new TppExport($bulan, $tahun, $request->user(), $this->resolveSelectedUnitKerjaId($request)),
            $namaFile
        );
    }

    public function exportWhatsappExcel(Request $request)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Fitur WhatsApp hanya tersedia untuk admin/operator unit kerja.');

        $rows = $this->baseFilteredQuery($request)
            ->with('pegawai')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get()
            ->map(function ($tpp) {
                $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                $breakdown = $this->calculateRekapBreakdowns($tpp);
                $tpp->wa_tax_label = $this->whatsappTaxLabel($tpp);
                $tpp->wa_message = $this->buildWhatsappMessage($tpp);
                $tpp->wa_link = $this->buildWhatsappLink($tpp);
                $tpp->periode_label = ($bulanNama[(int) $tpp->bulan] ?? $tpp->bulan) . '/' . $tpp->tahun;
                $tpp->beban_jml = $breakdown['beban_jml'] ?? 0;
                $tpp->pres_jml = $breakdown['pres_jml'] ?? 0;
                $tpp->kond_jml = $breakdown['kond_jml'] ?? 0;
                $tpp->lang_jml = $breakdown['lang_jml'] ?? 0;
                return $tpp;
            });

        $bulan = $request->bulan ? (int) $request->bulan : null;
        $tahun = $request->tahun ? (int) $request->tahun : null;
        $namaFile = 'Laporan_TPP_WA' . ($bulan && $tahun ? "_{$bulan}_{$tahun}" : '') . '.xlsx';

        return Excel::download(new TppWhatsappExport($rows), $namaFile);
    }

    public function editMassal(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat mengubah TPP secara langsung.');
        $this->abortIfPeriodNotEditableByRequest($request, (int) $request->bulan, (int) $request->tahun);

        $tpps = $this->tppUnitScope(Tpp::with('pegawai'), $request)
            ->where('bulan', (int) $request->bulan)
            ->where('tahun', (int) $request->tahun)
            ->get();

        return view('tpp.edit-massal', compact('tpps', 'request'));
    }

    public function updateMassal(Request $request)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat mengubah TPP secara langsung.');

        $validated = $request->validate([
            'tpp' => 'required|array|min:1',
            'tpp.*.produktivitas' => 'required|numeric|min:0|max:100',
            'tpp.*.kehadiran'     => 'required|numeric|min:0|max:100',
            'tpp.*.perilaku'      => 'required|numeric|min:0|max:100',
            'tpp.*.iuran_wajib'   => 'required|numeric|min:0',
            'tpp.*.bpjs_kesehatan_pemberi_kerja' => 'required|numeric|min:0',
            'tpp.*.tpp_tempat_bertugas' => 'required|numeric|min:0',
            'tpp.*.tunjangan_pph' => 'required|numeric|min:0',
            'tpp.*.iuran_jkk' => 'required|numeric|min:0',
            'tpp.*.iuran_jkm' => 'required|numeric|min:0',
            'tpp.*.iuran_tapera' => 'required|numeric|min:0',
            'tpp.*.iuran_pensiun' => 'required|numeric|min:0',
            'tpp.*.tunjangan_jht' => 'required|numeric|min:0',
            'tpp.*.bulog' => 'required|numeric|min:0',
            'tpp.*.tambahan_tpp'  => 'required|numeric|min:0',
            'tpp.*.potongan_tpp'  => 'required|numeric|min:0|max:100',
            'tpp.*.hitung_pajak'  => 'nullable|boolean',
        ]);

        $tppIds = $this->validatedRowIds($validated['tpp'], 'tpp');
        $tpps = $this->tppUnitScope(Tpp::with('pegawai.kelasJabatan'), $request)
            ->whereKey($tppIds)
            ->get()
            ->keyBy('id');

        abort_unless(
            $tpps->count() === count($tppIds),
            403,
            'Terdapat data TPP yang tidak termasuk dalam unit kerja Anda.'
        );

        $updatedCount = DB::transaction(function () use ($validated, $tppIds, $tpps) {
            foreach ($tppIds as $id) {
                $row = $validated['tpp'][$id];
                $tpp = $tpps[$id];

                $this->abortIfTppNotEditable($tpp);

                $hitungPajak = (bool) ((int) ($row['hitung_pajak'] ?? 0));

                $snapshot = $this->buildPegawaiSnapshot($tpp->pegawai);
                $hasil = $this->calculator->calculateFromSnapshot(
                    $snapshot,
                    (float) $row['produktivitas'],
                    (float) $row['kehadiran'],
                    (float) $row['perilaku'],
                    (float) $row['iuran_wajib'],
                    (float) ($row['tambahan_tpp'] ?? 0),
                    (float) ($row['potongan_tpp'] ?? 0),
                    $hitungPajak
                );

                $tpp->update(array_merge($hasil, [
                    'hitung_pajak' => $hitungPajak,
                    'bpjs_kesehatan_pemberi_kerja' => (float) ($row['bpjs_kesehatan_pemberi_kerja'] ?? 0),
                    'tpp_tempat_bertugas' => (float) ($row['tpp_tempat_bertugas'] ?? 0),
                    'tunjangan_pph' => (float) ($row['tunjangan_pph'] ?? 0),
                    'iuran_jkk' => (float) ($row['iuran_jkk'] ?? 0),
                    'iuran_jkm' => (float) ($row['iuran_jkm'] ?? 0),
                    'iuran_tapera' => (float) ($row['iuran_tapera'] ?? 0),
                    'iuran_pensiun' => (float) ($row['iuran_pensiun'] ?? 0),
                    'tunjangan_jht' => (float) ($row['tunjangan_jht'] ?? 0),
                    'bulog' => (float) ($row['bulog'] ?? 0),
                    'potongan_iwp' => (float) ($row['iuran_wajib'] ?? 0),
                    'pegawai_snapshot' => $snapshot,
                ]));
            }

            return count($tppIds);
        });

        return redirect()->route('tpp.index')->with('success', "Update massal berhasil untuk {$updatedCount} data.");
    }


    public function submitPeriod(Request $request)
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'operator'], true), 403, 'Akses ditolak');

        $data = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $unitKerjaId = (int) $request->user()->unit_kerja_id;
        $bulan = (int) $data['bulan'];
        $tahun = (int) $data['tahun'];
        $approval = $this->getOrInitializeApproval($unitKerjaId, $bulan, $tahun);

        if ($approval->isLocked()) {
            return back()->with('error', 'Periode TPP ini sudah dikunci oleh super admin.');
        }

        $hasData = $this->tppUnitScope(Tpp::query(), $request)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->exists();

        if (!$hasData) {
            return back()->with('error', 'Tidak ada data TPP untuk dikirim pada periode yang dipilih.');
        }

        $approval->fill([
            'status' => TppApproval::STATUS_SUBMITTED,
            'submitted_by' => $request->user()->id,
            'submitted_at' => now(),
        ]);
        $approval->appendHistory('Periode dikirim untuk validasi', $request->user()?->name);
        $approval->save();

        return back()->with('success', 'TPP periode ini berhasil dikirim untuk validasi super admin.');
    }

    public function lockPeriod(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses ditolak');

        $data = $request->validate([
            'unit_kerja_id' => 'required|integer|exists:unit_kerjas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $approval = $this->getOrInitializeApproval((int) $data['unit_kerja_id'], (int) $data['bulan'], (int) $data['tahun']);
        if (!$approval->isSubmitted()) {
            return back()->with('error', 'TPP hanya dapat dikunci setelah dikirim oleh admin/operator unit kerja.');
        }

        $hasData = Tpp::query()
            ->where('unit_kerja_id', (int) $data['unit_kerja_id'])
            ->where('bulan', (int) $data['bulan'])
            ->where('tahun', (int) $data['tahun'])
            ->exists();

        if (!$hasData) {
            return back()->with('error', 'Periode ini tidak bisa dikunci karena tidak memiliki data TPP.');
        }

        $approval->fill([
            'status' => TppApproval::STATUS_LOCKED,
            'locked_by' => $request->user()->id,
            'locked_at' => now(),
        ]);
        $approval->appendHistory('Periode divalidasi dan dikunci', $request->user()?->name);
        $approval->save();

        return back()->with('success', 'TPP periode ini berhasil divalidasi dan dikunci.');
    }

    public function unlockPeriod(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Akses ditolak');

        $data = $request->validate([
            'unit_kerja_id' => 'required|integer|exists:unit_kerjas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $approval = $this->getOrInitializeApproval((int) $data['unit_kerja_id'], (int) $data['bulan'], (int) $data['tahun']);
        if ($approval->isDraft()) {
            return back()->with('error', 'Periode ini sudah berada pada status draft.');
        }

        $approval->fill([
            'status' => TppApproval::STATUS_DRAFT,
            'unlocked_by' => $request->user()->id,
            'unlocked_at' => now(),
            'locked_by' => null,
            'locked_at' => null,
        ]);
        $approval->appendHistory('Kunci periode dibuka', $request->user()?->name);
        $approval->save();

        return back()->with('success', 'Kunci TPP berhasil dibuka. Admin/operator unit kerja kini bisa mengedit kembali.');
    }

    private function validatedRowIds(array $rows, string $field): array
    {
        $ids = [];

        foreach (array_keys($rows) as $key) {
            $keyString = (string) $key;

            if (!preg_match('/^[1-9]\d*$/', $keyString) || (string) ((int) $keyString) !== $keyString) {
                throw ValidationException::withMessages([
                    $field => 'Identitas baris data tidak valid.',
                ]);
            }

            $ids[] = (int) $keyString;
        }

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                $field => 'Identitas baris data tidak boleh berulang.',
            ]);
        }

        return $ids;
    }

    private function assertMatchingRowIds(array $validated, array $expectedIds, array $fields): void
    {
        sort($expectedIds);

        foreach ($fields as $field) {
            $actualIds = $this->validatedRowIds($validated[$field], $field);
            sort($actualIds);

            if ($actualIds !== $expectedIds) {
                throw ValidationException::withMessages([
                    $field => 'Setiap pegawai harus memiliki data yang lengkap pada semua kolom TPP.',
                ]);
            }
        }
    }

    private function getOrInitializeApproval(?int $unitKerjaId, ?int $bulan, ?int $tahun): ?TppApproval
    {
        if (!$unitKerjaId || !$bulan || !$tahun) {
            return null;
        }

        return TppApproval::firstOrNew([
            'unit_kerja_id' => (int) $unitKerjaId,
            'bulan' => (int) $bulan,
            'tahun' => (int) $tahun,
        ], [
            'status' => TppApproval::STATUS_DRAFT,
        ]);
    }
    private function abortIfTppNotEditable(Tpp $tpp): void
    {
        $unitKerjaId = (int) ($tpp->pegawai?->unit_kerja_id ?? $tpp->unit_kerja_id);
        $this->abortIfPeriodNotEditableByUnit($unitKerjaId, (int) $tpp->bulan, (int) $tpp->tahun);
    }

    private function abortIfPeriodNotEditableByRequest(Request $request, int $bulan, int $tahun, ?int $forcedUnitKerjaId = null): void
    {
        $unitKerjaId = $forcedUnitKerjaId ?: (int) ($request->user()?->unit_kerja_id);
        $this->abortIfPeriodNotEditableByUnit($unitKerjaId, $bulan, $tahun);
    }

    private function abortIfPeriodNotEditableByUnit(?int $unitKerjaId, int $bulan, int $tahun): void
    {
        $approval = $this->getOrInitializeApproval($unitKerjaId, $bulan, $tahun);
        if (!$approval) {
            return;
        }

        if (!$approval->canBeEdited()) {
            abort(403, 'TPP periode ini sedang menunggu validasi atau sudah dikunci.');
        }
    }

    private function formatWhatsappNumber(?string $phone): ?string
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

    private function buildWhatsappMessage(Tpp $tpp): string
    {
        $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        $periode = ($bulanNama[(int) $tpp->bulan] ?? $tpp->bulan) . '/' . $tpp->tahun;
        $rupiah = fn ($angka) => 'Rp. ' . number_format((float) $angka, 0, ',', '.');
        $persen = fn ($angka) => rtrim(rtrim(number_format((float) $angka, 2, '.', ''), '0'), '.') . '%';

        $sapaan = $this->whatsappGreeting();
        $namaPegawai = trim((string) $tpp->referensi_nama);
        $nip = trim((string) $tpp->referensi_nip);
        $pajakLabel = $this->whatsappTaxLabel($tpp);
        $breakdown = $this->calculateRekapBreakdowns($tpp);

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

    private function whatsappGreeting(): string
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

    private function whatsappTaxLabel(Tpp $tpp): string
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

    private function buildWhatsappLink(Tpp $tpp): ?string
    {
        $phone = $this->formatWhatsappNumber($tpp->referensi_no_hp);

        if (!$phone) {
            return null;
        }

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->buildWhatsappMessage($tpp));
    }

    private function baseFilteredQuery(Request $request)
    {
        $availableUnitKerjas = $request->user()?->isSuperAdmin()
            ? UnitKerja::query()->orderBy('nama_unit')->get()
            : collect();
        $selectedUnitKerjaId = $this->resolveSelectedUnitKerjaId($request, $availableUnitKerjas);

        $q = $this->tppUnitScope(Tpp::query(), $request, $selectedUnitKerjaId);
        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $user = $request->user();

        if ($user?->role === 'viewer') {
            if (!$user->pegawai_id) {
                return $q->whereRaw('1 = 0');
            }

            $q->where('pegawai_id', $user->pegawai_id);

            if ($request->filled('bulan')) {
                $q->where('bulan', (int) $request->bulan);
            }

            if ($request->filled('tahun')) {
                $q->where('tahun', (int) $request->tahun);
            }

            return $q;
        }

        $bulan = $request->filled('bulan') ? (int) $request->bulan : (int) $defaultPeriod->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) $defaultPeriod->year;

        $q->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($request->filled('search')) {
            $keyword = trim((string) $request->search);

            $q->where(function ($inner) use ($keyword) {
                $inner->whereHas('pegawai', function ($pegawaiQuery) use ($keyword) {
                    $pegawaiQuery->where('nama', 'like', '%' . $keyword . '%')
                        ->orWhere('nip', 'like', '%' . $keyword . '%');
                })->orWhere('pegawai_snapshot->nama', 'like', '%' . $keyword . '%')
                  ->orWhere('pegawai_snapshot->nip', 'like', '%' . $keyword . '%');
            });
        }

        return $q;
    }

    private function buildPegawaiSnapshot(?Pegawai $pegawai): array
    {
        return PegawaiSnapshotFactory::fromPegawai($pegawai);
    }

    public function edit(Request $request, Tpp $tpp)
    {
        $this->authorizeTpp($request, $tpp);
        $this->abortIfTppNotEditable($tpp);
        $tpp->load('pegawai.kelasJabatan');
        return view('tpp.edit', compact('tpp'));
    }

    public function update(Request $request, Tpp $tpp)
    {
        abort_if($request->user()?->isSuperAdmin(), 403, 'Super admin tidak dapat mengubah TPP secara langsung.');
        $this->authorizeTpp($request, $tpp);
        $this->abortIfTppNotEditable($tpp);
        $validated = $request->validate([
            'produktivitas' => 'required|numeric|min:0|max:100',
            'kehadiran' => 'required|numeric|min:0|max:100',
            'perilaku' => 'required|numeric|min:0|max:100',
            'bpjs_kesehatan' => 'required|numeric|min:0',
            'bpjs_kesehatan_pemberi_kerja' => 'required|numeric|min:0',
            'tpp_tempat_bertugas' => 'required|numeric|min:0',
            'tunjangan_pph' => 'required|numeric|min:0',
            'iuran_jkk' => 'required|numeric|min:0',
            'iuran_jkm' => 'required|numeric|min:0',
            'iuran_tapera' => 'required|numeric|min:0',
            'iuran_pensiun' => 'required|numeric|min:0',
            'tunjangan_jht' => 'required|numeric|min:0',
            'bulog' => 'required|numeric|min:0',
            'tambahan_tpp' => 'required|numeric|min:0',
            'potongan_tpp' => 'required|numeric|min:0|max:100',
            'hitung_pajak' => 'nullable|boolean',
        ]);

        $tpp->load('pegawai.kelasJabatan');

        $prod = (float) $validated['produktivitas'];
        $keh = (float) $validated['kehadiran'];
        $per = (float) $validated['perilaku'];
        $bpjs = (float) $validated['bpjs_kesehatan'];
        $bpjsPemberiKerja = (float) $validated['bpjs_kesehatan_pemberi_kerja'];
        $tppTempatBertugas = (float) $validated['tpp_tempat_bertugas'];
        $tunjanganPph = (float) $validated['tunjangan_pph'];
        $iuranJkk = (float) $validated['iuran_jkk'];
        $iuranJkm = (float) $validated['iuran_jkm'];
        $iuranTapera = (float) $validated['iuran_tapera'];
        $iuranPensiun = (float) $validated['iuran_pensiun'];
        $tunjanganJht = (float) $validated['tunjangan_jht'];
        $bulog = (float) $validated['bulog'];
        $tambahanTpp = (float) $validated['tambahan_tpp'];
        $potonganTpp = (float) $validated['potongan_tpp'];
        $hitungPajak = (bool) ((int) ($validated['hitung_pajak'] ?? 0));

        $snapshot = $this->buildPegawaiSnapshot($tpp->pegawai);
        $hasil = $this->calculator->calculateFromSnapshot($snapshot, $prod, $keh, $per, $bpjs, $tambahanTpp, $potonganTpp, $hitungPajak);

        $tpp->update([
            'produktivitas'  => $prod,
            'kehadiran'      => $keh,
            'perilaku'       => $per,
            'iuran_wajib'    => $bpjs,
            'bpjs_kesehatan_pemberi_kerja' => $bpjsPemberiKerja,
            'tpp_tempat_bertugas' => $tppTempatBertugas,
            'tunjangan_pph' => $tunjanganPph,
            'iuran_jkk' => $iuranJkk,
            'iuran_jkm' => $iuranJkm,
            'iuran_tapera' => $iuranTapera,
            'iuran_pensiun' => $iuranPensiun,
            'tunjangan_jht' => $tunjanganJht,
            'bulog' => $bulog,
            'potongan_iwp' => $bpjs,
            'tambahan_tpp'   => $tambahanTpp,
            'potongan_tpp'   => $potonganTpp,
            'hitung_pajak'   => $hitungPajak,
            'tpp_kotor'      => (float) ($hasil['tpp_kotor'] ?? 0),
            'pajak'          => (float) ($hasil['pajak'] ?? 0),
            'zakat'          => isset($hasil['zakat']) ? (float) $hasil['zakat'] : null,
            'total_diterima' => (float) ($hasil['total_diterima'] ?? 0),
            'pegawai_snapshot' => $snapshot,
        ]);

        return redirect()->route('tpp.index')
            ->with('success', 'Data TPP berhasil diperbarui.');
    }

    public function rekap(Request $request)
    {
        $bulan = (int) ($request->get('bulan', date('n')));
        $tahun = (int) ($request->get('tahun', date('Y')));
        $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        $availableUnitKerjas = $request->user()->isSuperAdmin()
            ? UnitKerja::query()->orderBy('nama_unit')->get()
            : collect();
        $selectedUnitKerjaId = $this->resolveSelectedUnitKerjaId($request, $availableUnitKerjas);
        $activeUnitKerja = $request->user()->isSuperAdmin()
            ? ($selectedUnitKerjaId ? $availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId) : null)
            : $request->user()->unitKerja;

        $rows = $this->tppUnitScope(Tpp::with(['pegawai.kelasJabatan']), $request, $selectedUnitKerjaId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderByRaw("COALESCE((SELECT nama FROM pegawais WHERE pegawais.id = tpps.pegawai_id), JSON_UNQUOTE(JSON_EXTRACT(pegawai_snapshot, '$.nama'))) asc")
            ->get();

        $totals = [
            'beban_pk' => 0, 'beban_dk' => 0, 'beban_pr' => 0, 'beban_jml' => 0,
            'pres_pk' => 0, 'pres_dk' => 0, 'pres_pr' => 0, 'pres_jml' => 0,
            'kond_pk' => 0, 'kond_dk' => 0, 'kond_pr' => 0, 'kond_jml' => 0,
            'lang_pk' => 0, 'lang_dk' => 0, 'lang_pr' => 0, 'lang_jml' => 0,
            'jumlah_besaran' => 0, 'tpp_kotor' => 0, 'bpjs1' => 0, 'bpjs4' => 0,
            'setelah_bpjs' => 0, 'pajak' => 0, 'setelah_pajak' => 0, 'zakat' => 0, 'diterima' => 0,
        ];

        foreach ($rows as $tpp) {
            $calc = $this->calculateRekapBreakdowns($tpp);
            foreach ($totals as $key => $value) {
                $totals[$key] += $calc[$key] ?? 0;
            }
        }

        return view('tpp.rekap', compact('rows', 'bulan', 'tahun', 'bulanNama', 'totals', 'availableUnitKerjas', 'selectedUnitKerjaId', 'activeUnitKerja'));
    }

    public function exportRekapExcel(Request $request)
    {
        $bulan = $request->bulan ? (int) $request->bulan : (int) now()->month;
        $tahun = $request->tahun ? (int) $request->tahun : (int) now()->year;

        return Excel::download(new TppRekapExport($bulan, $tahun, $request->user(), $this->resolveSelectedUnitKerjaId($request)), 'Rekap_TPP_' . $bulan . '_' . $tahun . '.xlsx');
    }

    public function rekapSipd(Request $request)
    {
        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $bulan = $request->filled('bulan') ? (int) $request->bulan : (int) $defaultPeriod->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) $defaultPeriod->year;
        $availableUnitKerjas = $request->user()->isSuperAdmin()
            ? UnitKerja::query()->orderBy('nama_unit')->get()
            : collect();
        $selectedUnitKerjaId = $this->resolveSelectedUnitKerjaId($request, $availableUnitKerjas);
        $activeUnitKerja = $request->user()->isSuperAdmin()
            ? ($selectedUnitKerjaId ? $availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId) : null)
            : $request->user()->unitKerja;

        $rows = $this->tppUnitScope(Tpp::with(['pegawai.kelasJabatan']), $request, $selectedUnitKerjaId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderByRaw("COALESCE((SELECT nama FROM pegawais WHERE pegawais.id = tpps.pegawai_id), JSON_UNQUOTE(JSON_EXTRACT(pegawai_snapshot, '$.nama'))) asc")
            ->get()
            ->map(fn ($tpp, $index) => array_merge(['no' => $index + 1], SipdRekapBuilder::rowFromTpp($tpp)))
            ->values()
            ->all();

        $totals = SipdRekapBuilder::totals($rows);

        return view('tpp.rekap_sipd', compact('rows', 'bulan', 'tahun', 'totals', 'availableUnitKerjas', 'selectedUnitKerjaId', 'activeUnitKerja'));
    }


    private function calculateRekapBreakdowns(Tpp $tpp): array
    {
        $prod = (float) $tpp->produktivitas;
        $keh = (float) $tpp->kehadiran;
        $per = (float) $tpp->perilaku;

        $potonganInput = max(0, min(100, (float) ($tpp->potongan_tpp ?? 0)));
        $faktorEfektif = max(0, 100 - $potonganInput) / 100;
        $bebanDasar = (float) $tpp->referensi_beban_kerja + max(0, (float) ($tpp->tambahan_tpp ?? 0));
        $prestasiDasar = (float) $tpp->referensi_prestasi_kerja;
        $kondisiDasar = (float) $tpp->referensi_kondisi_kerja;
        $kelangkaanDasar = (float) $tpp->referensi_kelangkaan_profesi;

        $break = function ($x) use ($prod, $keh, $per, $faktorEfektif) {
            $x = (float) $x;
            if ($x > 0) {
                $x *= $faktorEfektif;
            }
            $basePK = 0.40 * $x;
            $baseDK = 0.18 * $x;
            $basePR = 0.42 * $x;
            $valPK = (float) floor(($prod / 100) * $basePK);
            $valDK = (float) floor(($keh / 100) * $baseDK);
            $valPR = (float) floor(($per / 100) * $basePR);

            return ['pk' => $valPK, 'dk' => $valDK, 'pr' => $valPR, 'jml' => $valPK + $valDK + $valPR];
        };

        $beban = $break($bebanDasar);
        $pres = $break($prestasiDasar);
        $kond = $break($kondisiDasar);
        $lang = $break($kelangkaanDasar);

        $jumlahBesaran = 0;
        foreach ([$bebanDasar, $prestasiDasar, $kondisiDasar, $kelangkaanDasar] as $komponenDasar) {
            if ((float) $komponenDasar > 0) {
                $jumlahBesaran += (float) $komponenDasar * $faktorEfektif;
            }
        }

        $tppSetelahBpjs = (float) $tpp->tpp_kotor - (float) $tpp->iuran_wajib;
        $tppSetelahPajak = $tppSetelahBpjs - (float) $tpp->pajak;

        return [
            'beban_pk' => $beban['pk'], 'beban_dk' => $beban['dk'], 'beban_pr' => $beban['pr'], 'beban_jml' => $beban['jml'],
            'pres_pk' => $pres['pk'], 'pres_dk' => $pres['dk'], 'pres_pr' => $pres['pr'], 'pres_jml' => $pres['jml'],
            'kond_pk' => $kond['pk'], 'kond_dk' => $kond['dk'], 'kond_pr' => $kond['pr'], 'kond_jml' => $kond['jml'],
            'lang_pk' => $lang['pk'], 'lang_dk' => $lang['dk'], 'lang_pr' => $lang['pr'], 'lang_jml' => $lang['jml'],
            'jumlah_besaran' => $jumlahBesaran,
            'tpp_kotor' => (float) $tpp->tpp_kotor,
            'bpjs1' => (float) $tpp->iuran_wajib,
            'bpjs4' => (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),
            'setelah_bpjs' => $tppSetelahBpjs,
            'pajak' => (float) $tpp->pajak,
            'setelah_pajak' => $tppSetelahPajak,
            'zakat' => (float) $tpp->zakat,
            'diterima' => (float) $tpp->total_diterima,
        ];
    }

    public function exportRekapSipdExcel(Request $request)
    {
        $defaultPeriod = Carbon::now()->startOfMonth()->subMonth();
        $bulan = $request->filled('bulan') ? (int) $request->bulan : (int) $defaultPeriod->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) $defaultPeriod->year;

        return Excel::download(new TppSipdExport($bulan, $tahun, $request->user(), $this->resolveSelectedUnitKerjaId($request)), 'Rekap_SIPD_' . $bulan . '_' . $tahun . '.xlsx');
    }


    private function pegawaiScope(Request $request, ?int $selectedUnitKerjaId = null, ?int $bulan = null, ?int $tahun = null)
    {
        $query = Pegawai::query()->when(
            $bulan && $tahun,
            fn ($pegawaiQuery) => $pegawaiQuery->activeForPeriod($bulan, $tahun),
            fn ($pegawaiQuery) => $pegawaiQuery->where('status_pegawai', Pegawai::STATUS_AKTIF)
        );

        return $query->when(
            $request->user()?->isSuperAdmin(),
            fn ($pegawaiQuery) => $pegawaiQuery->when($selectedUnitKerjaId, fn ($superQuery) => $superQuery->where('unit_kerja_id', $selectedUnitKerjaId)),
            fn ($pegawaiQuery) => $pegawaiQuery->where('unit_kerja_id', $request->user()->unit_kerja_id)
        );
    }

    private function tppUnitScope($query, Request $request, ?int $selectedUnitKerjaId = null)
    {
        $actor = $request->user();
        $targetUnitKerjaId = $actor?->isSuperAdmin() ? $selectedUnitKerjaId : $actor?->unit_kerja_id;

        if (!$targetUnitKerjaId) {
            return $query;
        }

        return $query->where(function ($inner) use ($targetUnitKerjaId) {
            $inner->whereHas('pegawai', function ($pegawaiQuery) use ($targetUnitKerjaId) {
                $pegawaiQuery->where('unit_kerja_id', $targetUnitKerjaId);
            })->orWhere(function ($fallbackQuery) use ($targetUnitKerjaId) {
                $fallbackQuery->whereNull('pegawai_id')
                    ->where('unit_kerja_id', $targetUnitKerjaId);
            });
        });
    }

    private function resolveSelectedUnitKerjaId(Request $request, $availableUnitKerjas = null): ?int
    {
        if (!$request->user()?->isSuperAdmin()) {
            return null;
        }

        $selectedUnitKerjaId = $request->filled('unit_kerja_id') ? (int) $request->unit_kerja_id : null;
        if (!$selectedUnitKerjaId) {
            return null;
        }

        $availableUnitKerjas = $availableUnitKerjas ?: UnitKerja::query()->orderBy('nama_unit')->get();

        return $availableUnitKerjas->firstWhere('id', $selectedUnitKerjaId) ? $selectedUnitKerjaId : null;
    }

    private function authorizeTpp(Request $request, Tpp $tpp): void
    {
        $resolvedUnitKerjaId = $tpp->pegawai?->unit_kerja_id ?? $tpp->unit_kerja_id;
        abort_unless($request->user()->canAccessUnit($resolvedUnitKerjaId), 403, 'Akses ditolak');
    }

}
