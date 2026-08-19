@extends('layouts.main')

@section('title', 'Edit Pegawai')

@section('content')
@php
    $golonganList = ['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E'];
    $agamaList = ['Islam','Kristen','Katolik','Hindu','Buddha'];
@endphp

<div class="card shadow-soft">
    <div class="card-header fw-semibold">Edit Pegawai</div>
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

        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai->nama) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">NIP</label>
                <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai->nip) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $pegawai->nomor_rekening) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">No HP</label>
                <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $pegawai->no_hp) }}" placeholder="08xxxxxxxxxx">
            </div>
            <div class="col-md-4">
                <label class="form-label">Golongan</label>
                <select name="golongan" class="form-select" required>
                    @foreach($golonganList as $item)
                        <option value="{{ $item }}" {{ old('golongan', $pegawai->golongan) === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jabatan</label>
                <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $pegawai->jabatan) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Agama</label>
                <select name="agama" class="form-select" required>
                    @foreach($agamaList as $item)
                        <option value="{{ $item }}" {{ old('agama', $pegawai->agama) === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kelas Jabatan</label>
                <select name="kelas_jabatan_id" class="form-select" required>
                    @foreach($kelas as $item)
                        <option value="{{ $item->id }}" {{ (string) old('kelas_jabatan_id', $pegawai->kelas_jabatan_id) === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->nomor_kelas ?? '-' }} - {{ $item->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
