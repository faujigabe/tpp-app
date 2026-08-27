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
      <div class="col-md-4"><strong>Pegawai:</strong> {{ $tpp->referensi_nama }}</div>
      <div class="col-md-4"><strong>NIP:</strong> {{ $tpp->referensi_nip }}</div>
      <div class="col-md-2"><strong>Bulan:</strong> {{ $tpp->bulan }}</div>
      <div class="col-md-2"><strong>Tahun:</strong> {{ $tpp->tahun }}</div>
      <div class="col-md-4"><strong>Gol:</strong> {{ $tpp->referensi_golongan }}</div>
      <div class="col-md-4"><strong>Jabatan:</strong> {{ $tpp->referensi_jabatan }}</div>
      <div class="col-md-4"><strong>Kelas:</strong> {{ $tpp->referensi_nomor_kelas ?: '-' }}</div>
    </div>
  </div>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('tpp.update', $tpp->id) }}" class="card">
  @csrf
  @method('PUT')
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Produktivitas (%)</label><input type="number" name="produktivitas" class="form-control" value="{{ old('produktivitas', $tpp->produktivitas) }}" min="0" max="100" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Kehadiran (%)</label><input type="number" name="kehadiran" class="form-control" value="{{ old('kehadiran', $tpp->kehadiran) }}" min="0" max="100" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Perilaku (%)</label><input type="number" name="perilaku" class="form-control" value="{{ old('perilaku', $tpp->perilaku) }}" min="0" max="100" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Tambahan TPP</label><input type="number" name="tambahan_tpp" class="form-control" value="{{ old('tambahan_tpp', $tpp->tambahan_tpp ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Potongan TPP (%)</label><input type="number" name="potongan_tpp" class="form-control" value="{{ old('potongan_tpp', $tpp->potongan_tpp ?? 0) }}" min="0" max="100" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">BPJS Kesehatan 1% (Peserta)</label><input type="number" name="bpjs_kesehatan" class="form-control" value="{{ old('bpjs_kesehatan', $tpp->iuran_wajib) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">BPJS Kesehatan 4% (Pemberi Kerja)</label><input type="number" name="bpjs_kesehatan_pemberi_kerja" class="form-control" value="{{ old('bpjs_kesehatan_pemberi_kerja', $tpp->bpjs_kesehatan_pemberi_kerja ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">TPP Tempat Bertugas</label><input type="number" name="tpp_tempat_bertugas" class="form-control" value="{{ old('tpp_tempat_bertugas', $tpp->tpp_tempat_bertugas ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Tunjangan PPH</label><input type="number" name="tunjangan_pph" class="form-control" value="{{ old('tunjangan_pph', $tpp->tunjangan_pph ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3">
        <label class="form-label">Potongan PPH 21</label>
        <div class="form-check mt-2">
          <input type="hidden" name="hitung_pajak" value="0">
          <input class="form-check-input" type="checkbox" id="hitung_pajak" name="hitung_pajak" value="1" @checked((int) old('hitung_pajak', (int) ($tpp->hitung_pajak ?? false)) === 1)>
          <label class="form-check-label" for="hitung_pajak">Aktifkan perhitungan pajak</label>
          <div class="form-text">Nonaktifkan jika PPh 21 dihitung oleh bendahara/BKAD.</div>
        </div>
      </div>
      <div class="col-md-3"><label class="form-label">Iuran JKK</label><input type="number" name="iuran_jkk" class="form-control" value="{{ old('iuran_jkk', $tpp->iuran_jkk ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Iuran JKM</label><input type="number" name="iuran_jkm" class="form-control" value="{{ old('iuran_jkm', $tpp->iuran_jkm ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Iuran Tapera</label><input type="number" name="iuran_tapera" class="form-control" value="{{ old('iuran_tapera', $tpp->iuran_tapera ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Iuran Pensiun</label><input type="number" name="iuran_pensiun" class="form-control" value="{{ old('iuran_pensiun', $tpp->iuran_pensiun ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Tunjangan JHT</label><input type="number" name="tunjangan_jht" class="form-control" value="{{ old('tunjangan_jht', $tpp->tunjangan_jht ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-md-3"><label class="form-label">Bulog</label><input type="number" name="bulog" class="form-control" value="{{ old('bulog', $tpp->bulog ?? 0) }}" min="0" step="0.01" required></div>
      <div class="col-12"><button class="btn btn-primary">Simpan Perubahan</button></div>
    </div>
  </div>
</form>
@endsection
