@extends('layouts.main')

@section('title', 'Import Kelas Jabatan')

@section('content')
<div class="card shadow-soft">
    <div class="card-header fw-semibold">Import Kelas Jabatan dari Excel</div>
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

        <p class="mb-3">
            Gunakan template agar header kolom sesuai.
            <a href="{{ route('kelas-jabatan.template') }}">Download Template</a>
        </p>

        <form action="{{ route('kelas-jabatan.import.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-8">
                <label class="form-label">File Excel (.xlsx/.xls) atau CSV</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mulai Import</button>
                <a href="{{ route('kelas-jabatan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>

        <div class="alert alert-light border mt-4 mb-0">
            <div class="fw-semibold mb-2">Catatan import</div>
            <ul class="mb-0 ps-3">
                <li>Nomor Kelas wajib diisi 1-16.</li>
                <li>Jika Nomor Kelas sudah ada, data akan diupdate (upsert).</li>
                <li>Kolom kelangkaan_profesi boleh kosong.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
