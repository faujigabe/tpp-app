@extends('layouts.main')

@section('title', 'Import Kelas Jabatan')

@section('content')

<h2>Import Kelas Jabatan Unit</h2>
<p class="text-muted">File yang diimport hanya berlaku untuk unit kerja <strong>{{ $activeUnitName }}</strong>.</p>

@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<p>
    Gunakan template agar header kolom sesuai.
    <a href="{{ route('kelas-jabatan.template') }}">Download Template</a>
</p>

<form action="{{ route('kelas-jabatan.import.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <label>File Excel (.xlsx/.xls) atau CSV:</label><br>
    <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
    <br><br>

    <button type="submit">Mulai Import</button>
</form>

<p style="margin-top: 20px;">
    Catatan:
    <ul>
        <li>Nomor Kelas wajib diisi 1-16.</li>
        <li>Jika Nomor Kelas sudah ada pada unit ini, data akan <b>diupdate</b> (upsert), bukan ditolak.</li>
        <li>Kolom kelangkaan_profesi boleh kosong.</li>
    </ul>
</p>

@endsection
