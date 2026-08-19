@extends('layouts.main')
@section('title','Dashboard')

@section('content')
<h2 class="mb-3">Dashboard TPP</h2>

<form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end">
  <div class="col-md-2">
    <label class="form-label">Bulan</label>
    <input type="number" class="form-control" name="bulan" min="1" max="12" value="{{ $bulan }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">Tahun</label>
    <input type="number" class="form-control" name="tahun" value="{{ $tahun }}">
  </div>
  <div class="col-md-3">
    <button class="btn btn-primary">Tampilkan</button>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Reset</a>
  </div>
</form>

<hr class="my-4">

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Total Pegawai</div>
        <div class="fs-4 fw-bold">{{ $totalPegawai }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Jumlah Perhitungan ({{ $bulan }}/{{ $tahun }})</div>
        <div class="fs-4 fw-bold">{{ $jumlahPerhitungan }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Total TPP Kotor</div>
        <div class="fs-5 fw-bold">Rp {{ number_format($totalTppKotor,0,',','.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Total Pajak</div>
        <div class="fs-5 fw-bold">Rp {{ number_format($totalPajak,0,',','.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted">Total Zakat</div>
        <div class="fs-5 fw-bold">Rp {{ number_format($totalZakat,0,',','.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm h-100 border border-success">
      <div class="card-body">
        <div class="text-muted">Total Diterima</div>
        <div class="fs-5 fw-bold text-success">Rp {{ number_format($totalDiterima,0,',','.') }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
  <div class="card shadow-sm h-100 border border-primary">
    <div class="card-body">
      <div class="text-muted">Rata-rata TPP / Pegawai</div>
      <div class="fs-5 fw-bold text-primary">
        Rp {{ number_format($rataDiterima,0,',','.') }}
      </div>
    </div>
  </div>
</div>


  {{-- Opsional: statistik user --}}
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted mb-2">User Aktif</div>
        <div class="d-flex gap-3">
          <div><strong>Admin:</strong> {{ $userAdmin }}</div>
          <div><strong>Operator:</strong> {{ $userOperator }}</div>
          <div><strong>Viewer:</strong> {{ $userViewer }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<h4 class="mb-2">Grafik Total Diterima per Bulan ({{ $tahun }})</h4>
<canvas id="chart" height="120"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = @json($labels);
const values = @json($values);

function rupiah(n){
  return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
}

new Chart(document.getElementById('chart'), {
  type: 'bar',
  data: { labels, datasets: [{ label: 'Total Diterima', data: values, borderWidth: 1 }] },
  options: {
    plugins: {
      tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': ' + rupiah(ctx.parsed.y) } }
    },
    scales: {
      y: { ticks: { callback: (v) => rupiah(v) } }
    }
  }
}


);
</script>
@endsection