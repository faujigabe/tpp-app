@extends('layouts.main')

@section('title', 'Data TPP')

@section('content')
@php
    $viewerMode = $viewerMode ?? (Auth::check() && Auth::user()->role === 'viewer');
    $viewerNeedsPegawaiMapping = $viewerNeedsPegawaiMapping ?? false;
    $defaultPeriod = now()->startOfMonth()->subMonth();
    $bulan = $viewerMode ? request('bulan') : (request('bulan') ?? $defaultPeriod->month);
    $tahun = $viewerMode ? request('tahun') : (request('tahun') ?? $defaultPeriod->year);
    $search = $viewerMode ? null : request('search');
    $selectedUnitKerjaId = $selectedUnitKerjaId ?? (request('unit_kerja_id') ?: null);
    $activeUnitKerjaName = $activeUnitKerja->nama_unit ?? (Auth::user()->unitKerja->nama_unit ?? null);
    $isSuperAdmin = Auth::check() && Auth::user()->role === 'super_admin';
    $isUnitEditor = Auth::check() && in_array(Auth::user()->role, ['admin', 'operator'], true);
    $periodStatus = $periodStatus ?? ($periodApproval?->normalizedStatus() ?? \App\Models\TppApproval::STATUS_DRAFT);
    $periodCanEdit = $periodCanEdit ?? ($isUnitEditor && $periodStatus === \App\Models\TppApproval::STATUS_DRAFT);
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $rupiah = function($angka, $dec=2) {
        return 'Rp ' . number_format((float)$angka, $dec, ',', '.');
    };
    $jumlahDataFilter = $tpps->total();
    $periodeLabel = ($bulan && $tahun) ? (($bulanNama[(int)$bulan] ?? $bulan) . ' ' . $tahun) : 'Semua Periode';
    $periodReadiness = $periodReadiness ?? null;
    $periodReady = (bool) ($periodReadiness['ready'] ?? false);
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h3 class="mb-1">{{ $viewerMode ? 'Riwayat TPP Pribadi' : 'Daftar Hasil Perhitungan TPP' }}</h3>
        <div class="text-muted">{{ $viewerMode ? 'Tampilan ini hanya menampilkan seluruh riwayat TPP milik Anda sendiri. Gunakan filter bila ingin melihat periode tertentu.' : 'Kelola data TPP per periode, cari pegawai berdasarkan nama atau NIP, lalu kirim, cetak, atau export sesuai kebutuhan. Default tampilan menggunakan periode bulan sebelumnya.' }}</div>
    </div>

    @if(!$viewerMode && $isUnitEditor)
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('tpp.create', array_filter(['bulan' => $bulan, 'tahun' => $tahun, 'unit_kerja_id' => $selectedUnitKerjaId])) }}" class="btn btn-primary btn-icon">
            <i class="bi bi-plus-circle"></i> Tambah Perhitungan
        </a>
    </div>
    @endif
</div>

@if($viewerMode && $viewerNeedsPegawaiMapping)
    <div class="alert alert-warning">Akun viewer ini belum dihubungkan ke data pegawai. Hubungkan akun ke pegawai melalui menu Manajemen User agar riwayat TPP pribadi dapat tampil.</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@php
    $statusBadgeClass = \App\Models\TppApproval::badgeClassFor($periodStatus);
    $statusLabel = \App\Models\TppApproval::labelFor($periodStatus);
    $approvalNotes = trim((string) ($periodApproval->catatan ?? ''));
@endphp

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('tpp.index') }}" class="row g-3 align-items-end">
                    @unless($viewerMode)
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cari Pegawai</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Cari nama atau NIP">
                        </div>
                    </div>
                    @if($isSuperAdmin)
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Unit Kerja</label>
                        <select name="unit_kerja_id" class="form-select">
                            <option value="">Semua Unit Kerja</option>
                            @foreach(($availableUnitKerjas ?? collect()) as $unit)
                                <option value="{{ $unit->id }}" {{ (string) $selectedUnitKerjaId === (string) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    @endunless

                    <div class="{{ $viewerMode ? 'col-md-4' : ($isSuperAdmin ? 'col-md-2' : 'col-md-3') }}">
                        <label class="form-label fw-semibold">Bulan</label>
                        <select name="bulan" class="form-select">
                            @if($viewerMode)
                                <option value="">Semua Bulan</option>
                            @endif
                            @foreach($bulanNama as $k => $v)
                                <option value="{{ $k }}" {{ (string) $bulan === (string) $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="{{ $viewerMode ? 'col-md-3' : ($isSuperAdmin ? 'col-md-2' : 'col-md-2') }}">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="{{ $tahun }}">
                    </div>

                    <div class="{{ $viewerMode ? 'col-md-5' : ($isSuperAdmin ? 'col-md-3' : 'col-md-3') }} d-grid d-md-flex gap-2">
                        <button type="submit" class="btn btn-secondary btn-icon flex-fill">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('tpp.index') }}" class="btn btn-outline-secondary btn-icon flex-fill">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100 border-0 bg-light">
            <div class="card-body">
                @if(!$viewerMode)
                <div class="small text-muted text-uppercase fw-semibold mb-2">Ringkasan Filter</div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Periode</span>
                    <span class="fw-semibold">{{ $periodeLabel }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Data</span>
                    <span class="badge text-bg-primary">{{ number_format($jumlahDataFilter, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Pencarian</span>
                    <span class="fw-semibold text-truncate ms-3">{{ $search ?: 'Semua pegawai' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Unit Kerja</span>
                    <span class="fw-semibold text-truncate ms-3">{{ $isSuperAdmin ? ($activeUnitKerjaName ?: 'Semua Unit Kerja') : ($activeUnitKerjaName ?: 'Belum diatur') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Status Periode</span>
                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Siap Kirim WA</span>
                    <span class="fw-semibold text-success">{{ number_format($waValidCount ?? 0, 0, ',', '.') }} pegawai</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">No. HP Belum Ada</span>
                    <span class="fw-semibold text-muted">{{ number_format($waMissingCount ?? 0, 0, ',', '.') }} pegawai</span>
                </div>
                @if($approvalNotes !== '')
                <hr class="my-3">
                <div class="small text-muted mb-1">Riwayat Status Periode</div>
                <div class="small text-dark bg-white border rounded p-2" style="white-space: pre-line;">{{ $approvalNotes }}</div>
                @endif
                @else
                <div class="small text-muted text-uppercase fw-semibold mb-2">Ringkasan Akses Saya</div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Akses Data</span>
                    <span class="fw-semibold text-primary">TPP Pribadi</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Nama Pegawai</span>
                    <span class="fw-semibold text-truncate ms-3">{{ $viewerPegawai->nama ?? auth()->user()->name }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Filter</span>
                    <span class="fw-semibold text-muted">{{ $periodeLabel }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@unless($viewerMode)
@if($periodReadiness)
<div class="card shadow-sm mb-3 border-0 {{ $periodReady ? 'border-start border-success border-4' : 'border-start border-warning border-4' }}">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <div class="fw-semibold">Kesiapan Periode {{ $periodeLabel }}</div>
                    <span class="badge {{ $periodReady ? 'text-bg-success' : 'text-bg-warning' }}">{{ $periodReadiness['percentage'] }}% siap</span>
                </div>
                <p class="small text-muted mb-2">{{ $periodReadiness['message'] }}</p>
                <div class="progress" style="height: 8px;" role="progressbar" aria-valuenow="{{ $periodReadiness['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar {{ $periodReady ? 'bg-success' : 'bg-warning' }}" style="width: {{ $periodReadiness['percentage'] }}%"></div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-start">
                <div><span class="text-muted small d-block">Pegawai aktif</span><strong>{{ $periodReadiness['total'] }}</strong></div>
                <div><span class="text-muted small d-block">Sudah dihitung</span><strong>{{ $periodReadiness['calculated'] }}</strong></div>
                <div><span class="text-muted small d-block">Belum dihitung</span><strong class="{{ $periodReadiness['missing_tpp']->isNotEmpty() ? 'text-danger' : 'text-success' }}">{{ $periodReadiness['missing_tpp']->count() }}</strong></div>
                <div><span class="text-muted small d-block">Tanpa kelas</span><strong class="{{ $periodReadiness['missing_kelas']->isNotEmpty() ? 'text-danger' : 'text-success' }}">{{ $periodReadiness['missing_kelas']->count() }}</strong></div>
            </div>
        </div>

        @if(!$periodReady)
        <div class="row g-3 mt-1">
            @if($periodReadiness['missing_tpp']->isNotEmpty())
            <div class="col-lg-6">
                <div class="small fw-semibold mb-1">Belum memiliki rincian TPP</div>
                <div class="small text-muted">
                    {{ $periodReadiness['missing_tpp']->take(5)->map(fn ($pegawai) => $pegawai->nama . ' (' . $pegawai->nip . ')')->implode(', ') }}
                    @if($periodReadiness['missing_tpp']->count() > 5)
                        dan {{ $periodReadiness['missing_tpp']->count() - 5 }} pegawai lainnya.
                    @endif
                </div>
                @if($isUnitEditor)
                    <a href="{{ route('tpp.create', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-outline-primary mt-2">Lengkapi Input TPP</a>
                @endif
            </div>
            @endif
            @if($periodReadiness['missing_kelas']->isNotEmpty())
            <div class="col-lg-6">
                <div class="small fw-semibold mb-1">Belum memiliki kelas jabatan</div>
                <div class="small text-muted">
                    {{ $periodReadiness['missing_kelas']->take(5)->map(fn ($pegawai) => $pegawai->nama . ' (' . $pegawai->nip . ')')->implode(', ') }}
                    @if($periodReadiness['missing_kelas']->count() > 5)
                        dan {{ $periodReadiness['missing_kelas']->count() - 5 }} pegawai lainnya.
                    @endif
                </div>
                <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-outline-danger mt-2">Perbaiki Data Pegawai</a>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endif

<div class="card shadow-sm mb-3 border-0">
    <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="fw-semibold">Aksi Periode {{ $periodeLabel }}</div>
            <div class="text-muted small">Status periode saat ini: <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>. Admin/operator mengirim TPP untuk validasi, lalu super admin memvalidasi dan mengunci data jika sudah final.</div>
        </div>

        <div class="d-flex gap-2 flex-wrap justify-content-lg-end align-items-center">
            @if(!$viewerMode && $isUnitEditor && $periodStatus === \App\Models\TppApproval::STATUS_DRAFT)
                <form action="{{ route('tpp.submit-period') }}" method="POST" data-confirm data-confirm-title="Kirim TPP untuk validasi?" data-confirm-message="Periode {{ $periodeLabel }} akan dikirim. Data tidak dapat diedit sampai super admin membuka kembali periode." data-confirm-label="Kirim TPP" data-confirm-variant="primary">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="btn btn-primary" {{ !$periodReady ? 'disabled' : '' }} title="{{ !$periodReady ? ($periodReadiness['message'] ?? 'Periode belum siap.') : '' }}">
                        <i class="bi bi-send-check me-1"></i> Kirim TPP
                    </button>
                </form>
            @endif
            @if(!$viewerMode && $isSuperAdmin && $selectedUnitKerjaId && $periodStatus === \App\Models\TppApproval::STATUS_SUBMITTED)
                <form action="{{ route('tpp.lock-period') }}" method="POST" data-confirm data-confirm-title="Validasi dan kunci periode?" data-confirm-message="TPP {{ $periodeLabel }} untuk {{ $activeUnitKerjaName ?: 'unit kerja terpilih' }} akan dikunci. Admin dan operator tidak dapat mengubah data sampai kunci dibuka." data-confirm-label="Validasi & Kunci" data-confirm-variant="success">
                    @csrf
                    <input type="hidden" name="unit_kerja_id" value="{{ $selectedUnitKerjaId }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="btn btn-success" {{ !$periodReady ? 'disabled' : '' }} title="{{ !$periodReady ? ($periodReadiness['message'] ?? 'Periode belum siap.') : '' }}">
                        <i class="bi bi-lock-fill me-1"></i> Validasi &amp; Kunci TPP
                    </button>
                </form>
            @endif
            @if(!$viewerMode && $isSuperAdmin && $selectedUnitKerjaId && in_array($periodStatus, [\App\Models\TppApproval::STATUS_SUBMITTED, \App\Models\TppApproval::STATUS_LOCKED], true))
                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#bukaPeriodeModal">
                    <i class="bi bi-unlock-fill me-1"></i> Buka Kunci Validasi
                </button>
            @endif
            @if(!$viewerMode && !$isSuperAdmin && ($waValidCount ?? 0) > 0)
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#whatsappMassalModal">
                    <i class="bi bi-whatsapp me-1"></i> Kirim WA Massal
                </button>
            @endif

            @auth
                @if($isUnitEditor && $jumlahDataFilter > 0 && $periodStatus === \App\Models\TppApproval::STATUS_DRAFT)
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusMassalModal">
                        <i class="bi bi-trash3 me-1"></i> Hapus Massal
                    </button>
                @endif
            @endauth

            <a class="btn btn-outline-danger" href="{{ route('tpp.cetak', request()->all()) }}">
                <i class="bi bi-printer me-1"></i> Cetak PDF
            </a>
            <a class="btn btn-outline-success" href="{{ route('tpp.export', request()->all()) }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
            </a>
            @if(!$isSuperAdmin)
            <a class="btn btn-success" href="{{ route('tpp.export.whatsapp', request()->all()) }}">
                <i class="bi bi-file-earmark-text me-1"></i> Export Excel WA
            </a>
            @endif
        </div>
    </div>
</div>
@endunless

@if(!$viewerMode && $isSuperAdmin && $selectedUnitKerjaId && in_array($periodStatus, [\App\Models\TppApproval::STATUS_SUBMITTED, \App\Models\TppApproval::STATUS_LOCKED], true))
<div class="modal fade" id="bukaPeriodeModal" tabindex="-1" aria-labelledby="bukaPeriodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('tpp.unlock-period') }}" method="POST">
                @csrf
                <input type="hidden" name="unit_kerja_id" value="{{ $selectedUnitKerjaId }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="bukaPeriodeModalLabel">Buka Kembali Periode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">Periode {{ $periodeLabel }} akan kembali menjadi Draft dan dapat diedit oleh Admin/Operator.</div>
                    <label for="alasanBukaPeriode" class="form-label fw-semibold">Alasan pembukaan periode</label>
                    <textarea id="alasanBukaPeriode" name="alasan" class="form-control" rows="4" minlength="10" maxlength="500" required placeholder="Contoh: Perbaikan data kehadiran pegawai berdasarkan hasil verifikasi.">{{ old('alasan') }}</textarea>
                    <div class="form-text">Wajib diisi minimal 10 karakter dan akan disimpan dalam riwayat status.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-unlock-fill me-1"></i>Buka Periode</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(($waValidCount ?? 0) === 0 && $jumlahDataFilter > 0 && !$viewerMode && !$isSuperAdmin)
    <div class="alert alert-light border text-muted">
        Tidak ada nomor WhatsApp yang siap dipakai pada data TPP periode ini. Isi <strong>No. HP</strong> di menu Pegawai agar fitur kirim WhatsApp bisa digunakan.
    </div>
@endif

@if(!$viewerMode && Auth::check() && $isUnitEditor && $jumlahDataFilter === 0)
    <div class="alert alert-light border text-muted">
        Tombol <strong>Hapus Massal</strong> disembunyikan karena tidak ada data TPP untuk filter yang dipilih.
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 pt-3 pb-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
            <div>
                <h5 class="mb-1">Tabel Data TPP</h5>
                <div class="text-muted small">Menampilkan {{ $tpps->count() }} data pada halaman ini dari total {{ number_format($jumlahDataFilter, 0, ',', '.') }} data.</div>
            </div>
            <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2">{{ $periodeLabel }}</span>
        </div>
    </div>

    <div class="card-body pt-3">
        <div class="table-responsive mobile-card-table-wrap">
            <table class="table table-hover align-middle mb-0 mobile-card-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" data-mobile-label="Nomor">#</th>
                        <th>Pegawai</th>
                        @if($isSuperAdmin && !$viewerMode)
                            <th style="width: 210px;">Unit Kerja</th>
                        @endif
                        <th style="width: 150px;">Periode</th>
                        @if($viewerMode)
                            <th class="text-end">Produktifitas</th>
                            <th class="text-end">Kehadiran</th>
                            <th class="text-end">Perilaku</th>
                        @endif
                        <th class="text-end">Tambahan TPP</th>
                        <th class="text-end">Potongan TPP</th>
                        <th class="text-end">TPP Kotor</th>
                        <th class="text-end">BPJS 1%</th>
                        <th class="text-end">Pajak</th>
                        <th class="text-end">Zakat</th>
                        <th class="text-end">Total Diterima</th>
                        @unless($viewerMode)<th class="text-center" style="width: 220px;">Aksi</th>@endunless
                    </tr>
                </thead>
                <tbody>
                    @forelse($tpps as $index => $tpp)
                        <tr>
                            <td class="text-muted">{{ $tpps->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $tpp->referensi_nama ?: '-' }}</div>
                                <div class="small text-muted">NIP: {{ $tpp->referensi_nip ?: '-' }}</div>
                                <div class="small text-muted">No. HP: {{ $tpp->nomor_whatsapp ?: '-' }}</div>
                            </td>
                            @if($isSuperAdmin && !$viewerMode)
                                <td>
                                    <div class="fw-semibold text-dark">{{ $tpp->pegawai->unitKerja->nama_unit ?? $tpp->snapshot('unit_kerja.nama_unit') ?? $tpp->unitKerja->nama_unit ?? '-' }}</div>
                                    <div class="small text-muted">Ruang lingkup data</div>
                                </td>
                            @endif
                            <td>
                                <span class="badge rounded-pill text-bg-light border text-dark">
                                    {{ $bulanNama[(int) $tpp->bulan] ?? $tpp->bulan }} {{ $tpp->tahun }}
                                </span>
                            </td>
                            @if($viewerMode)
                                <td class="text-end">{{ number_format((float) ($tpp->produktivitas ?? 0), 2, ',', '.') }}%</td>
                                <td class="text-end">{{ number_format((float) ($tpp->kehadiran ?? 0), 2, ',', '.') }}%</td>
                                <td class="text-end">{{ number_format((float) ($tpp->perilaku ?? 0), 2, ',', '.') }}%</td>
                            @endif
                            <td class="text-end">{{ $rupiah($tpp->tambahan_tpp ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format((float)($tpp->potongan_tpp ?? 0), 2, ',', '.') }}%</td>
                            <td class="text-end">{{ $rupiah($tpp->tpp_kotor, 2) }}</td>
                            <td class="text-end">{{ $rupiah($tpp->iuran_wajib, 2) }}</td>
                            <td class="text-end">{{ $rupiah($tpp->pajak, 2) }}</td>
                            <td class="text-end">{{ $rupiah($tpp->zakat, 2) }}</td>
                            <td class="text-end fw-semibold text-success">{{ $rupiah($tpp->total_diterima, 2) }}</td>
                            @unless($viewerMode)
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    @if(!$isSuperAdmin)
                                        @if(!empty($tpp->wa_link))
                                            <a href="{{ $tpp->wa_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Kirim WhatsApp">
                                                <i class="bi bi-whatsapp me-1"></i> WA
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No. HP pegawai belum tersedia">
                                                <i class="bi bi-whatsapp me-1"></i> WA
                                            </button>
                                        @endif
                                    @endif
                                    @if($periodCanEdit)
                                    <a href="{{ route('tpp.edit', $tpp->id) }}" class="btn btn-sm btn-warning btn-icon" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('tpp.destroy', $tpp->id) }}" method="POST" data-confirm data-confirm-title="Hapus data TPP?" data-confirm-message="Data TPP {{ $tpp->pegawai->nama ?? 'pegawai ini' }} untuk periode {{ $bulanNama[(int) $tpp->bulan] ?? $tpp->bulan }} {{ $tpp->tahun }} akan dihapus dan tidak lagi muncul dalam rekap." data-confirm-label="Hapus TPP" data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @else
                                        <span class="badge rounded-pill {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                    @endif
                                </div>
                            </td>
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $viewerMode ? 13 : 11 }}" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data TPP yang cocok dengan filter atau pencarian yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white border-0 pt-0">
        {{ $tpps->links() }}
    </div>
</div>

@if(!$viewerMode && ($waValidCount ?? 0) > 0)
<div class="modal fade" id="whatsappMassalModal" tabindex="-1" aria-labelledby="whatsappMassalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="whatsappMassalModalLabel">Kirim WhatsApp Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-whatsapp fs-5 mt-1"></i>
                    <div>
                        <div class="fw-semibold">Pesan akan dibuka ke WhatsApp sesuai data periode terpilih.</div>
                        <div class="small mb-0">Gunakan tombol kirim massal untuk membuka chat pegawai yang memiliki nomor HP.</div>
                    </div>
                </div>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="small text-muted mb-2">Ringkasan pengiriman</div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Periode</span>
                        <strong>{{ $periodeLabel }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Siap dikirim</span>
                        <strong class="text-success">{{ number_format($waValidCount ?? 0, 0, ',', '.') }} pegawai</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>No. HP belum tersedia</span>
                        <strong class="text-muted">{{ number_format($waMissingCount ?? 0, 0, ',', '.') }} pegawai</strong>
                    </div>
                </div>

                <div class="small text-muted">
                    Browser dapat memblokir sebagian tab jika terlalu banyak chat dibuka sekaligus. Jika ada yang belum terbuka, gunakan tombol WA pada baris pegawai terkait.
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="small text-muted">Pengiriman mengikuti filter bulan, tahun, dan pencarian yang sedang aktif.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKirimWhatsappMassal">
                        <i class="bi bi-whatsapp me-1"></i> Buka Semua Chat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(!$viewerMode && Auth::check() && $isUnitEditor && $jumlahDataFilter > 0 && $periodStatus === \App\Models\TppApproval::STATUS_DRAFT)
<div class="modal fade" id="hapusMassalModal" tabindex="-1" aria-labelledby="hapusMassalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="hapusMassalModalLabel">Konfirmasi Hapus Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                    <div>
                        <div class="fw-semibold">Tindakan ini permanen dan tidak bisa dibatalkan.</div>
                        <div class="small mb-0">Pastikan periode yang dipilih sudah benar sebelum melanjutkan.</div>
                    </div>
                </div>

                <div class="border rounded p-3 bg-light mb-3">
                    <div class="small text-muted mb-2">Ringkasan penghapusan</div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Periode</span>
                        <strong>{{ $periodeLabel }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Jumlah data yang akan dihapus</span>
                        <strong class="text-danger">{{ number_format($jumlahDataFilter, 0, ',', '.') }} data</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="konfirmasiHapusInput" class="form-label fw-semibold">Ketik HAPUS</label>
                    <input type="text" class="form-control" id="konfirmasiHapusInput" placeholder="HAPUS" autocomplete="off">
                    <div class="form-text">Tombol hapus akan aktif setelah Anda mengetik HAPUS dengan benar.</div>
                </div>

                <div class="mb-1">
                    <label for="passwordKonfirmasiInput" class="form-label fw-semibold">Masukkan password akun Anda</label>
                    <input type="password" class="form-control" id="passwordKonfirmasiInput" placeholder="Password akun" autocomplete="current-password">
                    <div class="form-text">Sebagai verifikasi terakhir sebelum semua data periode ini dihapus.</div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="small text-muted">Data yang sudah dihapus tidak dapat dipulihkan.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusMassal" disabled>Ya, Hapus Semua</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="formHapusMassal" action="{{ route('tpp.destroy.massal') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="bulan" value="{{ $bulan }}">
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="konfirmasi_hapus" id="konfirmasiHapusValue">
    <input type="hidden" name="password_konfirmasi" id="passwordKonfirmasiValue">
</form>
@endif
@endsection

@push('scripts')
@if(!$viewerMode && ($waValidCount ?? 0) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const links = @json(($massWhatsappItems ?? collect())->pluck('link')->values()->all());
    const btnKirimMassal = document.getElementById('btnKirimWhatsappMassal');
    const modalWhatsapp = document.getElementById('whatsappMassalModal');

    if (!btnKirimMassal || !Array.isArray(links) || links.length === 0) {
        return;
    }

    btnKirimMassal.addEventListener('click', function () {
        links.forEach(function (link, index) {
            setTimeout(function () {
                window.open(link, '_blank', 'noopener');
            }, index * 450);
        });

        if (modalWhatsapp && window.bootstrap) {
            const instance = bootstrap.Modal.getInstance(modalWhatsapp);
            if (instance) {
                instance.hide();
            }
        }
    });
});
</script>
@endif

@if(!$viewerMode && Auth::check() && $isUnitEditor && $jumlahDataFilter > 0 && $periodStatus === \App\Models\TppApproval::STATUS_DRAFT)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formHapusMassal = document.getElementById('formHapusMassal');
    const inputKonfirmasi = document.getElementById('konfirmasiHapusInput');
    const hiddenKonfirmasi = document.getElementById('konfirmasiHapusValue');
    const inputPassword = document.getElementById('passwordKonfirmasiInput');
    const hiddenPassword = document.getElementById('passwordKonfirmasiValue');
    const tombolKonfirmasi = document.getElementById('btnKonfirmasiHapusMassal');
    const modalElement = document.getElementById('hapusMassalModal');

    if (!formHapusMassal || !inputKonfirmasi || !hiddenKonfirmasi || !inputPassword || !hiddenPassword || !tombolKonfirmasi || !modalElement) {
        return;
    }

    const sinkronkanStatusTombol = function () {
        const nilai = inputKonfirmasi.value.trim().toUpperCase();
        const passwordTerisi = inputPassword.value.trim().length > 0;
        tombolKonfirmasi.disabled = nilai !== 'HAPUS' || !passwordTerisi;
    };

    const resetKonfirmasi = function () {
        inputKonfirmasi.value = '';
        inputPassword.value = '';
        hiddenKonfirmasi.value = '';
        hiddenPassword.value = '';
        tombolKonfirmasi.disabled = true;
    };

    inputKonfirmasi.addEventListener('input', sinkronkanStatusTombol);
    inputPassword.addEventListener('input', sinkronkanStatusTombol);

    tombolKonfirmasi.addEventListener('click', function () {
        hiddenKonfirmasi.value = inputKonfirmasi.value.trim().toUpperCase();
        hiddenPassword.value = inputPassword.value.trim();
        formHapusMassal.submit();
    });

    modalElement.addEventListener('hidden.bs.modal', resetKonfirmasi);
});
</script>
@endif
@endpush
