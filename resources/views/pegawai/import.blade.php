@extends('layouts.main')

@section('title', 'Import Pegawai')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Import Pegawai dari Excel</h3>
        <p class="text-muted mb-0">Gunakan template terbaru agar kolom pegawai sesuai dengan field yang saat ini aktif di aplikasi.</p>
    </div>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-icon">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-soft border-0">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> File belum bisa diproses.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger shadow-soft border-0">
        <i class="bi bi-exclamation-octagon me-1"></i>{{ session('error') }}
    </div>
@endif

<div class="card shadow-soft border-0">
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <form action="{{ route('pegawai.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(auth()->user()->isSuperAdmin())
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit Kerja Tujuan</label>
                        <select name="unit_kerja_id" class="form-select @error('unit_kerja_id') is-invalid @enderror" required>
                            <option value="">Pilih unit kerja</option>
                            @foreach($unitKerjas as $unit)
                                <option value="{{ $unit->id }}" @selected((string) old('unit_kerja_id', $selectedUnitKerjaId) === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                        @error('unit_kerja_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit Kerja</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->unitKerja->nama_unit ?? '-' }}" disabled>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel (.xlsx/.xls) atau CSV</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary btn-icon"><i class="bi bi-upload"></i> Mulai Import</button>
                        <a href="{{ route('pegawai.template') }}" class="btn btn-outline-success btn-icon"><i class="bi bi-file-earmark-excel"></i> Download Template Terbaru</a>
                    </div>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="rounded-4 border bg-light p-3 h-100">
                    <div class="fw-semibold mb-2">Catatan penting</div>
                    <ul class="mb-0 ps-3 text-muted small">
                        <li>Template Excel sekarang sudah memuat kolom <strong>No NPWP</strong>.</li>
                        <li><strong>Foto profil tidak diimport dari Excel</strong>; unggah manual melalui form tambah/edit pegawai.</li>
                        <li>Golongan harus salah satu: II/A s.d. IV/E.</li>
                        <li>Kelas Jabatan diisi angka 1-16 atau nama kelas yang sama persis dengan master.</li>
                        <li>NIP harus 18 digit dan NIK, jika diisi, harus 16 digit.</li>
                        <li>Operator tidak dapat mengubah pegawai yang terdaftar pada unit kerja lain.</li>
                        <li>Jika satu baris gagal, seluruh file dibatalkan agar data tidak tersimpan sebagian.</li>
                        <li>Import akan masuk ke unit kerja yang dipilih atau unit kerja akun Anda.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
