@extends('layouts.main')

@section('title', 'Edit Kelas Jabatan')

@section('content')
<div class="card shadow-soft">
    <div class="card-header fw-semibold">Edit Kelas Jabatan</div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kelas-jabatan.update', $kelas->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Nomor Kelas (1-16)</label>
                <input type="number" name="nomor_kelas" class="form-control" min="1" max="16" value="{{ old('nomor_kelas', $kelas->nomor_kelas) }}" required>
            </div>
            <div class="col-md-9">
                <label class="form-label">Nama Kelas</label>
                <input type="text" name="nama_kelas" class="form-control" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Beban Kerja</label>
                <input type="number" step="0.01" min="0" name="beban_kerja" class="form-control" value="{{ old('beban_kerja', $kelas->beban_kerja) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prestasi Kerja</label>
                <input type="number" step="0.01" min="0" name="prestasi_kerja" class="form-control" value="{{ old('prestasi_kerja', $kelas->prestasi_kerja) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kondisi Kerja</label>
                <input type="number" step="0.01" min="0" name="kondisi_kerja" class="form-control" value="{{ old('kondisi_kerja', $kelas->kondisi_kerja) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kelangkaan Profesi</label>
                <input type="number" step="0.01" min="0" name="kelangkaan_profesi" class="form-control" value="{{ old('kelangkaan_profesi', $kelas->kelangkaan_profesi) }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('kelas-jabatan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
