@extends('layouts.main')

@section('title', 'Data Kelas Jabatan')

@section('content')
@php
    $totalKelas = $data->count();
    $totalBeban = $data->sum('beban_kerja');
    $totalPrestasi = $data->sum('prestasi_kerja');
    $totalKondisi = $data->sum('kondisi_kerja');
    $totalKelangkaan = $data->sum('kelangkaan_profesi');
    $totalTpp = $totalBeban + $totalPrestasi + $totalKondisi + $totalKelangkaan;
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Data Kelas Jabatan</h3>
        <div class="text-muted">Master kelas jabatan berlaku khusus untuk unit kerja <strong>{{ $activeUnitName }}</strong>.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('kelas-jabatan.import.form') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-arrow-up me-1"></i>Import Kelas Jabatan
        </a>
        <a href="{{ route('kelas-jabatan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Data
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Kelas Jabatan</div>
                <div class="fs-3 fw-bold">{{ number_format($totalKelas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Beban Kerja</div>
                <div class="fs-5 fw-bold text-primary">{{ number_format($totalBeban, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Prestasi Kerja</div>
                <div class="fs-5 fw-bold text-success">{{ number_format($totalPrestasi, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Kondisi Kerja</div>
                <div class="fs-5 fw-bold text-warning">{{ number_format($totalKondisi, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Kelangkaan Profesi</div>
                <div class="fs-5 fw-bold text-info">{{ number_format($totalKelangkaan, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm border-0 h-100 border border-danger-subtle">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Besaran TPP</div>
                <div class="fs-5 fw-bold text-danger">{{ number_format($totalTpp, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
        <div>
            <div class="fw-semibold">Daftar Kelas Jabatan</div>
            <div class="small text-muted">Tabel dibuat lebih bersih dengan penekanan pada komponen nilai TPP.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('kelas-jabatan.template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i>Template Import
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 110px;">No Kelas</th>
                        <th>Nama Kelas</th>
                        <th class="text-end">Beban</th>
                        <th class="text-end">Prestasi</th>
                        <th class="text-end">Kondisi</th>
                        <th class="text-end">Kelangkaan</th>
                        <th class="text-end">Total Besaran TPP</th>
                        <th class="text-center" style="width: 120px;">Dipakai</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        @php
                            $total = (float) $item->beban_kerja + (float) $item->prestasi_kerja + (float) $item->kondisi_kerja + (float) $item->kelangkaan_profesi;
                        @endphp
                        <tr>
                            <td><span class="badge rounded-pill text-bg-dark">Kelas {{ $item->nomor_kelas }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $item->nama_kelas }}</div>
                                <div class="small text-muted">Nomor kelas {{ $item->nomor_kelas }}</div>
                            </td>
                            <td class="text-end">{{ number_format($item->beban_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->prestasi_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kondisi_kerja, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kelangkaan_profesi, 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-primary">{{ number_format($total, 2, ',', '.') }}</td>
                            <td class="text-center"><span class="badge rounded-pill text-bg-light border text-dark">{{ number_format($item->pegawais_count ?? 0, 0, ',', '.') }} pegawai</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('kelas-jabatan.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('kelas-jabatan.destroy', $item->id) }}" method="POST" data-confirm data-confirm-title="Hapus kelas jabatan?" data-confirm-message="Kelas {{ $item->nomor_kelas }} — {{ $item->nama_kelas }} akan dihapus. Sistem akan menolak jika kelas masih digunakan pegawai." data-confirm-label="Hapus Kelas" data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Belum ada data kelas jabatan untuk unit kerja ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
