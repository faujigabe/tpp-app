@extends('layouts.main')

@section('title', 'Edit Kelas Jabatan')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Edit Kelas Jabatan</h3>
        <p class="text-muted mb-0">Perbarui nilai komponen kelas jabatan unit <strong>{{ $activeUnitName }}</strong> secara rapi dan konsisten.</p>
    </div>
    <a href="{{ route('kelas-jabatan.index') }}" class="btn btn-outline-secondary btn-icon">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-soft border-0">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Periksa kembali data kelas jabatan.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-soft">
            <div class="card-body p-4 p-lg-5">
                <form action="{{ route('kelas-jabatan.update', $kelas->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nomor Kelas</label>
                            <input type="number" name="nomor_kelas" min="1" max="16" class="form-control @error('nomor_kelas') is-invalid @enderror" value="{{ old('nomor_kelas', $kelas->nomor_kelas) }}" required>
                            @error('nomor_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                            @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Beban Kerja</label>
                            <input type="number" step="0.01" name="beban_kerja" class="form-control @error('beban_kerja') is-invalid @enderror" value="{{ old('beban_kerja', $kelas->beban_kerja) }}" required>
                            @error('beban_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prestasi Kerja</label>
                            <input type="number" step="0.01" name="prestasi_kerja" class="form-control @error('prestasi_kerja') is-invalid @enderror" value="{{ old('prestasi_kerja', $kelas->prestasi_kerja) }}" required>
                            @error('prestasi_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kondisi Kerja</label>
                            <input type="number" step="0.01" name="kondisi_kerja" class="form-control @error('kondisi_kerja') is-invalid @enderror" value="{{ old('kondisi_kerja', $kelas->kondisi_kerja) }}" required>
                            @error('kondisi_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kelangkaan Profesi</label>
                            <input type="number" step="0.01" name="kelangkaan_profesi" class="form-control @error('kelangkaan_profesi') is-invalid @enderror" value="{{ old('kelangkaan_profesi', $kelas->kelangkaan_profesi) }}" placeholder="Boleh dikosongkan">
                            @error('kelangkaan_profesi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                        <a href="{{ route('kelas-jabatan.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="bi bi-save"></i> Update Kelas Jabatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
