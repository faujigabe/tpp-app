<?php

namespace App\Http\Controllers;

use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\TppApproval;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        $search = trim((string) $request->get('q', ''));
        $fotoFilter = (string) $request->get('foto', '');
        $npwpFilter = (string) $request->get('npwp', '');
        $selectedUnitKerjaId = $actor->isSuperAdmin()
            ? ($request->filled('unit_kerja_id') ? (int) $request->integer('unit_kerja_id') : null)
            : (int) $actor->unit_kerja_id;
        $statusFilter = (string) $request->get('status', '');

        $baseQuery = $this->pegawaiScope($request, $selectedUnitKerjaId)->with('unitKerja');

        $pegawais = (clone $baseQuery)
            ->with('kelasJabatan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($fotoFilter === 'sudah', fn ($query) => $query->whereNotNull('foto_profil')->where('foto_profil', '!=', ''))
            ->when($fotoFilter === 'belum', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('foto_profil')->orWhere('foto_profil', '');
            }))
            ->when($npwpFilter === 'sudah', fn ($query) => $query->whereNotNull('no_npwp')->where('no_npwp', '!=', ''))
            ->when($npwpFilter === 'belum', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('no_npwp')->orWhere('no_npwp', '');
            }))
            ->when($statusFilter === Pegawai::STATUS_AKTIF, fn ($query) => $query->where('status_pegawai', Pegawai::STATUS_AKTIF))
            ->when($statusFilter === 'nonaktif', fn ($query) => $query->whereIn('status_pegawai', Pegawai::inactiveStatuses()))
            ->when(in_array($statusFilter, array_keys(Pegawai::availableStatuses()), true) && ! in_array($statusFilter, [Pegawai::STATUS_AKTIF], true), fn ($query) => $query->where('status_pegawai', $statusFilter))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $totalPegawai = (clone $baseQuery)->count();
        $tanpaKelas = (clone $baseQuery)->whereNull('kelas_jabatan_id')->count();
        $totalSudahFoto = (clone $baseQuery)->whereNotNull('foto_profil')->where('foto_profil', '!=', '')->count();
        $totalSudahNpwp = (clone $baseQuery)->whereNotNull('no_npwp')->where('no_npwp', '!=', '')->count();
        $totalAktif = (clone $baseQuery)->where('status_pegawai', Pegawai::STATUS_AKTIF)->count();
        $totalNonaktif = (clone $baseQuery)->whereIn('status_pegawai', Pegawai::inactiveStatuses())->count();
        $unitKerjas = $this->availableUnitKerjas($actor);
        $statusOptions = Pegawai::availableStatuses();

        return view('pegawai.index', compact(
            'pegawais',
            'search',
            'fotoFilter',
            'npwpFilter',
            'totalPegawai',
            'tanpaKelas',
            'totalSudahFoto',
            'totalSudahNpwp',
            'unitKerjas',
            'selectedUnitKerjaId',
            'statusFilter',
            'totalAktif',
            'totalNonaktif',
            'statusOptions'
        ));
    }

    public function create(Request $request)
    {
        $unitKerjas = $this->availableUnitKerjas($request->user());
        $selectedUnitKerjaId = (int) old('unit_kerja_id', $request->get('unit_kerja_id', $request->user()->unit_kerja_id));
        if (! $request->user()->isSuperAdmin()) {
            $selectedUnitKerjaId = (int) $request->user()->unit_kerja_id;
        }
        $kelas = $this->kelasJabatanOptionsForForm($request, $selectedUnitKerjaId);
        $namaJabatanOptions = $this->namaJabatanOptionsFromKelas($kelas);

        return view('pegawai.create', compact('kelas', 'namaJabatanOptions', 'unitKerjas', 'selectedUnitKerjaId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        if ($request->hasFile('foto_profil')) {
            $validated['foto_profil'] = $request->file('foto_profil')->store('foto-profil', 'public');
        }

        unset($validated['hapus_foto_profil']);
        $validated['unit_kerja_id'] = $this->resolveUnitKerjaId($request, $validated);
        $validated['status_pegawai'] = $validated['status_pegawai'] ?? Pegawai::STATUS_AKTIF;

        if (($validated['status_pegawai'] ?? Pegawai::STATUS_AKTIF) === Pegawai::STATUS_AKTIF) {
            $validated['nonaktif_sejak'] = null;
            $validated['catatan_status'] = null;
        } else {
            $validated['nonaktif_sejak'] = $validated['nonaktif_sejak'] ?? now()->toDateString();
        }

        Pegawai::create($validated);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan');
    }

    public function edit(Request $request, Pegawai $pegawai)
    {
        $this->authorizePegawai($request, $pegawai);
        $unitKerjas = $this->availableUnitKerjas($request->user());
        $selectedUnitKerjaId = (int) old('unit_kerja_id', $pegawai->unit_kerja_id);
        if (! $request->user()->isSuperAdmin()) {
            $selectedUnitKerjaId = (int) $request->user()->unit_kerja_id;
        }
        $kelas = $this->kelasJabatanOptionsForForm($request, $selectedUnitKerjaId, $pegawai->kelas_jabatan_id);
        $namaJabatanOptions = $this->namaJabatanOptionsFromKelas($kelas);
        $fotoPegawaiUrl = $pegawai->foto_profil ? asset('storage/' . $pegawai->foto_profil) : null;

        return view('pegawai.edit', compact('pegawai', 'kelas', 'namaJabatanOptions', 'unitKerjas', 'selectedUnitKerjaId', 'fotoPegawaiUrl'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $this->authorizePegawai($request, $pegawai);
        $validated = $this->validatedData($request, $pegawai->id);

        if (($validated['hapus_foto_profil'] ?? false) && $pegawai->foto_profil) {
            Storage::disk('public')->delete($pegawai->foto_profil);
            $validated['foto_profil'] = null;
        }

        if ($request->hasFile('foto_profil')) {
            if ($pegawai->foto_profil) {
                Storage::disk('public')->delete($pegawai->foto_profil);
            }
            $validated['foto_profil'] = $request->file('foto_profil')->store('foto-profil', 'public');
        }

        unset($validated['hapus_foto_profil']);
        $validated['unit_kerja_id'] = $this->resolveUnitKerjaId($request, $validated, $pegawai->unit_kerja_id);
        $validated['status_pegawai'] = $validated['status_pegawai'] ?? ($pegawai->status_pegawai ?: Pegawai::STATUS_AKTIF);

        if ($validated['status_pegawai'] === Pegawai::STATUS_AKTIF) {
            $validated['nonaktif_sejak'] = null;
            $validated['catatan_status'] = null;
        } else {
            $validated['nonaktif_sejak'] = $validated['nonaktif_sejak'] ?? now()->toDateString();
        }

        $pegawai->update($validated);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui');
    }

    public function importForm(Request $request)
    {
        $unitKerjas = $this->availableUnitKerjas($request->user());
        $selectedUnitKerjaId = old('unit_kerja_id', $request->user()->unit_kerja_id);

        return view('pegawai.import', compact('unitKerjas', 'selectedUnitKerjaId'));
    }

    public function importStore(Request $request)
    {
        $rules = ['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'];
        if ($request->user()->isSuperAdmin()) {
            $rules['unit_kerja_id'] = 'required|exists:unit_kerjas,id';
        }
        $request->validate($rules);

        $import = new PegawaiImport(
            $request->user()->unit_kerja_id,
            $request->user()->isSuperAdmin() ? (int) $request->integer('unit_kerja_id') : null
        );
        Excel::import($import, $request->file('file'));

        $failureCount = count($import->failures());
        $message = 'Import pegawai selesai. ' . $import->createdCount . ' data baru ditambahkan, ' . $import->updatedCount . ' data diperbarui.';
        if ($failureCount > 0) {
            $message .= ' ' . $failureCount . ' baris gagal, periksa format file dan master Kelas Jabatan.';
        }

        return redirect()->route('pegawai.index')->with('success', $message);
    }

    public function downloadTemplate()
    {
        return Excel::download(new PegawaiTemplateExport, 'template_import_pegawai.xlsx');
    }

    public function destroyMassal(Request $request)
    {
        $validated = $request->validate([
            'pegawai_ids' => 'required|array|min:1',
            'pegawai_ids.*' => 'integer|exists:pegawais,id',
        ]);

        $query = $this->pegawaiScope($request)->whereIn('id', $validated['pegawai_ids']);
        $pegawais = $query->get();

        $blockedNames = [];
        foreach ($pegawais as $pegawai) {
            if ($this->hasEditableTppHistory($pegawai)) {
                $blockedNames[] = $pegawai->nama;
            }
        }

        if (! empty($blockedNames)) {
            return redirect()->route('pegawai.index')->with('error', 'Sebagian data tidak dapat dihapus karena masih memiliki riwayat TPP draft/submitted. Kunci dulu periode lama atau hapus data TPP yang belum final. Pegawai: ' . implode(', ', $blockedNames));
        }
        foreach ($pegawais as $pegawai) {
            if ($pegawai->foto_profil) {
                Storage::disk('public')->delete($pegawai->foto_profil);
            }
        }
        $deleted = $query->delete();

        return redirect()->route('pegawai.index')->with('success', $deleted . ' data pegawai berhasil dihapus.');
    }

    public function destroy(Request $request, Pegawai $pegawai)
    {
        $this->authorizePegawai($request, $pegawai);

        if ($this->hasEditableTppHistory($pegawai)) {
            return redirect()->route('pegawai.index')->with('error', 'Pegawai tidak dapat dihapus karena masih memiliki riwayat TPP draft/submitted. Finalkan atau hapus dulu periode yang belum terkunci.');
        }

        if ($pegawai->foto_profil) {
            Storage::disk('public')->delete($pegawai->foto_profil);
        }
        $pegawai->delete();

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus');
    }

    public function updateStatus(Request $request, Pegawai $pegawai)
    {
        $this->authorizePegawai($request, $pegawai);

        $validated = $request->validate([
            'status_pegawai' => ['required', Rule::in(array_keys(Pegawai::availableStatuses()))],
            'nonaktif_sejak' => 'nullable|date',
            'catatan_status' => 'nullable|string|max:255',
        ]);

        $payload = [
            'status_pegawai' => $validated['status_pegawai'],
            'nonaktif_sejak' => $validated['status_pegawai'] === Pegawai::STATUS_AKTIF ? null : ($validated['nonaktif_sejak'] ?? now()->toDateString()),
            'catatan_status' => $validated['status_pegawai'] === Pegawai::STATUS_AKTIF ? null : ($validated['catatan_status'] ?? ('Pegawai berstatus ' . strtolower(Pegawai::availableStatuses()[$validated['status_pegawai']] ?? $validated['status_pegawai']) . '.')),
        ];

        $pegawai->update($payload);

        $message = $validated['status_pegawai'] === Pegawai::STATUS_AKTIF
            ? 'Pegawai berhasil diaktifkan kembali dan akan muncul pada input TPP berikutnya.'
            : 'Status pegawai berhasil diperbarui menjadi ' . (Pegawai::availableStatuses()[$validated['status_pegawai']] ?? ucfirst($validated['status_pegawai'])) . '. Riwayat TPP lama tetap aman, tetapi pegawai tidak akan muncul pada input TPP baru.';

        return redirect()->route('pegawai.index')->with('success', $message);
    }

    private function hasEditableTppHistory(Pegawai $pegawai): bool
    {
        return DB::table('tpps')
            ->leftJoin('tpp_approvals', function ($join) {
                $join->on('tpp_approvals.unit_kerja_id', '=', 'tpps.unit_kerja_id')
                    ->on('tpp_approvals.bulan', '=', 'tpps.bulan')
                    ->on('tpp_approvals.tahun', '=', 'tpps.tahun');
            })
            ->where('tpps.pegawai_id', $pegawai->id)
            ->where(function ($query) {
                $query->whereNull('tpp_approvals.status')
                    ->orWhere('tpp_approvals.status', '!=', TppApproval::STATUS_LOCKED);
            })
            ->exists();
    }

    private function validatedData(Request $request, ?int $pegawaiId = null): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawais,nip,' . ($pegawaiId ?? 'NULL') . ',id',
            'nik' => 'nullable|string|max:50|unique:pegawais,nik,' . ($pegawaiId ?? 'NULL') . ',id',
            'no_npwp' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:100',
            'no_hp' => 'required|string|max:20',
            'golongan' => 'required|in:II/A,II/B,II/C,II/D,III/A,III/B,III/C,III/D,IV/A,IV/B,IV/C,IV/D,IV/E',
            'jabatan' => 'required|string',
            'nama_jabatan' => 'nullable|string|max:255',
            'tipe_jabatan' => 'nullable|integer|min:0',
            'eselon' => 'nullable|string|max:100',
            'status_asn' => 'nullable|integer|min:0',
            'masa_kerja_golongan' => 'nullable|integer|min:0',
            'alamat' => 'nullable|string',
            'kode_bank' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:100',
            'agama' => 'required|string|max:100',
            'status_pegawai' => ['nullable', Rule::in(array_keys(Pegawai::availableStatuses()))],
            'nonaktif_sejak' => 'nullable|date',
            'catatan_status' => 'nullable|string|max:255',
            'kelas_jabatan_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('kelas_jabatans', 'id')->where(function ($query) use ($request) {
                    $unitKerjaId = $request->user()->isSuperAdmin()
                        ? (int) $request->input('unit_kerja_id')
                        : (int) $request->user()->unit_kerja_id;

                    $query->where('unit_kerja_id', $unitKerjaId);
                }),
            ],
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hapus_foto_profil' => 'nullable|boolean',
        ];

        if ($request->user()->isSuperAdmin()) {
            $rules['unit_kerja_id'] = 'required|exists:unit_kerjas,id';
        }

        return $request->validate($rules);
    }


    private function kelasJabatanOptionsForForm(Request $request, ?int $targetUnitKerjaId, ?int $currentKelasId = null)
    {
        $query = KelasJabatan::query()
            ->where('unit_kerja_id', $targetUnitKerjaId);

        if ($targetUnitKerjaId && ! $request->user()->isSuperAdmin()) {
            $legacyUnitId = $this->legacyDefaultUnitId();

            if ($legacyUnitId && $legacyUnitId !== (int) $targetUnitKerjaId) {
                $query->where(function ($inner) use ($targetUnitKerjaId, $legacyUnitId, $currentKelasId) {
                    $inner->whereExists(function ($pegawaiQuery) use ($targetUnitKerjaId) {
                        $pegawaiQuery->selectRaw('1')
                            ->from('pegawais')
                            ->whereColumn('pegawais.kelas_jabatan_id', 'kelas_jabatans.id')
                            ->where('pegawais.unit_kerja_id', $targetUnitKerjaId);
                    })
                    ->orWhere('kelas_jabatans.id', $currentKelasId)
                    ->orWhereNotExists(function ($legacyQuery) use ($legacyUnitId) {
                        $legacyQuery->selectRaw('1')
                            ->from('kelas_jabatans as legacy_kj')
                            ->where('legacy_kj.unit_kerja_id', $legacyUnitId)
                            ->whereColumn('legacy_kj.nomor_kelas', 'kelas_jabatans.nomor_kelas')
                            ->whereColumn('legacy_kj.nama_kelas', 'kelas_jabatans.nama_kelas')
                            ->whereColumn('legacy_kj.beban_kerja', 'kelas_jabatans.beban_kerja')
                            ->whereColumn('legacy_kj.prestasi_kerja', 'kelas_jabatans.prestasi_kerja')
                            ->whereColumn('legacy_kj.kondisi_kerja', 'kelas_jabatans.kondisi_kerja')
                            ->whereColumn('legacy_kj.kelangkaan_profesi', 'kelas_jabatans.kelangkaan_profesi');
                    });
                });
            }
        }

        return $query
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();
    }

    private function legacyDefaultUnitId(): ?int
    {
        return UnitKerja::query()
            ->where('nama_unit', 'like', '%Biro Administrasi Pembangunan%')
            ->value('id') ?: UnitKerja::query()->min('id');
    }


    private function namaJabatanOptionsFromKelas($kelas)
    {
        return collect($kelas)
            ->map(function ($item) {
                $nama = trim((string) ($item->nama_kelas ?? ''));
                $nomorLabel = 'Kelas ' . ($item->nomor_kelas ?? '');

                return [
                    'value' => $nama,
                    'label' => $nama !== '' ? ($nomorLabel . ' - ' . $nama) : $nomorLabel,
                    'kelas_jabatan_id' => (int) $item->id,
                    'nomor_kelas_label' => $nomorLabel,
                ];
            })
            ->filter(fn ($item) => $item['value'] !== '')
            ->values()
            ->all();
    }

    private function pegawaiScope(Request $request, ?int $selectedUnitKerjaId = null)
    {
        $actor = $request->user();

        return Pegawai::query()
            ->when(!$actor->isSuperAdmin(), fn ($query) => $query->where('unit_kerja_id', $actor->unit_kerja_id))
            ->when($actor->isSuperAdmin() && $selectedUnitKerjaId, fn ($query) => $query->where('unit_kerja_id', $selectedUnitKerjaId));
    }

    private function availableUnitKerjas($actor)
    {
        return UnitKerja::query()
            ->when(!$actor->isSuperAdmin(), fn ($query) => $query->whereKey($actor->unit_kerja_id))
            ->orderBy('nama_unit')
            ->get();
    }

    private function resolveUnitKerjaId(Request $request, array $validated, ?int $fallback = null): int
    {
        if ($request->user()->isSuperAdmin()) {
            return (int) ($validated['unit_kerja_id'] ?? $fallback);
        }

        return (int) $request->user()->unit_kerja_id;
    }

    private function authorizePegawai(Request $request, Pegawai $pegawai): void
    {
        abort_unless($request->user()->canAccessUnit($pegawai->unit_kerja_id), 403, 'Akses ditolak');
    }
}
