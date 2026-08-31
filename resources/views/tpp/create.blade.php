@extends('layouts.main')

@section('title', 'Input TPP')

@section('content')


@push('styles')
<style>
  .tpp-massal-table-wrap {
    border: 1px solid #e9edf5;
    border-radius: 18px;
    overflow: auto;
    background: #fff;
    max-height: 70vh;
  }
  .tpp-massal-table {
    min-width: 1180px;
    margin-bottom: 0;
  }
  .tpp-massal-table[data-active-group="all"] {
    min-width: 3200px;
  }
  .tpp-massal-table thead th {
    background: #f8fafc;
    vertical-align: middle;
    white-space: nowrap;
    font-size: .92rem;
    padding: 1rem .9rem;
    border-bottom: 1px solid #e9edf5;
    position: sticky;
    top: 0;
    z-index: 6;
  }
  .tpp-massal-table tbody td {
    padding: 1rem .9rem;
    vertical-align: top;
    border-color: #eef2f7;
    background: #fff;
  }
  .tpp-massal-table tbody tr:hover td {
    background: #fbfdff;
  }
  .tpp-massal-table tbody tr:hover .sticky-col {
    background: #fbfdff !important;
  }
  .tpp-massal-table .pegawai-cell {
    min-width: 260px;
  }
  .tpp-massal-table .pegawai-cell .nama {
    font-weight: 700;
    line-height: 1.35;
    color: #0f172a;
  }
  .tpp-massal-table .pegawai-cell .nip {
    font-size: .85rem;
    color: #64748b;
    margin-top: .2rem;
  }
  .tpp-massal-table .meta-badge {
    display: inline-flex;
    align-items: center;
    padding: .35rem .65rem;
    border-radius: 999px;
    background: #f8fafc;
    border: 1px solid #e9edf5;
    font-weight: 600;
  }
  .tpp-massal-table .input-stack {
    min-width: 150px;
  }
  .tpp-massal-table .input-stack .form-control {
    min-height: 46px;
    padding: .7rem .85rem;
  }
  .tpp-massal-table .input-stack .form-text {
    margin-top: .45rem;
    font-size: .82rem;
  }
  .tpp-massal-table .sticky-col {
    position: sticky;
    z-index: 4;
    background: #fff !important;
    background-clip: padding-box;
  }
  .tpp-massal-table thead .sticky-col {
    z-index: 7;
    background: #f8fafc !important;
  }
  .tpp-massal-table tbody .sticky-col {
    box-shadow: none !important;
  }
  .tpp-massal-table .sticky-no {
    left: 0;
    min-width: 64px;
    width: 64px;
  }
  .tpp-massal-table .sticky-pegawai {
    left: 64px;
    min-width: 300px;
    width: 300px;
    border-right: 1px solid #e5e7eb;
  }
  .section-label {
    font-size: .9rem;
    font-weight: 700;
    color: #475467;
    margin-bottom: .4rem;
  }

  .tpp-summary-card {
    border: 1px solid #e9edf5;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 1rem 1.1rem;
    height: 100%;
  }
  .tpp-summary-card .label {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    margin-bottom: .35rem;
    font-weight: 700;
  }
  .tpp-summary-card .value {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
  }
  .tpp-toolbar {
    display: flex;
    gap: .75rem;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 1rem;
  }
  .tpp-toolbar .search-box {
    min-width: 280px;
    flex: 1 1 320px;
    max-width: 460px;
  }
  .tpp-row-hidden {
    display: none;
  }
  .tpp-column-hidden {
    display: none !important;
  }
  .tpp-group-switcher {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
    padding: .65rem;
    border: 1px solid #e4eaf2;
    border-radius: 16px;
    background: #f8fafc;
  }
  .tpp-group-button {
    border-radius: 12px;
    font-weight: 700;
  }
  .tpp-group-button[aria-pressed="true"] {
    color: #fff;
    background: #1f7ae0;
    border-color: #1f7ae0;
    box-shadow: 0 8px 18px rgba(31, 122, 224, .2);
  }
  .tpp-group-help {
    min-height: 48px;
    padding: .75rem 1rem;
    border-left: 4px solid #1f7ae0;
    border-radius: 10px;
    background: #eef6ff;
  }
  .tpp-massal-table .form-control.is-edited {
    border-color: #f59e0b;
    background: #fffbeb;
    box-shadow: 0 0 0 .2rem rgba(245, 158, 11, .08);
  }
  .tpp-massal-table .form-control:invalid {
    border-color: #dc3545;
  }
  .pegawai-mobile-meta {
    display: none;
  }
  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
    margin-right: .45rem;
    vertical-align: middle;
  }
  @media (max-width: 767.98px) {
    .tpp-massal-table {
      min-width: 660px;
    }
    .tpp-massal-table .sticky-pegawai {
      left: 52px;
      min-width: 245px;
      width: 245px;
    }
    .tpp-massal-table .sticky-no {
      min-width: 52px;
      width: 52px;
    }
    .identity-extra {
      display: none !important;
    }
    .pegawai-mobile-meta {
      display: block;
      margin-top: .35rem;
      color: #64748b;
      font-size: .78rem;
    }
    .tpp-group-switcher {
      flex-wrap: nowrap;
      overflow-x: auto;
      padding-bottom: .75rem;
    }
    .tpp-group-button {
      white-space: nowrap;
    }
  }
</style>
@endpush


@php
  $bulanNow = $selectedBulan ?? (int)date('n');
  $tahunNow = $selectedTahun ?? (int)date('Y');
  $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
  $isSuperAdmin = auth()->user()?->isSuperAdmin();
  $selectedStatus = $periodApproval?->normalizedStatus() ?? \App\Models\TppApproval::STATUS_DRAFT;
  $statusBadgeClass = \App\Models\TppApproval::badgeClassFor($selectedStatus);
  $statusLabel = \App\Models\TppApproval::labelFor($selectedStatus);
  $statusAlertClass = \App\Models\TppApproval::alertClassFor($selectedStatus);
  $statusDotClass = \App\Models\TppApproval::dotClassFor($selectedStatus);
  $currentPeriodCount = isset($currentPeriodInputs) ? $currentPeriodInputs->count() : 0;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Perhitungan TPP (Massal)</h3>
    <div class="text-muted">Kelola input per pegawai sesuai periode aktif, lalu simpan dalam satu proses massal.</div>
  </div>
  <a href="{{ route('tpp.index') }}" class="btn btn-outline-secondary btn-icon">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="tpp-summary-card">
      <div class="label">Periode aktif</div>
      <div class="value">{{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }}</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="tpp-summary-card">
      <div class="label">Status periode</div>
      <div class="value"><span class="badge {{ $statusBadgeClass }} px-3 py-2">{{ $statusLabel }}</span></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="tpp-summary-card">
      <div class="label">Unit kerja aktif</div>
      <div class="value">{{ optional($activeUnitKerja)->nama_unit ?? ($isSuperAdmin ? 'Belum dipilih' : '-') }}</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="tpp-summary-card">
      <div class="label">Progress input</div>
      <div class="value">{{ $currentPeriodCount }} / {{ $pegawais->count() }} pegawai</div>
      <div class="small text-muted mt-1">Sudah pernah dihitung pada periode ini: <strong>{{ $currentPeriodCount }}</strong></div>
    </div>
  </div>
</div>

<div class="card shadow-soft mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('tpp.create') }}" class="row g-3 align-items-end">
      @if($isSuperAdmin)
      <div class="col-md-4">
        <label class="form-label">Unit Kerja</label>
        <select name="unit_kerja_id" class="form-select" required>
          <option value="">Pilih unit kerja</option>
          @foreach($availableUnitKerjas as $unit)
            <option value="{{ $unit->id }}" {{ (int) $selectedUnitKerjaId === (int) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
      @else
      <div class="col-md-3">
      @endif
        <label class="form-label">Muat default untuk Bulan</label>
        <select name="bulan" class="form-select">
          @foreach($bulanNama as $num=>$nama)
            <option value="{{ $num }}" {{ $bulanNow==$num ? 'selected':'' }}>{{ $nama }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tahun</label>
        <input type="number" name="tahun" class="form-control" value="{{ $tahunNow }}" min="2000" max="2100">
      </div>
      <div class="col-md-{{ $isSuperAdmin ? '2' : '6' }} d-flex gap-2 flex-wrap align-items-center">
        <button class="btn btn-outline-primary btn-icon" type="submit">
          <i class="bi bi-arrow-repeat"></i> Muat Data
        </button>
      </div>
      <div class="col-12">
        <div class="small text-muted">
          Default akan diambil dari periode sebelumnya: <strong>{{ $bulanNama[(int) $prevMonth->month] ?? $prevMonth->format('m') }} {{ $prevMonth->year }}</strong>
        </div>
      </div>
    </form>
  </div>
</div>

@if($selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT)
  <div class="alert {{ $statusAlertClass }} shadow-soft">
    @if($selectedStatus === \App\Models\TppApproval::STATUS_SUBMITTED)
      <div class="fw-semibold mb-1"><i class="bi bi-lock me-1"></i>Periode {{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }} Terkunci.</div>
      <div>Silahkan tunggu validasi admin dan jika masih ada data yang belum pas silahkan hubungi admin untuk membatalkan validasi dan membuka kunci.</div>
    @else
      <div class="fw-semibold mb-1"><i class="bi bi-patch-check me-1"></i>Periode {{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }} sudah ter Validasi.</div>
      <div>Jika ada perubahan data, silahkan hubungi admin untuk membuka kunci.</div>
    @endif
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger shadow-soft">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Periksa input:</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

@if(!empty($ekinerjaImport))
  <div class="alert alert-info shadow-soft">
    <div class="fw-semibold mb-1"><i class="bi bi-file-earmark-pdf me-1"></i>Hasil import PDF e-Kinerja</div>
    <div>Berhasil dicocokkan ke pegawai unit ini: <strong>{{ $ekinerjaImport['matched_count'] ?? 0 }}</strong> dari <strong>{{ $ekinerjaImport['record_count'] ?? 0 }}</strong> baris.</div>
    @if(!empty($ekinerjaImport['matched_by']))
      <div class="small text-muted mt-1">Pencocokan: NIP <strong>{{ $ekinerjaImport['matched_by']['nip'] ?? 0 }}</strong>, nama <strong>{{ $ekinerjaImport['matched_by']['nama'] ?? 0 }}</strong>.</div>
    @endif
    @if(!empty($ekinerjaImport['unmatched']))
      <div class="small mt-2">Baris yang belum cocok (maks. 10):
        <ul class="mb-0 mt-1 ps-3">
          @foreach(($ekinerjaImport['unmatched'] ?? []) as $row)
            <li>{{ $row['nip'] ?? '-' }} - {{ $row['nama'] ?? '-' }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
@endif

@if($isSuperAdmin && !$selectedUnitKerjaId)
  <div class="alert alert-info shadow-soft">
    <div class="fw-semibold mb-1"><i class="bi bi-building me-1"></i>Pilih unit kerja terlebih dahulu</div>
    <div>Untuk input TPP massal sebagai super admin, pilih unit kerja pada panel di atas lalu klik <strong>Muat Data</strong>. Daftar pegawai dan default periode akan mengikuti unit kerja yang dipilih.</div>
  </div>
@endif


@if(!$isSuperAdmin || $selectedUnitKerjaId)
<div class="card shadow-soft mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="fw-semibold">Import Nilai e-Kinerja dari PDF</div>
        <div class="small text-muted">Ambil otomatis nilai <strong>% Kehadiran</strong>, <strong>% Perilaku</strong>, dan <strong>% Produktivitas</strong> per pegawai berdasarkan NIP.</div>
      </div>
    </div>
    <form method="POST" action="{{ route('tpp.import-ekinerja-pdf') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
      @csrf
      <input type="hidden" name="bulan" value="{{ $bulanNow }}">
      <input type="hidden" name="tahun" value="{{ $tahunNow }}">
      <div class="col-md-6">
        <label class="form-label">Upload PDF e-Kinerja</label>
        <input type="file" name="ekinerja_pdf" class="form-control" accept="application/pdf" required {{ $selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT ? 'disabled' : '' }}>
      </div>
      <div class="col-md-3">
        <label class="form-label">Periode import</label>
        <input type="text" class="form-control" value="{{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }}" readonly>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-danger btn-icon" {{ $selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT ? 'disabled' : '' }} title="{{ $selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT ? 'Periode terpilih sedang menunggu validasi atau sudah divalidasi.' : '' }}">
          <i class="bi bi-file-earmark-arrow-up"></i> Import PDF
        </button>
      </div>
    </form>
  </div>
</div>
@endif

<form method="POST" action="{{ route('tpp.store') }}" class="card shadow-soft" id="tppMassalForm"
      data-confirm
      data-confirm-title="Simpan perhitungan TPP massal?"
      data-confirm-message="Seluruh nilai pegawai periode {{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }} akan dihitung dan disimpan. Pastikan setiap kelompok kolom sudah diperiksa."
      data-confirm-label="Hitung & Simpan"
      data-confirm-variant="primary">
  @csrf
  @if($isSuperAdmin && $selectedUnitKerjaId)
    <input type="hidden" name="unit_kerja_id" value="{{ $selectedUnitKerjaId }}">
  @endif

  <div class="card-body">
    <div class="row g-3 mb-3">
      @if($isSuperAdmin)
      <div class="col-md-4">
        <label class="form-label">Unit kerja aktif</label>
        <input type="text" class="form-control" value="{{ optional($activeUnitKerja)->nama_unit ?? 'Belum dipilih' }}" readonly>
      </div>
      @endif
      <div class="col-md-{{ $isSuperAdmin ? '2' : '3' }}">
        <label class="form-label">Pilih bulan dan tahun perhitungan</label>
        <select name="bulan" class="form-select" required>
          @foreach($bulanNama as $num=>$nama)
            <option value="{{ $num }}" {{ old('bulan', $bulanNow)==$num ? 'selected':'' }}>{{ $nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-{{ $isSuperAdmin ? '2' : '3' }}">
        <label class="form-label">Tahun</label>
        <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $tahunNow) }}" min="2000" max="2100" required>
      </div>

      <div class="col-md-{{ $isSuperAdmin ? '4' : '6' }} d-flex align-items-end gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-primary btn-icon" id="btnSet100" data-action-group="kinerja">
          <i class="bi bi-check2-circle"></i> Set 100% (Semua)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-icon" id="btnSetBpjs0" data-action-group="potongan">
          <i class="bi bi-clipboard2-minus"></i> Set BPJS 0
        </button>
        <button type="button" class="btn btn-outline-secondary btn-icon" id="btnSetBpjs4Zero" data-action-group="potongan">
          <i class="bi bi-building"></i> Set BPJS 4% 0
        </button>
        <button type="button" class="btn btn-outline-warning btn-icon" id="btnSetPotongan0" data-action-group="potongan">
          <i class="bi bi-percent"></i> Set Potongan 0%
        </button>
        <button type="button" class="btn btn-outline-secondary btn-icon" id="btnSetSipd0" data-action-group="sipd">
          <i class="bi bi-list-columns"></i> Set Kolom SIPD 0
        </button>
      </div>
    </div>

    <div class="alert alert-light border small">
      <div class="mb-1"><span class="status-dot {{ $statusDotClass }}"></span>Status periode saat ini: <strong>{{ $statusLabel }}</strong></div>
      @if($activeUnitKerja)
        <div class="mb-1">Unit kerja aktif: <strong>{{ $activeUnitKerja->nama_unit }}</strong></div>
      @endif
      <div class="fw-semibold mb-1">Aturan hitung baru</div>
      <div>Tambahan TPP akan ditambahkan ke <strong>Beban Kerja</strong>. Potongan TPP diisi dalam persen potongan, lalu sistem memakai nilai efektif <strong>100 - potongan</strong> untuk mengalikan komponen yang memiliki nilai.</div>
      <div class="mt-1">Nilai default komponen manual akan mengambil data dari periode sebelumnya sesuai pilihan di atas, yaitu <strong>{{ $bulanNama[(int) $prevMonth->month] ?? $prevMonth->format('m') }} {{ $prevMonth->year }}</strong>, bila tersedia.</div>
    </div>

    @if(!$isSuperAdmin || $selectedUnitKerjaId)
    <div class="mb-3">
      <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-2">
        <div>
          <div class="fw-semibold">Kelompok kolom input</div>
          <div class="small text-muted">Pilih kelompok yang ingin dikerjakan. Identitas pegawai selalu terlihat.</div>
        </div>
        <div class="tpp-group-switcher" role="group" aria-label="Kelompok kolom input TPP">
          <button type="button" class="btn btn-outline-primary tpp-group-button" data-tpp-group="kinerja" aria-pressed="true"><i class="bi bi-speedometer2 me-1"></i>Kinerja</button>
          <button type="button" class="btn btn-outline-primary tpp-group-button" data-tpp-group="potongan" aria-pressed="false"><i class="bi bi-percent me-1"></i>Potongan &amp; BPJS</button>
          <button type="button" class="btn btn-outline-primary tpp-group-button" data-tpp-group="sipd" aria-pressed="false"><i class="bi bi-list-columns-reverse me-1"></i>Komponen SIPD</button>
          <button type="button" class="btn btn-outline-secondary tpp-group-button" data-tpp-group="all" aria-pressed="false"><i class="bi bi-grid-3x3-gap me-1"></i>Lihat Semua</button>
        </div>
      </div>
      <div class="tpp-group-help small" id="tppGroupHelp" role="status" aria-live="polite"></div>
    </div>

    <div class="tpp-toolbar">
      <div>
        <div class="fw-semibold">Daftar pegawai periode {{ $bulanNama[$bulanNow] ?? $bulanNow }} {{ $tahunNow }}</div>
        <div class="small text-muted">Cari nama atau NIP untuk mempercepat input. Header tabel akan tetap terlihat saat Anda scroll.</div>
      </div>
      <div class="search-box">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" id="pegawaiSearchInput" placeholder="Cari nama atau NIP pegawai...">
        </div>
        <div class="small text-muted mt-1">Pegawai terlihat: <strong id="pegawaiVisibleCount">{{ $pegawais->count() }}</strong> dari {{ $pegawais->count() }}</div>
      </div>
    </div>
    <div class="tpp-massal-table-wrap">
      <table class="table table-hover align-middle tpp-massal-table" data-active-group="kinerja">
        <thead class="table-light">
          <tr>
            <th class="sticky-col sticky-no">No</th>
            <th class="sticky-col sticky-pegawai">Pegawai</th>
            <th class="identity-extra">Gol</th>
            <th class="identity-extra">Jabatan</th>
            <th class="identity-extra">Kelas</th>
            <th data-column-group="kinerja" style="width:140px;">Produktivitas (%)</th>
            <th data-column-group="kinerja" style="width:140px;">Kehadiran (%)</th>
            <th data-column-group="kinerja" style="width:140px;">Perilaku (%)</th>
            <th data-column-group="potongan" style="width:180px;">Tambahan TPP</th>
            <th data-column-group="potongan" style="width:170px;">Potongan TPP (%)</th>
            <th data-column-group="potongan" style="width:220px;">BPJS Kesehatan 1% (Peserta)</th>
            <th data-column-group="potongan" style="width:240px;">BPJS Kesehatan 4% (Pemberi Kerja)</th>
            <th data-column-group="sipd" style="width:180px;">TPP Tempat Bertugas</th>
            <th data-column-group="sipd" style="width:160px;">Tunjangan PPH</th>
            <th data-column-group="sipd" style="width:220px;">
              <div class="d-flex flex-column align-items-center gap-1">
                <span>Potongan PPH 21</span>
                <div class="d-flex flex-wrap justify-content-center gap-1">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="btnPilihSemuaPajakCreate">Pilih Semua</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnKosongkanSemuaPajakCreate">Kosongkan Semua</button>
                </div>
              </div>
            </th>
            <th data-column-group="sipd" style="width:170px;">Iuran JKK</th>
            <th data-column-group="sipd" style="width:170px;">Iuran JKM</th>
            <th data-column-group="sipd" style="width:170px;">Iuran Tapera</th>
            <th data-column-group="sipd" style="width:170px;">Iuran Pensiun</th>
            <th data-column-group="sipd" style="width:180px;">Tunjangan JHT</th>
            <th data-column-group="sipd" style="width:150px;">Bulog</th>
                      </tr>
        </thead>
        <tbody>
          @foreach($pegawais as $i => $p)
            @php
              $pid = $p->id;
              $defaults = $defaultInputs[$pid] ?? [];
            @endphp
            <tr data-pegawai-row data-search-text="{{ \Illuminate\Support\Str::lower($p->nama . ' ' . ($p->nip ?? '')) }}">
              <td class="sticky-col sticky-no">{{ $i+1 }}</td>
              <td class="sticky-col sticky-pegawai pegawai-cell">
                <div class="nama">{{ $p->nama }}</div>
                <div class="nip">NIP: {{ $p->nip }}</div>
                <div class="pegawai-mobile-meta">Gol. {{ $p->golongan }} · Kelas {{ optional($p->kelasJabatan)->nomor_kelas ?? '-' }}<br>{{ $p->jabatan }}</div>
                @if(isset($currentPeriodInputs[$pid]))
                  <div class="mt-2"><span class="badge text-bg-success">Sudah ada input periode ini</span></div>
                @endif
                @if(data_get($ekinerjaImport, 'matched.' . $pid))
                  <div class="mt-2"><span class="badge text-bg-info">Nilai e-Kinerja terisi</span></div>
                @endif
              </td>
              <td class="identity-extra"><span class="meta-badge">{{ $p->golongan }}</span></td>
              <td class="identity-extra">{{ $p->jabatan }}</td>
              <td class="identity-extra"><span class="meta-badge">{{ optional($p->kelasJabatan)->nomor_kelas ?? '-' }}</span></td>

              <td data-column-group="kinerja">
                <div class="input-stack">
                <input type="number" class="form-control inp-prod"
                       name="produktivitas[{{ $pid }}]"
                       value="{{ old("produktivitas.$pid", data_get($ekinerjaImport, 'matched.' . $pid . '.produktivitas', 100)) }}"
                       min="0" max="100" step="0.01" required>
                </div>
              </td>
              <td data-column-group="kinerja">
                <div class="input-stack">
                <input type="number" class="form-control inp-keh"
                       name="kehadiran[{{ $pid }}]"
                       value="{{ old("kehadiran.$pid", data_get($ekinerjaImport, 'matched.' . $pid . '.kehadiran', 100)) }}"
                       min="0" max="100" step="0.01" required>
                </div>
              </td>
              <td data-column-group="kinerja">
                <div class="input-stack">
                <input type="number" class="form-control inp-per"
                       name="perilaku[{{ $pid }}]"
                       value="{{ old("perilaku.$pid", data_get($ekinerjaImport, 'matched.' . $pid . '.perilaku', 100)) }}"
                       min="0" max="100" step="0.01" required>
                </div>
              </td>
              <td data-column-group="potongan">
                <div class="input-stack">
                <input type="number" class="form-control inp-tambahan"
                       name="tambahan_tpp[{{ $pid }}]"
                       value="{{ old("tambahan_tpp.$pid", $defaults['tambahan_tpp'] ?? 0) }}"
                       min="0" step="0.01" required>
                </div>
              </td>
              <td data-column-group="potongan">
                <div class="input-stack">
                <input type="number" class="form-control inp-potongan"
                       name="potongan_tpp[{{ $pid }}]"
                       value="{{ old("potongan_tpp.$pid", 0) }}"
                       min="0" max="100" step="0.01" required>
                <div class="form-text">Nilai efektif = <span class="fw-semibold text-primary preview-potongan">100.00%</span></div>
                </div>
              </td>
              <td data-column-group="potongan">
                <div class="input-stack">
                <input type="number" class="form-control inp-bpjs"
                       name="bpjs_kesehatan[{{ $pid }}]"
                       value="{{ old("bpjs_kesehatan.$pid", $defaults['bpjs_kesehatan'] ?? 0) }}"
                       min="0" step="0.01" required>
                </div>
              </td>
              <td data-column-group="potongan">
                <div class="input-stack">
                <input type="number" class="form-control inp-bpjs-4"
                       name="bpjs_kesehatan_pemberi_kerja[{{ $pid }}]"
                       value="{{ old("bpjs_kesehatan_pemberi_kerja.$pid", $defaults['bpjs_kesehatan_pemberi_kerja'] ?? 0) }}"
                       min="0" step="0.01" required>
                </div>
              </td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="tpp_tempat_bertugas[{{ $pid }}]" value="{{ old("tpp_tempat_bertugas.$pid", $defaults['tpp_tempat_bertugas'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="tunjangan_pph[{{ $pid }}]" value="{{ old("tunjangan_pph.$pid", $defaults['tunjangan_pph'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd">
                <input type="hidden" name="hitung_pajak[{{ $pid }}]" value="0">
                <div class="form-check d-flex justify-content-center">
                  <input class="form-check-input hitung-pajak-item" type="checkbox" name="hitung_pajak[{{ $pid }}]" value="1" @checked((int) old("hitung_pajak.$pid", array_key_exists('hitung_pajak', $defaults ?? []) ? (int) $defaults['hitung_pajak'] : 0) === 1)>
                </div>
              </td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="iuran_jkk[{{ $pid }}]" value="{{ old("iuran_jkk.$pid", $defaults['iuran_jkk'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="iuran_jkm[{{ $pid }}]" value="{{ old("iuran_jkm.$pid", $defaults['iuran_jkm'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="iuran_tapera[{{ $pid }}]" value="{{ old("iuran_tapera.$pid", $defaults['iuran_tapera'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="iuran_pensiun[{{ $pid }}]" value="{{ old("iuran_pensiun.$pid", $defaults['iuran_pensiun'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="tunjangan_jht[{{ $pid }}]" value="{{ old("tunjangan_jht.$pid", $defaults['tunjangan_jht'] ?? 0) }}" min="0" step="0.01" required></div></td>
              <td data-column-group="sipd"><div class="input-stack"><input type="number" class="form-control inp-sipd" name="bulog[{{ $pid }}]" value="{{ old("bulog.$pid", $defaults['bulog'] ?? 0) }}" min="0" step="0.01" required></div></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
      <div class="text-muted small">Total pegawai yang siap diinput: <strong>{{ $pegawais->count() }}</strong></div>
      <button class="btn btn-primary btn-icon" {{ ($pegawais->isEmpty() || ($selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT)) ? 'disabled' : '' }} title="{{ ($selectedStatus !== \App\Models\TppApproval::STATUS_DRAFT) ? 'Periode terpilih sedang menunggu validasi atau sudah divalidasi.' : '' }}">
        <i class="bi bi-save2"></i> Hitung & Simpan (Semua Pegawai)
      </button>
    </div>
    @else
    <div class="border rounded-4 p-4 text-center bg-light-subtle text-muted">
      Pilih unit kerja terlebih dahulu untuk menampilkan daftar pegawai yang akan diinput TPP-nya.
    </div>
    @endif
  </div>
</form>

@push('scripts')
<script>
  const tppGroupDescriptions = {
    kinerja: '<strong>Kinerja:</strong> produktivitas, kehadiran, dan perilaku. Gunakan “Set 100%” bila seluruh pegawai memiliki nilai penuh.',
    potongan: '<strong>Potongan &amp; BPJS:</strong> tambahan TPP, persentase potongan, BPJS peserta, dan BPJS pemberi kerja.',
    sipd: '<strong>Komponen SIPD:</strong> tempat bertugas, tunjangan/potongan PPh, JKK, JKM, Tapera, pensiun, JHT, dan Bulog.',
    all: '<strong>Semua kolom:</strong> gunakan tampilan ini hanya bila perlu membandingkan seluruh komponen secara bersamaan.'
  };

  const setActiveTppGroup = (group, remember = true) => {
    const selectedGroup = Object.prototype.hasOwnProperty.call(tppGroupDescriptions, group) ? group : 'kinerja';
    const table = document.querySelector('.tpp-massal-table');

    document.querySelectorAll('[data-column-group]').forEach(cell => {
      const hidden = selectedGroup !== 'all' && cell.dataset.columnGroup !== selectedGroup;
      cell.classList.toggle('tpp-column-hidden', hidden);
    });

    document.querySelectorAll('[data-tpp-group]').forEach(button => {
      const active = button.dataset.tppGroup === selectedGroup;
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    document.querySelectorAll('[data-action-group]').forEach(button => {
      button.classList.toggle('d-none', selectedGroup !== 'all' && button.dataset.actionGroup !== selectedGroup);
    });

    if (table) table.dataset.activeGroup = selectedGroup;
    const help = document.getElementById('tppGroupHelp');
    if (help) help.innerHTML = tppGroupDescriptions[selectedGroup];

    if (remember) {
      try { window.localStorage.setItem('tpp-input-active-group', selectedGroup); } catch (error) {}
    }
  };

  document.querySelectorAll('[data-tpp-group]').forEach(button => {
    button.addEventListener('click', () => setActiveTppGroup(button.dataset.tppGroup));
  });

  let initialTppGroup = 'kinerja';
  try { initialTppGroup = window.localStorage.getItem('tpp-input-active-group') || 'kinerja'; } catch (error) {}
  setActiveTppGroup(initialTppGroup, false);

  const markEdited = (input) => input.classList.add('is-edited');

  const updatePotonganPreview = (input) => {
    const row = input.closest('tr');
    const target = row?.querySelector('.preview-potongan');
    if (!target) return;

    const raw = parseFloat(input.value);
    const potongan = Number.isFinite(raw) ? Math.min(100, Math.max(0, raw)) : 0;
    const efektif = 100 - potongan;
    target.textContent = `${efektif.toFixed(2)}%`;
  };

  document.querySelectorAll('.inp-potongan').forEach(el => {
    updatePotonganPreview(el);
    el.addEventListener('input', () => updatePotonganPreview(el));
    el.addEventListener('change', () => updatePotonganPreview(el));
  });

  document.querySelectorAll('.tpp-massal-table input').forEach(input => {
    input.addEventListener('input', () => markEdited(input));
    input.addEventListener('change', () => markEdited(input));
  });

  const setMassalValue = (selector, value) => {
    document.querySelectorAll(selector).forEach(input => {
      input.value = value;
      markEdited(input);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  };

  document.getElementById('btnSet100')?.addEventListener('click', () => {
    setMassalValue('.inp-prod,.inp-keh,.inp-per', 100);
  });
  document.getElementById('btnSetBpjs0')?.addEventListener('click', () => {
    setMassalValue('.inp-bpjs', 0);
  });
  document.getElementById('btnSetBpjs4Zero')?.addEventListener('click', () => {
    setMassalValue('.inp-bpjs-4', 0);
  });
  document.getElementById('btnSetPotongan0')?.addEventListener('click', () => {
    setMassalValue('.inp-potongan', 0);
  });
  document.getElementById('btnSetSipd0')?.addEventListener('click', () => {
    setMassalValue('.inp-sipd', 0);
  });

  const searchInput = document.getElementById('pegawaiSearchInput');
  const searchableRows = Array.from(document.querySelectorAll('[data-pegawai-row]'));
  const visibleCount = document.getElementById('pegawaiVisibleCount');

  const runPegawaiFilter = () => {
    const keyword = (searchInput?.value || '').trim().toLowerCase();
    let visible = 0;

    searchableRows.forEach(row => {
      const haystack = row.getAttribute('data-search-text') || '';
      const matched = !keyword || haystack.includes(keyword);
      row.classList.toggle('tpp-row-hidden', !matched);
      if (matched) visible += 1;
    });

    if (visibleCount) {
      visibleCount.textContent = visible;
    }
  };

  searchInput?.addEventListener('input', runPegawaiFilter);
  runPegawaiFilter();

  document.getElementById('btnPilihSemuaPajakCreate')?.addEventListener('click', () => {
    document.querySelectorAll('.hitung-pajak-item').forEach(el => {
      el.checked = true;
      markEdited(el);
    });
  });

  document.getElementById('btnKosongkanSemuaPajakCreate')?.addEventListener('click', () => {
    document.querySelectorAll('.hitung-pajak-item').forEach(el => {
      el.checked = false;
      markEdited(el);
    });
  });

  const tppMassalForm = document.getElementById('tppMassalForm');

  tppMassalForm?.addEventListener('invalid', event => {
    const group = event.target.closest('[data-column-group]')?.dataset.columnGroup;
    if (group) setActiveTppGroup(group);
  }, true);

  tppMassalForm?.addEventListener('submit', event => {
    const invalidInput = event.currentTarget.querySelector(':invalid');
    if (!invalidInput) return;

    event.preventDefault();
    const group = invalidInput.closest('[data-column-group]')?.dataset.columnGroup;
    if (group) setActiveTppGroup(group);
    window.setTimeout(() => {
      invalidInput.focus({ preventScroll: false });
      invalidInput.reportValidity();
    }, 0);
  });
</script>
@endpush

@endsection
