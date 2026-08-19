<?php

namespace App\Http\Controllers;

use App\Exports\KelasJabatanTemplateExport;
use App\Imports\KelasJabatanImport;
use App\Models\KelasJabatan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KelasJabatanController extends Controller
{
    public function index()
    {
        $data = KelasJabatan::orderBy('nomor_kelas')->paginate(20);
        return view('kelas_jabatan.index', compact('data'));
    }

    public function create()
    {
        return view('kelas_jabatan.create');
    }

    public function importForm()
    {
        return view('kelas_jabatan.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new KelasJabatanImport(), $request->file('file'));

        return redirect()->route('kelas-jabatan.index')
            ->with('success', 'Import kelas jabatan selesai. Baris dengan nomor_kelas yang sudah ada akan diperbarui.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new KelasJabatanTemplateExport(), 'template_import_kelas_jabatan.xlsx');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_kelas' => 'required|integer|min:1|max:16|unique:kelas_jabatans,nomor_kelas',
            'nama_kelas' => 'required|string|max:255',
            'beban_kerja' => 'required|numeric|min:0',
            'prestasi_kerja' => 'required|numeric|min:0',
            'kondisi_kerja' => 'required|numeric|min:0',
            'kelangkaan_profesi' => 'nullable|numeric|min:0',
        ]);

        KelasJabatan::create($data);

        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan berhasil ditambahkan.');
    }

    public function edit(KelasJabatan $kelas_jabatan)
    {
        return view('kelas_jabatan.edit', ['kelas' => $kelas_jabatan]);
    }

    public function update(Request $request, KelasJabatan $kelas_jabatan)
    {
        $data = $request->validate([
            'nomor_kelas' => 'required|integer|min:1|max:16|unique:kelas_jabatans,nomor_kelas,' . $kelas_jabatan->id,
            'nama_kelas' => 'required|string|max:255',
            'beban_kerja' => 'required|numeric|min:0',
            'prestasi_kerja' => 'required|numeric|min:0',
            'kondisi_kerja' => 'required|numeric|min:0',
            'kelangkaan_profesi' => 'nullable|numeric|min:0',
        ]);

        $kelas_jabatan->update($data);

        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan berhasil diperbarui.');
    }

    public function destroy(KelasJabatan $kelas_jabatan)
    {
        $kelas_jabatan->delete();
        return redirect()->route('kelas-jabatan.index')->with('success', 'Kelas jabatan berhasil dihapus.');
    }
}
