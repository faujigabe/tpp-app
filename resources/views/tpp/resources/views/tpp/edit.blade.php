@extends('layouts.main')

@section('title', 'Edit TPP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Edit TPP</h4>
  <a href="{{ route('tpp.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row g-2 small">
      <div class="col-md-4"><strong>Pegawai:</strong> {{ $tpp->pegawai->nama }}</div>
      <div class="col-md-4"><strong>NIP:</strong> {{ $tpp->pegawai->nip }}</div>
      <div class="col-md-2"><strong>Bulan:</strong> {{ $tpp->bulan }}</div>
      <div class="col-md-2"><strong>Tahun:</strong> {{ $tpp->tahun }}</div>
      <div class="col-md-4"><strong>Gol:</strong> {{ $tpp->pegawai->golongan }}</div>
      <div class="col-md-4"><strong>Jabatan:</strong> {{ $tpp->pegawai->jabatan }}</div>
      <div class="col-md-4"><strong>Kelas:</strong> {{ optional($tpp->pegawai->kelasJabatan)->nomor_kelas ?? '-' }}</div>
    </div>
  </div>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('tpp.update', $tpp->id) }}" class="card">
  @csrf
  @method('PUT')

  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Produktivitas (%)</label>
        <input type="number" name="produktivitas" class="form-control"
               value="{{ old('produktivitas', $tpp->produktivitas) }}" min="0" max="100" step="0.01" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Kehadiran (%)</label>
        <input type="number" name="kehadiran" class="form-control"
               value="{{ old('kehadiran', $tpp->kehadiran) }}" min="0" max="100" step="0.01" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Perilaku (%)</label>
        <input type="number" name="perilaku" class="form-control"
               value="{{ old('perilaku', $tpp->perilaku) }}" min="0" max="100" step="0.01" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">BPJS Kesehatan 1% (Peserta)</label>
        <input type="number" name="bpjs_kesehatan" class="form-control"
               value="{{ old('bpjs_kesehatan', $tpp->iuran_wajib) }}" min="0" step="0.01" required>
      </div>

      <div class="col-12">
        <button class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</form>
@endsection