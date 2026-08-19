@extends('layouts.main')

@section('title', 'Input TPP')

@section('content')
@php
  $bulanNow = (int) date('n');
  $tahunNow = (int) date('Y');
  $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h3 class="mb-0">Perhitungan TPP Massal</h3>
    <div class="text-muted">Pilih bulan &amp; tahun, lalu isi nilai per pegawai.</div>
  </div>
  <a href="{{ route('tpp.index') }}" class="btn btn-outline-secondary btn-icon">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

@if(session('error'))
  <div class="alert alert-danger shadow-soft">{{ session('error') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger shadow-soft">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Periksa input:</div>
    <ul class="mb-0 ps-3">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if(!empty($pegawaiTanpaKelas) && $pegawaiTanpaKelas->count() > 0)
  <div class="alert alert-warning shadow-soft">
    <div class="fw-semibold mb-1">Ada pegawai yang belum punya Kelas Jabatan.</div>
    <div class="small">Pegawai berikut akan menghasilkan TPP 0 jika tetap diproses: {{ $pegawaiTanpaKelas->pluck('nama')->implode(', ') }}</div>
  </div>
@endif

<form method="POST" action="{{ route('tpp.store') }}" class="card shadow-soft">
  @csrf

  <div class="card-body">
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <label class="form-label">Bulan</label>
        <select name="bulan" class="form-select" required>
          @foreach($bulanNama as $num => $nama)
            <option value="{{ $num }}" {{ old('bulan', $bulanNow) == $num ? 'selected' : '' }}>{{ $nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Tahun</label>
        <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $tahunNow) }}" min="2000" max="2100" required>
      </div>

      <div class="col-md-6 d-flex align-items-end gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-primary btn-icon" id="btnSet100">
          <i class="bi bi-check2-circle"></i> Set 100% (Semua)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-icon" id="btnSetBpjs0">
          <i class="bi bi-clipboard2-minus"></i> Set BPJS 0
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:60px;">No</th>
            <th>Pegawai</th>
            <th style="width:180px;">NIP</th>
            <th style="width:90px;">Gol</th>
            <th>Jabatan</th>
            <th style="width:90px;">Kelas</th>
            <th style="width:145px;">Produktivitas (%)</th>
            <th style="width:145px;">Kehadiran (%)</th>
            <th style="width:145px;">Perilaku (%)</th>
            <th style="width:220px;">BPJS Kesehatan 1%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pegawais as $i => $p)
            @php $pid = $p->id; @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <div class="fw-semibold">{{ $p->nama }}</div>
                @if(!$p->kelasJabatan)
                  <div class="small text-danger">Belum ada kelas jabatan</div>
                @endif
              </td>
              <td class="text-muted">{{ $p->nip }}</td>
              <td><span class="badge rounded-pill text-bg-light border">{{ $p->golongan }}</span></td>
              <td>{{ $p->jabatan }}</td>
              <td>{{ optional($p->kelasJabatan)->nomor_kelas ?? '-' }}</td>
              <td>
                <input type="number" class="form-control form-control-sm inp-prod" name="produktivitas[{{ $pid }}]" value="{{ old("produktivitas.$pid", 100) }}" min="0" max="100" step="0.01" required>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm inp-keh" name="kehadiran[{{ $pid }}]" value="{{ old("kehadiran.$pid", 100) }}" min="0" max="100" step="0.01" required>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm inp-per" name="perilaku[{{ $pid }}]" value="{{ old("perilaku.$pid", 100) }}" min="0" max="100" step="0.01" required>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm inp-bpjs" name="bpjs_kesehatan[{{ $pid }}]" value="{{ old("bpjs_kesehatan.$pid", 0) }}" min="0" step="0.01" required>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <button class="btn btn-primary btn-icon">
        <i class="bi bi-save2"></i> Hitung &amp; Simpan
      </button>
    </div>
  </div>
</form>

@push('scripts')
<script>
  document.getElementById('btnSet100')?.addEventListener('click', () => {
    document.querySelectorAll('.inp-prod,.inp-keh,.inp-per').forEach(el => el.value = 100);
  });

  document.getElementById('btnSetBpjs0')?.addEventListener('click', () => {
    document.querySelectorAll('.inp-bpjs').forEach(el => el.value = 0);
  });
</script>
@endpush
@endsection
