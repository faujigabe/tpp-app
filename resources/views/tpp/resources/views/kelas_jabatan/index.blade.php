@extends('layouts.main')

@section('title', 'Kelas Jabatan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">Data Kelas Jabatan</h3>
        <div class="text-muted">Kelola master kelas jabatan, import data, dan perbarui komponen TPP.</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('kelas-jabatan.import.form') }}" class="btn btn-outline-success btn-icon">
            <i class="bi bi-upload"></i> Import
        </a>
        <a href="{{ route('kelas-jabatan.template') }}" class="btn btn-outline-secondary btn-icon">
            <i class="bi bi-download"></i> Template
        </a>
        <a href="{{ route('kelas-jabatan.create') }}" class="btn btn-primary btn-icon">
            <i class="bi bi-plus-circle"></i> Tambah Data
        </a>
    </div>
</div>

<div class="card shadow-soft">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-semibold">Daftar Kelas Jabatan</div>
        <span class="badge text-bg-light border">{{ number_format($data->total(), 0, ',', '.') }} data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No Kelas</th>
                        <th>Nama Kelas</th>
                        <th class="text-end">Beban Kerja</th>
                        <th class="text-end">Prestasi Kerja</th>
                        <th class="text-end">Kondisi Kerja</th>
                        <th class="text-end">Kelangkaan Profesi</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td><span class="badge rounded-pill text-bg-light border">{{ $item->nomor_kelas }}</span></td>
                            <td class="fw-semibold">{{ $item->nama_kelas }}</td>
                            <td class="text-end">{{ number_format($item->beban_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->prestasi_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kondisi_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kelangkaan_profesi ?? 0, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('kelas-jabatan.edit', $item->id) }}" class="btn btn-sm btn-warning btn-icon">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('kelas-jabatan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas jabatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">Belum ada data kelas jabatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">{{ $data->links() }}</div>
</div>
@endsection
