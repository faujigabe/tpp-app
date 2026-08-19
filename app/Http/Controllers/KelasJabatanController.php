<?php

namespace App\Http\Controllers;

use App\Exports\KelasJabatanTemplateExport;
use App\Imports\KelasJabatanImport;
use App\Models\KelasJabatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class KelasJabatanController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        $data = $this->kelasQuery($actor)
            ->withCount('pegawais')
            ->orderBy('nomor_kelas')
            ->orderBy('nama_kelas')
            ->get();

        return view('kelas_jabatan.index', [
            'data' => $data,
            'activeUnitName' => $actor->unitKerja->nama_unit ?? '-',
        ]);
    }

    public function create(Request $request): View
    {
        return view('kelas_jabatan.create', [
            'activeUnitName' => $request->user()->unitKerja->nama_unit ?? '-',
        ]);
    }

    public function importForm(Request $request): View
    {
        return view('kelas_jabatan.import', [
            'activeUnitName' => $request->user()->unitKerja->nama_unit ?? '-',
        ]);
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new KelasJabatanImport((int) $request->user()->unit_kerja_id), $request->file('file'));

        return redirect()->route('kelas-jabatan.index')
            ->with('success', 'Import kelas jabatan unit berhasil selesai. Baris dengan nomor kelas yang sudah ada pada unit ini akan diperbarui.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new KelasJabatanTemplateExport, 'template_import_kelas_jabatan.xlsx');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['unit_kerja_id'] = (int) $request->user()->unit_kerja_id;

        KelasJabatan::create($validated);

        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan unit berhasil ditambahkan.');
    }

    public function edit(Request $request, KelasJabatan $kelas_jabatan): View
    {
        $this->authorizeKelasJabatan($request, $kelas_jabatan);

        return view('kelas_jabatan.edit', [
            'kelas' => $kelas_jabatan,
            'activeUnitName' => $request->user()->unitKerja->nama_unit ?? '-',
        ]);
    }

    public function update(Request $request, KelasJabatan $kelas_jabatan): RedirectResponse
    {
        $this->authorizeKelasJabatan($request, $kelas_jabatan);
        $validated = $this->validatedData($request, $kelas_jabatan->id);
        $validated['unit_kerja_id'] = (int) $request->user()->unit_kerja_id;

        $kelas_jabatan->update($validated);

        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan unit berhasil diperbarui.');
    }

    public function destroy(Request $request, KelasJabatan $kelas_jabatan): RedirectResponse
    {
        $this->authorizeKelasJabatan($request, $kelas_jabatan);
        $kelas_jabatan->delete();

        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan unit berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $unitKerjaId = (int) $request->user()->unit_kerja_id;

        return $request->validate([
            'nomor_kelas' => [
                'required',
                'integer',
                'min:1',
                'max:16',
            ],
            'nama_kelas' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('kelas_jabatans', 'nama_kelas')
                    ->where(fn ($query) => $query
                        ->where('unit_kerja_id', $unitKerjaId)
                        ->where('nomor_kelas', (int) $request->input('nomor_kelas'))
                    )
                    ->ignore($ignoreId),
            ],
            'beban_kerja' => 'required|numeric',
            'prestasi_kerja' => 'required|numeric',
            'kondisi_kerja' => 'required|numeric',
            'kelangkaan_profesi' => 'nullable|numeric',
        ]);
    }

    private function kelasQuery($actor)
    {
        return KelasJabatan::query()->where('unit_kerja_id', $actor->unit_kerja_id);
    }

    private function authorizeKelasJabatan(Request $request, KelasJabatan $kelasJabatan): void
    {
        abort_unless($request->user()->canAccessUnit($kelasJabatan->unit_kerja_id), 403, 'Akses ditolak');
    }
}
