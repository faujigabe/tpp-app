<?php

namespace App\Http\Controllers;

use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Models\KelasJabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::with('kelasJabatan')
            ->orderBy('nama')
            ->paginate(25);

        return view('pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        $kelas = KelasJabatan::orderBy('nomor_kelas')->get();
        return view('pegawai.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawais,nip',
            'nomor_rekening' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'golongan' => 'required|in:II/A,II/B,II/C,II/D,III/A,III/B,III/C,III/D,IV/A,IV/B,IV/C,IV/D,IV/E',
            'jabatan' => 'required|string|max:255',
            'agama' => 'required|string|max:50',
            'kelas_jabatan_id' => 'required|exists:kelas_jabatans,id',
        ]);

        Pegawai::create($data);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai)
    {
        $kelas = KelasJabatan::orderBy('nomor_kelas')->get();
        return view('pegawai.edit', compact('pegawai', 'kelas'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawais,nip,' . $pegawai->id,
            'nomor_rekening' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'golongan' => 'required|in:II/A,II/B,II/C,II/D,III/A,III/B,III/C,III/D,IV/A,IV/B,IV/C,IV/D,IV/E',
            'jabatan' => 'required|string|max:255',
            'agama' => 'required|string|max:50',
            'kelas_jabatan_id' => 'required|exists:kelas_jabatans,id',
        ]);

        $pegawai->update($data);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function importForm()
    {
        return view('pegawai.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new PegawaiImport, $request->file('file'));

        return redirect()->route('pegawai.index')
            ->with('success', 'Import pegawai selesai. Jika ada baris yang gagal, periksa format file dan master Kelas Jabatan.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new PegawaiTemplateExport(), 'template_import_pegawai.xlsx');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();
        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function destroyMassal(Request $request)
    {
        $data = $request->validate([
            'pegawai_ids' => 'required|array|min:1',
            'pegawai_ids.*' => 'integer|exists:pegawais,id',
        ]);

        $deleted = Pegawai::whereIn('id', $data['pegawai_ids'])->delete();

        return redirect()->route('pegawai.index')
            ->with('success', "Hapus massal pegawai berhasil: {$deleted} data.");
    }
}
