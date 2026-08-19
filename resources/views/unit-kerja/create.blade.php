@extends('layouts.main')
@section('title', 'Tambah Unit Kerja')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="mb-1">Tambah Unit Kerja</h3><p class="text-muted mb-0">Tambahkan unit baru sebelum membuat user atau pegawai untuk unit tersebut.</p></div>
    <a href="{{ route('unit-kerja.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<form method="POST" action="{{ route('unit-kerja.store') }}" class="card shadow-soft border-0"><div class="card-body p-4 p-lg-5">@csrf
    <div class="row g-4">
        <div class="col-md-8"><label class="form-label fw-semibold">Nama Unit Kerja</label><input type="text" name="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" value="{{ old('nama_unit') }}" required>@error('nama_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-4"><label class="form-label fw-semibold">Kode Unit</label><input type="text" name="kode_unit" class="form-control @error('kode_unit') is-invalid @enderror" value="{{ old('kode_unit') }}">@error('kode_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('unit-kerja.index') }}" class="btn btn-light border">Batal</a><button class="btn btn-primary">Simpan</button></div>
</div></form>
@endsection
