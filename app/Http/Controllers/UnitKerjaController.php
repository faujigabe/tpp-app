<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $unitKerjas = UnitKerja::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nama_unit', 'like', "%{$search}%")
                        ->orWhere('kode_unit', 'like', "%{$search}%");
                });
            })
            ->withCount(['users', 'pegawais'])
            ->orderBy('nama_unit')
            ->paginate(10)
            ->withQueryString();

        return view('unit-kerja.index', compact('unitKerjas', 'search'));
    }

    public function create()
    {
        return view('unit-kerja.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'kode_unit' => 'nullable|string|max:50|unique:unit_kerjas,kode_unit',
        ]);

        UnitKerja::create($validated);

        return redirect()->route('unit-kerja.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(UnitKerja $unit_kerja)
    {
        return view('unit-kerja.edit', ['unitKerja' => $unit_kerja]);
    }

    public function update(Request $request, UnitKerja $unit_kerja)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'kode_unit' => ['nullable', 'string', 'max:50', Rule::unique('unit_kerjas', 'kode_unit')->ignore($unit_kerja->id)],
        ]);

        $unit_kerja->update($validated);

        return redirect()->route('unit-kerja.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(UnitKerja $unit_kerja)
    {
        if ($unit_kerja->users()->exists() || $unit_kerja->pegawais()->exists() || $unit_kerja->tpps()->exists()) {
            return back()->with('error', 'Unit kerja tidak dapat dihapus karena masih dipakai oleh data user, pegawai, atau TPP.');
        }

        $unit_kerja->delete();

        return redirect()->route('unit-kerja.index')->with('success', 'Unit kerja berhasil dihapus.');
    }
}
