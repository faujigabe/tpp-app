@extends('layouts.main')
@section('title', 'Dashboard Utama')

@section('content')
@php
    $bulanList = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $completionRate = $totalPegawai > 0 ? round(($jumlahPerhitungan / $totalPegawai) * 100) : 0;
    $avgDiterima = $jumlahPerhitungan > 0 ? $rataDiterima : 0;
    $viewerMode = $viewerMode ?? false;
    $viewerNeedsPegawaiMapping = $viewerNeedsPegawaiMapping ?? false;
    $viewerHasPhoto = !empty($viewerPegawai?->foto_profil) || !empty(auth()->user()?->foto_profil);
    $viewerFotoProfilStamp = $viewerPegawai?->updated_at ?: auth()->user()?->updated_at ?: now();
    $viewerFotoProfilUrl = $viewerHasPhoto
        ? route('profile.photo') . '?v=' . urlencode((string) $viewerFotoProfilStamp)
        : null;
    $activeUnitKerjaName = $activeUnitKerja?->nama_unit ?? 'Semua Unit Kerja';
    $dashboardRole = auth()->user()?->role;
    $dashboardRoleContent = match ($dashboardRole) {
        'super_admin' => [
            'label' => 'Mode Super Admin',
            'title' => 'Pusat Pengawasan TPP Pemerintah Daerah',
            'description' => 'Pantau progres seluruh unit kerja, tindak lanjuti periode yang diajukan, serta awasi jejak perubahan dan kesehatan backup.',
        ],
        'admin' => [
            'label' => 'Mode Admin Unit',
            'title' => 'Pengelolaan TPP Unit Kerja',
            'description' => 'Kelola pegawai, periksa kelengkapan perhitungan, kirim periode untuk validasi, dan siapkan laporan unit kerja.',
        ],
        'operator' => [
            'label' => 'Mode Operator',
            'title' => 'Penyelesaian Input TPP Periode Berjalan',
            'description' => 'Fokus pada pegawai yang belum dihitung, lengkapi nilai tiap komponen, dan pastikan data siap dikirim untuk validasi.',
        ],
        default => [
            'label' => 'Dashboard TPP',
            'title' => 'Ringkasan Pengelolaan TPP',
            'description' => 'Pantau progres dan ringkasan data TPP sesuai kewenangan akun Anda.',
        ],
    };
@endphp

@if($viewerMode)

<style>
    .viewer-hero-head { align-items: stretch !important; }
    .viewer-avatar-hero {
        width: 132px;
        min-width: 132px;
        height: 132px;
        border-radius: 28px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
        font-size: 2.8rem;
        font-weight: 700;
        line-height: 1;
    }
    .viewer-avatar-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    @media (max-width: 767.98px) {
        .viewer-hero-head {
            align-items: center !important;
        }
        .viewer-avatar-hero {
            width: 96px;
            min-width: 96px;
            height: 96px;
            border-radius: 22px;
            font-size: 2rem;
        }
    }
</style>
<div class="viewer-shell">
    <div class="viewer-hero app-card overflow-hidden mb-4">
        <div class="p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-xl-8">
                    <div class="d-flex align-items-center gap-4 mb-3 viewer-hero-head">
                        <div class="viewer-avatar-lg viewer-avatar-hero">
                            @if($viewerFotoProfilUrl)
                                <img src="{{ $viewerFotoProfilUrl }}" alt="Foto Profil {{ $viewerPegawai?->nama ?? auth()->user()->name }}">
                            @else
                                {{ strtoupper(substr(trim($viewerPegawai?->nama ?? auth()->user()->name), 0, 1)) }}{{ strtoupper(substr(trim(explode(' ', trim($viewerPegawai?->nama ?? auth()->user()->name))[1] ?? ''), 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <span class="hero-chip mb-2 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-person-badge"></i>
                                Dashboard Pegawai
                            </span>
                            <h2 class="text-white mb-1">{{ $viewerPegawai?->nama ?? auth()->user()->name }}</h2>
                            <p class="hero-text mb-0">
                                {{ $viewerPegawai?->nip ? 'NIP: ' . $viewerPegawai->nip : 'Akun pegawai belum terhubung sepenuhnya' }}
                            </p>
                        </div>
                    </div>
                    <p class="hero-text mb-4">Pantau rekap TPP pribadi per periode, total potongan, dan riwayat penerimaan Anda dengan tampilan yang lebih ringkas dan mudah dibaca.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-light btn-hero" href="{{ route('tpp.index') }}"><i class="bi bi-wallet2 me-2"></i>Riwayat TPP Saya</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle me-2"></i>Profil Saya</a>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="viewer-profile-card">
                        <div class="viewer-profile-row">
                            <span>Periode aktif</span>
                            <strong>{{ $bulanList[$bulan] ?? $bulan }} {{ $tahun }}</strong>
                        </div>
                        <div class="viewer-profile-row">
                            <span>Status akun</span>
                            <strong>{{ $viewerNeedsPegawaiMapping ? 'Perlu ditautkan' : 'Aktif' }}</strong>
                        </div>
                        <div class="viewer-profile-row">
                            <span>Role</span>
                            <strong>Pegawai</strong>
                        </div>
                        <div class="viewer-profile-row border-0 pb-0">
                            <span>Email</span>
                            <strong class="text-break">{{ auth()->user()->email }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($viewerNeedsPegawaiMapping)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            Akun pegawai ini belum dihubungkan ke data pegawai. Hubungkan akun ini pada menu <strong>Manajemen User</strong> agar riwayat TPP pribadi bisa tampil dengan benar.
        </div>
    @endif

    <div class="app-card p-4 mb-4 filter-panel">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
            <div>
                <h5 class="mb-1">Filter periode TPP pribadi</h5>
                <p class="text-muted mb-0">Tampilan default menggunakan bulan sebelumnya. Anda tetap bisa melihat periode lain kapan saja.</p>
            </div>
            <div class="filter-badge"><i class="bi bi-shield-lock"></i> Hanya menampilkan data milik Anda</div>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label fw-semibold">Bulan</label>
                <select class="form-select" name="bulan">
                    @foreach($bulanList as $key => $label)
                        <option value="{{ $key }}" {{ (int) $bulan === (int) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label class="form-label fw-semibold">Tahun</label>
                <input type="number" class="form-control" name="tahun" value="{{ $tahun }}">
            </div>
            <div class="col-md-4 col-lg-4 d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-2"></i>Tampilkan</button>
                <a class="btn btn-light border" href="{{ route('dashboard') }}"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-primary h-100">
                <div class="metric-icon"><i class="bi bi-graph-up"></i></div>
                <div>
                    <div class="metric-label">Produktivitas</div>
                    <div class="metric-value">{{ number_format((float) ($viewerSelectedTpp->produktivitas ?? $viewerAverageProduktivitas), 2, ',', '.') }}%</div>
                    <div class="metric-note">{{ $viewerSelectedTpp ? 'Periode filter terpilih' : 'Rata-rata seluruh riwayat' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-success h-100">
                <div class="metric-icon"><i class="bi bi-calendar2-check"></i></div>
                <div>
                    <div class="metric-label">Kehadiran</div>
                    <div class="metric-value">{{ number_format((float) ($viewerSelectedTpp->kehadiran ?? $viewerAverageKehadiran), 2, ',', '.') }}%</div>
                    <div class="metric-note">{{ $viewerSelectedTpp ? 'Periode filter terpilih' : 'Rata-rata seluruh riwayat' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-warning h-100">
                <div class="metric-icon"><i class="bi bi-person-hearts"></i></div>
                <div>
                    <div class="metric-label">Perilaku</div>
                    <div class="metric-value">{{ number_format((float) ($viewerSelectedTpp->perilaku ?? $viewerAveragePerilaku), 2, ',', '.') }}%</div>
                    <div class="metric-note">{{ $viewerSelectedTpp ? 'Periode filter terpilih' : 'Rata-rata seluruh riwayat' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-danger h-100">
                <div class="metric-icon"><i class="bi bi-person-vcard"></i></div>
                <div>
                    <div class="metric-label">Profil Lengkap</div>
                    <div class="metric-value">{{ $viewerProfileCompletion }}%</div>
                    <div class="metric-note">{{ $viewerProfileFieldsFilled }}/{{ $viewerProfileFieldsTotal }} data pribadi terisi</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-primary h-100">
                <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="metric-label">TPP Diterima</div>
                    <div class="metric-value">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</div>
                    <div class="metric-note">Periode {{ $bulanList[$bulan] ?? $bulan }} {{ $tahun }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-success h-100">
                <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="metric-label">TPP Kotor</div>
                    <div class="metric-value">Rp {{ number_format($totalTppKotor, 0, ',', '.') }}</div>
                    <div class="metric-note">Sebelum potongan</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-warning h-100">
                <div class="metric-icon"><i class="bi bi-shield-minus"></i></div>
                <div>
                    <div class="metric-label">Iuran Wajib</div>
                    <div class="metric-value">Rp {{ number_format($totalBpjs, 0, ',', '.') }}</div>
                    <div class="metric-note">Potongan BPJS</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="app-card metric-card metric-card-danger h-100">
                <div class="metric-icon"><i class="bi bi-receipt-cutoff"></i></div>
                <div>
                    <div class="metric-label">Pajak + Zakat</div>
                    <div class="metric-value">Rp {{ number_format($totalPajak + $totalZakat, 0, ',', '.') }}</div>
                    <div class="metric-note">Total potongan lain</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="app-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">Grafik penerimaan TPP</h5>
                        <p class="text-muted mb-0">Total TPP diterima Anda sepanjang tahun {{ $tahun }}.</p>
                    </div>
                    <span class="chart-badge"><i class="bi bi-graph-up-arrow"></i> Riwayat tahunan</span>
                </div>
                <div class="viewer-chart-wrap">
                    <canvas id="tppTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="app-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Ringkasan profil & periode terbaru</h5>
                        <p class="text-muted mb-0">Ringkasan cepat yang paling sering dibutuhkan pegawai.</p>
                    </div>
                    <span class="viewer-mini-badge"><i class="bi bi-person-check"></i> Personal</span>
                </div>
                <div class="viewer-info-list">
                    <div class="viewer-info-item">
                        <span>Nama Pegawai</span>
                        <strong>{{ $viewerPegawai?->nama ?? auth()->user()->name }}</strong>
                    </div>
                    <div class="viewer-info-item">
                        <span>NIP</span>
                        <strong>{{ $viewerPegawai?->nip ?? 'Belum tersedia' }}</strong>
                    </div>
                    <div class="viewer-info-item">
                        <span>Periode TPP Terbaru</span>
                        <strong>
                            @if($viewerLatestTpp)
                                {{ $bulanList[(int) $viewerLatestTpp->bulan] ?? $viewerLatestTpp->bulan }} {{ $viewerLatestTpp->tahun }}
                            @else
                                Belum ada data
                            @endif
                        </strong>
                    </div>
                    <div class="viewer-info-item">
                        <span>TPP Diterima Terakhir</span>
                        <strong>
                            @if($viewerLatestTpp)
                                Rp {{ number_format((float) $viewerLatestTpp->total_diterima, 0, ',', '.') }}
                            @else
                                Belum ada data
                            @endif
                        </strong>
                    </div>
                    <div class="viewer-info-item">
                        <span>Kelengkapan Profil</span>
                        <div class="viewer-completion-wrap">
                            <strong>{{ $viewerProfileCompletion }}% lengkap</strong>
                            <div class="progress viewer-completion-progress" role="progressbar" aria-valuenow="{{ $viewerProfileCompletion }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $viewerProfileCompletion }}%"></div>
                            </div>
                            <small class="text-muted">{{ $viewerProfileFieldsFilled }} dari {{ $viewerProfileFieldsTotal }} data pribadi sudah terisi</small>
                        </div>
                    </div>
                </div>
                <div class="viewer-checklist mt-3">
                    @foreach($viewerProfileChecklist as $item)
                        <span class="viewer-check-item {{ $item['filled'] ? 'is-filled' : 'is-missing' }}">
                            <i class="bi {{ $item['filled'] ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
                <div class="mt-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-pencil-square me-2"></i>Kelola Profil Saya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 panel-card">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">3 periode terakhir</h5>
                        <small class="text-muted">Pantau tren produktivitas, kehadiran, perilaku, dan TPP terbaru Anda.</small>
                    </div>
                    <span class="badge text-bg-light">3 periode</span>
                </div>
                <div class="card-body pt-0">
                    <div class="viewer-period-list viewer-period-list-compact mb-3">
                        @forelse($viewerRecentPeriods as $periode)
                            <div class="viewer-period-item viewer-period-rich-item">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between gap-3 flex-wrap mb-2">
                                        <div>
                                            <div class="viewer-period-title">{{ $bulanList[(int) $periode->bulan] ?? $periode->bulan }} {{ $periode->tahun }}</div>
                                            <div class="viewer-period-note">TPP diterima Rp {{ number_format((float) $periode->total_diterima, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    <div class="viewer-score-grid">
                                        <div class="viewer-score-chip"><span>Produktivitas</span><strong>{{ number_format((float) $periode->produktivitas, 2, ',', '.') }}%</strong></div>
                                        <div class="viewer-score-chip"><span>Kehadiran</span><strong>{{ number_format((float) $periode->kehadiran, 2, ',', '.') }}%</strong></div>
                                        <div class="viewer-score-chip"><span>Perilaku</span><strong>{{ number_format((float) $periode->perilaku, 2, ',', '.') }}%</strong></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">Belum ada riwayat periode.</div>
                        @endforelse
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Rekap 6 periode terakhir</h6>
                                <small class="text-muted">Ringkasan singkat total penerimaan per periode.</small>
                            </div>
                            <span class="badge text-bg-light">6 periode</span>
                        </div>
                        <div class="viewer-period-list">
                            @forelse($periodeTerakhir as $periode)
                                <div class="viewer-period-item">
                                    <div>
                                        <div class="viewer-period-title">{{ $bulanList[(int) $periode->bulan] ?? $periode->bulan }} {{ $periode->tahun }}</div>
                                        <div class="viewer-period-note">{{ $periode->jumlah }} data perhitungan</div>
                                    </div>
                                    <div class="viewer-period-amount">Rp {{ number_format($periode->total, 0, ',', '.') }}</div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">Belum ada riwayat periode.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 panel-card">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Riwayat TPP pribadi</h5>
                        <small class="text-muted">5 data terakhir yang tercatat untuk akun Anda</small>
                    </div>
                    <a href="{{ route('tpp.index') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>TPP Kotor</th>
                                <th>TPP Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top5 as $item)
                                <tr>
                                    <td>{{ $bulanList[(int) $item->bulan] ?? $item->bulan }} {{ $item->tahun }}</td>
                                    <td>Rp {{ number_format($item->tpp_kotor, 0, ',', '.') }}</td>
                                    <td class="fw-semibold text-success">Rp {{ number_format($item->total_diterima, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada riwayat TPP.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else

@if(auth()->user()->isSuperAdmin() && isset($backupHealthSummary) && !$backupHealthSummary['healthy'])
    <div class="alert alert-danger border-0 shadow-sm d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4" role="alert">
        <div>
            <div class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Backup database memerlukan perhatian</div>
            <div class="small mt-1">Backup harian atau mingguan tidak ditemukan, tidak memiliki checksum, atau melewati batas usia yang ditetapkan.</div>
        </div>
        <a href="{{ route('backup-monitor.index') }}" class="btn btn-danger">Periksa Backup</a>
    </div>
@endif

<div class="dashboard-hero app-card overflow-hidden mb-4">
    <div class="p-4 p-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-xl-7">
                <span class="hero-chip mb-3 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-person-badge"></i>
                    {{ $dashboardRoleContent['label'] }} · {{ $bulanList[$bulan] ?? $bulan }} {{ $tahun }}
                </span>
                <h2 class="text-white mb-3">{{ $dashboardRoleContent['title'] }}</h2>
                <p class="hero-text mb-4">{{ $dashboardRoleContent['description'] }}</p>
                <div class="d-flex flex-wrap gap-2">
                    @if($dashboardRole === 'super_admin')
                        <a class="btn btn-light btn-hero" href="{{ route('tpp.index') }}"><i class="bi bi-clipboard2-check me-2"></i>Tinjau TPP</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('audit-logs.index') }}"><i class="bi bi-clock-history me-2"></i>Jejak Perubahan</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('backup-monitor.index') }}"><i class="bi bi-database-check me-2"></i>Monitoring Backup</a>
                    @elseif($dashboardRole === 'admin')
                        <a class="btn btn-light btn-hero" href="{{ route('tpp.create', ['bulan' => $bulan, 'tahun' => $tahun]) }}"><i class="bi bi-calculator me-2"></i>Input TPP</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('tpp.rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}"><i class="bi bi-file-earmark-bar-graph me-2"></i>Rekap TPP</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('pegawai.index') }}"><i class="bi bi-people me-2"></i>Data Pegawai</a>
                    @else
                        <a class="btn btn-light btn-hero" href="{{ route('tpp.create', ['bulan' => $bulan, 'tahun' => $tahun]) }}"><i class="bi bi-calculator me-2"></i>Input TPP</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('tpp.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"><i class="bi bi-wallet2 me-2"></i>Daftar TPP</a>
                        <a class="btn btn-outline-light btn-hero-outline" href="{{ route('pegawai.index') }}"><i class="bi bi-people me-2"></i>Data Pegawai</a>
                    @endif
                </div>
            </div>
            <div class="col-xl-5">
                <div class="hero-summary-grid">
                    <div class="hero-summary-card glass-card">
                        <div class="hero-summary-label">Total TPP Diterima</div>
                        <div class="hero-summary-value">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</div>
                        <small>Akumulasi bersih periode terpilih</small>
                    </div>
                    <div class="hero-summary-card glass-card">
                        <div class="hero-summary-label">Rata-rata Diterima</div>
                        <div class="hero-summary-value">Rp {{ number_format($avgDiterima, 0, ',', '.') }}</div>
                        <small>Per pegawai yang sudah dihitung</small>
                    </div>
                    <div class="hero-summary-card glass-card wide-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="hero-summary-label">Progress Perhitungan</div>
                                <div class="hero-summary-value">{{ $completionRate }}%</div>
                            </div>
                            <span class="hero-mini-badge">{{ number_format($jumlahPerhitungan, 0, ',', '.') }} / {{ number_format($totalPegawai, 0, ',', '.') }}</span>
                        </div>
                        <div class="progress hero-progress" role="progressbar" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $completionRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-card p-4 mb-4 filter-panel">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-1">Filter dashboard {{ $dashboardRole === 'super_admin' ? 'lintas unit' : 'unit kerja' }}</h5>
            <p class="text-muted mb-0">Pilih periode{{ $dashboardRole === 'super_admin' ? ' dan unit kerja' : '' }} yang ingin dipantau. Default menggunakan bulan sebelumnya.</p>
            @unless($viewerMode)
                <small class="text-muted d-block mt-1">Ruang lingkup data: <span class="fw-semibold">{{ $activeUnitKerjaName }}</span></small>
            @endunless
        </div>
        <div class="filter-badge"><i class="bi bi-funnel"></i> Default periode: bulan sebelumnya</div>
    </div>
    <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
        <div class="col-md-4 col-lg-3">
            <label class="form-label fw-semibold">Bulan</label>
            <select class="form-select" name="bulan">
                @foreach($bulanList as $key => $label)
                    <option value="{{ $key }}" {{ (int) $bulan === (int) $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label fw-semibold">Tahun</label>
            <input type="number" class="form-control" name="tahun" value="{{ $tahun }}">
        </div>
        @if(auth()->user()->isSuperAdmin())
            <div class="col-md-4 col-lg-4">
                <label class="form-label fw-semibold">Unit Kerja</label>
                <select class="form-select" name="unit_kerja_id">
                    <option value="">Semua Unit Kerja</option>
                    @foreach(($availableUnitKerjas ?? collect()) as $unit)
                        <option value="{{ $unit->id }}" {{ (int) ($selectedUnitKerjaId ?? 0) === (int) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3 d-flex flex-wrap gap-2">
        @else
            <div class="col-md-4 col-lg-4 d-flex flex-wrap gap-2">
        @endif
            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-2"></i>Tampilkan Dashboard</button>
            <a class="btn btn-light border" href="{{ route('dashboard') }}"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="app-card metric-card metric-card-primary h-100">
            <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="metric-label">Total Pegawai</div>
                <div class="metric-value">{{ number_format($totalPegawai, 0, ',', '.') }}</div>
                <div class="metric-note">Seluruh pegawai terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card metric-card metric-card-success h-100">
            <div class="metric-icon"><i class="bi bi-calculator-fill"></i></div>
            <div>
                <div class="metric-label">Perhitungan Aktif</div>
                <div class="metric-value">{{ number_format($jumlahPerhitungan, 0, ',', '.') }}</div>
                <div class="metric-note">Sudah diproses pada periode ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card metric-card metric-card-warning h-100">
            <div class="metric-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="metric-label">Belum Dihitung</div>
                <div class="metric-value">{{ number_format($pegawaiBelumDihitung, 0, ',', '.') }}</div>
                <div class="metric-note">Perlu tindak lanjut perhitungan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="app-card metric-card metric-card-danger h-100">
            <div class="metric-icon"><i class="bi bi-diagram-3-fill"></i></div>
            <div>
                <div class="metric-label">Tanpa Kelas Jabatan</div>
                <div class="metric-value">{{ number_format($pegawaiTanpaKelas, 0, ',', '.') }}</div>
                <div class="metric-note">Butuh pemetaan kelas jabatan</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="app-card finance-card finance-primary h-100">
            <div class="finance-card-head">
                <div>
                    <div class="finance-label">Total TPP Kotor</div>
                    <div class="finance-value">Rp {{ number_format($totalTppKotor, 2, ',', '.') }}</div>
                </div>
                <span class="finance-icon"><i class="bi bi-cash-coin"></i></span>
            </div>
            <div class="finance-note">Nilai sebelum potongan pada periode terpilih.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="app-card finance-card finance-secondary h-100">
            <div class="finance-card-head">
                <div>
                    <div class="finance-label">BPJS Kesehatan 1%</div>
                    <div class="finance-value">Rp {{ number_format($totalBpjs, 2, ',', '.') }}</div>
                </div>
                <span class="finance-icon"><i class="bi bi-heart-pulse"></i></span>
            </div>
            <div class="finance-note">Potongan kesehatan yang terhitung otomatis.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="app-card finance-card finance-warning h-100">
            <div class="finance-card-head">
                <div>
                    <div class="finance-label">Pajak + Zakat</div>
                    <div class="finance-value">Rp {{ number_format($totalPajak + $totalZakat, 2, ',', '.') }}</div>
                </div>
                <span class="finance-icon"><i class="bi bi-receipt"></i></span>
            </div>
            <div class="finance-note">Akumulasi kewajiban yang memengaruhi TPP bersih.</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="app-card finance-card finance-success h-100">
            <div class="finance-card-head">
                <div>
                    <div class="finance-label">TPP Diterima</div>
                    <div class="finance-value">Rp {{ number_format($totalDiterima, 2, ',', '.') }}</div>
                </div>
                <span class="finance-icon"><i class="bi bi-wallet2"></i></span>
            </div>
            <div class="finance-note">Nominal bersih yang diterima pegawai pada periode ini.</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="app-card panel-card chart-card h-100">
            <div class="card-header px-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                <div>
                    <div class="fw-semibold">Grafik Total Diterima per Bulan</div>
                    <div class="small text-muted">Pergerakan nilai TPP bersih sepanjang tahun {{ $tahun }}.</div>
                </div>
                <span class="chart-badge"><i class="bi bi-bar-chart-line"></i> Monitoring Tahunan</span>
            </div>
            <div class="card-body px-4 pb-4">
                <div style="position: relative; height: 360px;">
                    <canvas id="chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="app-card panel-card h-100">
            <div class="card-header px-4">
                <div class="fw-semibold">{{ $dashboardRole === 'operator' ? 'Fokus Operator' : 'Distribusi User Aktif' }}</div>
                <div class="small text-muted">{{ $dashboardRole === 'operator' ? 'Prioritas pekerjaan untuk menyelesaikan periode terpilih.' : 'Pembagian akun berdasarkan peran sistem.' }}</div>
            </div>
            <div class="card-body px-4">
                @if($dashboardRole === 'operator')
                <div class="role-item">
                    <div>
                        <div class="role-title">Lengkapi perhitungan</div>
                        <small>Pegawai yang belum dihitung pada periode ini</small>
                    </div>
                    <span class="badge {{ $pegawaiBelumDihitung > 0 ? 'text-bg-warning' : 'text-bg-success' }} rounded-pill">{{ $pegawaiBelumDihitung }}</span>
                </div>
                <div class="role-item">
                    <div>
                        <div class="role-title">Periksa kelas jabatan</div>
                        <small>Pegawai yang belum memiliki kelas jabatan</small>
                    </div>
                    <span class="badge {{ $pegawaiTanpaKelas > 0 ? 'text-bg-danger' : 'text-bg-success' }} rounded-pill">{{ $pegawaiTanpaKelas }}</span>
                </div>
                <div class="role-item mb-0 pb-0 border-0">
                    <div>
                        <div class="role-title">Data siap dipantau</div>
                        <small>Buka daftar TPP untuk pemeriksaan akhir</small>
                    </div>
                    <a href="{{ route('tpp.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-outline-primary">Periksa</a>
                </div>
                @else
                <div class="role-item">
                    <div>
                        <div class="role-title">Admin</div>
                        <small>Mengelola pengguna dan laporan unit</small>
                    </div>
                    <span class="badge text-bg-primary rounded-pill">{{ $userAdmin }}</span>
                </div>
                <div class="role-item">
                    <div>
                        <div class="role-title">Operator</div>
                        <small>Menginput dan memproses TPP unit</small>
                    </div>
                    <span class="badge text-bg-success rounded-pill">{{ $userOperator }}</span>
                </div>
                <div class="role-item mb-0 pb-0 border-0">
                    <div>
                        <div class="role-title">Viewer</div>
                        <small>Melihat riwayat TPP pribadi</small>
                    </div>
                    <span class="badge text-bg-secondary rounded-pill">{{ $userViewer }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="app-card panel-card h-100">
            <div class="card-header px-4">
                <div class="fw-semibold">6 Periode Terakhir</div>
                <div class="small text-muted">Ringkasan jumlah data dan total diterima pada periode terbaru.</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Periode</th>
                                <th class="text-center">Jumlah Data</th>
                                <th class="text-end">Total Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periodeTerakhir as $periode)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $bulanList[(int) $periode->bulan] ?? $periode->bulan }} {{ $periode->tahun }}</div>
                                        <small class="text-muted">Periode penggajian dan rekap TPP</small>
                                    </td>
                                    <td class="text-center">{{ number_format($periode->jumlah, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($periode->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada riwayat perhitungan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, rgba(6,17,29,1) 0%, rgba(16,32,51,1) 45%, rgba(31,122,224,1) 100%);
        border-radius: 28px;
        position: relative;
    }
    .dashboard-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,.22), transparent 28%),
                    radial-gradient(circle at bottom left, rgba(255,255,255,.12), transparent 22%);
        pointer-events: none;
    }
    .dashboard-hero > div { position: relative; z-index: 1; }
    .hero-chip {
        padding: .65rem 1rem;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,.14);
        font-weight: 600;
    }
    .hero-text { color: rgba(255,255,255,.82); max-width: 720px; }
    .btn-hero, .btn-hero-outline { border-radius: 14px; padding: .8rem 1.1rem; font-weight: 600; }
    .btn-hero-outline { border: 1px solid rgba(255,255,255,.35); color: #fff; }
    .btn-hero-outline:hover { background: rgba(255,255,255,.12); color: #fff; }
    .hero-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .glass-card {
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 22px;
        padding: 1.2rem;
        color: #fff;
        backdrop-filter: blur(8px);
        box-shadow: none;
    }
    .wide-card { grid-column: 1 / -1; }
    .hero-summary-label { font-size: .86rem; color: rgba(255,255,255,.78); margin-bottom: .4rem; }
    .hero-summary-value { font-size: 1.55rem; font-weight: 700; line-height: 1.2; }
    .hero-mini-badge {
        font-size: .78rem;
        border-radius: 999px;
        padding: .45rem .7rem;
        background: rgba(255,255,255,.14);
        color: #fff;
    }
    .hero-progress {
        height: 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        overflow: hidden;
    }
    .hero-progress .progress-bar {
        background: linear-gradient(90deg, #fff 0%, #b6d8ff 100%);
        border-radius: 999px;
    }
    .filter-panel { border: 1px solid rgba(31,122,224,.08); }
    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .7rem 1rem;
        border-radius: 999px;
        background: rgba(31,122,224,.08);
        color: #165db2;
        font-size: .9rem;
        font-weight: 600;
    }
    .filter-panel,
    .metric-card,
    .finance-card,
    .panel-card {
        border: 1px solid rgba(15, 23, 42, .06);
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .dashboard-hero {
        border: 1px solid rgba(13, 38, 73, .12);
        box-shadow: 0 26px 60px rgba(6, 17, 29, .15);
    }
    .filter-panel:hover,
    .metric-card:hover,
    .finance-card:hover,
    .panel-card:hover {
        transform: translateY(-2px);
        border-color: rgba(31, 122, 224, .12);
        box-shadow: 0 20px 48px rgba(15, 23, 42, .10);
    }
    .metric-card,
    .finance-card {
        position: relative;
        overflow: hidden;
    }
    .metric-card::before,
    .finance-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: var(--card-accent, #1f7ae0);
    }
    .metric-card-primary { --card-accent: #1f7ae0; }
    .metric-card-success { --card-accent: #16a34a; }
    .metric-card-warning { --card-accent: #d97706; }
    .metric-card-danger { --card-accent: #dc2626; }
    .metric-card {
        padding: 1.35rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .metric-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        border: 1px solid rgba(15, 23, 42, .06);
    }
    .metric-card-primary .metric-icon {
        background: rgba(31, 122, 224, .12);
        color: #1f7ae0;
        border-color: rgba(31, 122, 224, .12);
    }
    .metric-card-success .metric-icon {
        background: rgba(22, 163, 74, .12);
        color: #15803d;
        border-color: rgba(22, 163, 74, .12);
    }
    .metric-card-warning .metric-icon {
        background: rgba(217, 119, 6, .12);
        color: #b45309;
        border-color: rgba(217, 119, 6, .12);
    }
    .metric-card-danger .metric-icon {
        background: rgba(220, 38, 38, .12);
        color: #b91c1c;
        border-color: rgba(220, 38, 38, .12);
    }
    .metric-label { color: #667085; font-size: .92rem; margin-bottom: .2rem; }
    .metric-value { font-size: 1.8rem; font-weight: 700; color: #101828; line-height: 1.15; }
    .metric-note { color: #98a2b3; font-size: .85rem; }
    .finance-card {
        padding: 1.35rem;
        border-radius: 20px;
        --card-accent: #1f7ae0;
    }
    .finance-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .7rem;
    }
    .finance-label { font-size: .9rem; color: #667085; margin-bottom: .45rem; }
    .finance-value { font-size: 1.15rem; font-weight: 700; color: #101828; line-height: 1.35; }
    .finance-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(15, 23, 42, .06);
        color: var(--card-accent);
    }
    .finance-note { color: #98a2b3; font-size: .84rem; line-height: 1.45; }
    .finance-primary {
        --card-accent: #1f7ae0;
        border-color: rgba(31,122,224,.12);
        background: linear-gradient(180deg,#fff 0%, #f5f9ff 100%);
    }
    .finance-secondary {
        --card-accent: #7c3aed;
        border-color: rgba(124,58,237,.12);
        background: linear-gradient(180deg,#fff 0%, #faf7ff 100%);
    }
    .finance-warning {
        --card-accent: #d97706;
        border-color: rgba(217,119,6,.14);
        background: linear-gradient(180deg,#fff 0%, #fffaf0 100%);
    }
    .finance-success {
        --card-accent: #15803d;
        border-color: rgba(21,128,61,.12);
        background: linear-gradient(180deg,#fff 0%, #f3fcf7 100%);
    }
    .panel-card {
        border: 1px solid rgba(15, 23, 42, .06);
    }
    .panel-card .card-header {
        padding-top: 1.2rem;
        padding-bottom: 1rem;
    }
    .chart-card .card-body {
        background: linear-gradient(180deg, rgba(245,248,252,.52) 0%, rgba(255,255,255,0) 100%);
    }
    .chart-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #165db2;
        font-size: .82rem;
        font-weight: 700;
        border: 1px solid rgba(31, 122, 224, .08);
    }
    .role-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #eef2f7;
    }
    .role-item .badge {
        min-width: 44px;
        padding: .55rem .75rem;
        font-weight: 700;
    }
    .role-title { font-weight: 600; color: #101828; margin-bottom: .15rem; }
    .panel-card .table-responsive {
        border: 0;
        border-top: 1px solid rgba(15, 23, 42, .06);
        border-radius: 0 0 18px 18px;
        background: transparent;
    }
    .viewer-shell { position: relative; }
    .viewer-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 48%, #2563eb 100%);
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,.08);
        box-shadow: 0 20px 50px rgba(15, 23, 42, .18);
    }
    .viewer-avatar-lg {
        width: 68px;
        height: 68px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.18);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
        flex-shrink: 0;
        overflow: hidden;
    }
    .viewer-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .viewer-profile-card {
        border-radius: 22px;
        padding: 1.15rem 1.25rem;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.14);
        backdrop-filter: blur(8px);
    }
    .viewer-profile-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .8rem 0;
        border-bottom: 1px solid rgba(255,255,255,.12);
        color: rgba(255,255,255,.92);
        font-size: .92rem;
    }
    .viewer-profile-row span { color: rgba(255,255,255,.72); }
    .viewer-chart-wrap {
        position: relative;
        height: 320px;
    }
    .viewer-mini-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .48rem .76rem;
        border-radius: 999px;
        background: #eef6ff;
        color: #165db2;
        font-size: .8rem;
        font-weight: 700;
    }
    .viewer-info-list {
        display: grid;
        gap: .85rem;
    }
    .viewer-info-item {
        display: flex;
        flex-direction: column;
        gap: .2rem;
        padding: .9rem 1rem;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, .06);
    }
    .viewer-info-item span {
        font-size: .82rem;
        color: #667085;
    }
    .viewer-info-item strong {
        color: #101828;
        font-size: .96rem;
        line-height: 1.4;
    }
    .viewer-period-list {
        display: grid;
        gap: .85rem;
    }
    .viewer-period-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        padding: 1rem 1.05rem;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, .06);
    }
    .viewer-period-title {
        font-weight: 700;
        color: #101828;
        margin-bottom: .15rem;
    }
    .viewer-period-note {
        color: #667085;
        font-size: .84rem;
    }
    .viewer-period-amount {
        font-weight: 800;
        color: #165db2;
        white-space: nowrap;
    }
    .viewer-completion-wrap {
        display: flex;
        flex-direction: column;
        gap: .45rem;
    }
    .viewer-completion-progress {
        height: 8px;
        border-radius: 999px;
        background: #e8eef7;
        overflow: hidden;
    }
    .viewer-completion-progress .progress-bar {
        background: linear-gradient(90deg, #2563eb 0%, #16a34a 100%);
        border-radius: 999px;
    }
    .viewer-checklist {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
    }
    .viewer-check-item {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        padding: .5rem .75rem;
        font-size: .79rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .viewer-check-item.is-filled {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
    }
    .viewer-check-item.is-missing {
        background: #fff7ed;
        color: #9a3412;
        border-color: #fed7aa;
    }
    .viewer-score-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .viewer-score-chip {
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, .06);
        padding: .8rem .9rem;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        align-items: center;
    }
    .viewer-score-chip span {
        color: #667085;
        font-size: .8rem;
    }
    .viewer-score-chip strong {
        color: #0f172a;
        font-size: .94rem;
    }
    .viewer-period-rich-item {
        align-items: stretch;
    }
    .viewer-period-list-compact {
        gap: 1rem;
    }
    @media (max-width: 991px) {
        .viewer-score-grid {
            grid-template-columns: 1fr;
        }
        .hero-summary-grid { grid-template-columns: 1fr; }
        .wide-card { grid-column: auto; }
        .viewer-profile-row,
        .viewer-period-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .viewer-chart-wrap { height: 260px; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels);
        const values = @json($values);
        const ctx = document.getElementById('chart') || document.getElementById('tppTrendChart');
        if (!ctx) return;
        function rupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n || 0); }
        if (window.tppDashboardChart instanceof Chart) window.tppDashboardChart.destroy();
        window.tppDashboardChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Diterima',
                    data: values,
                    borderWidth: 0,
                    borderRadius: 12,
                    maxBarThickness: 34,
                    backgroundColor: [
                        'rgba(15,44,77,.95)','rgba(18,58,100,.95)','rgba(21,72,123,.95)','rgba(24,86,146,.95)',
                        'rgba(27,100,169,.95)','rgba(30,114,192,.95)','rgba(31,122,224,.95)','rgba(52,136,228,.95)',
                        'rgba(73,150,232,.95)','rgba(94,164,236,.95)','rgba(115,178,240,.95)','rgba(136,192,244,.95)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#102033',
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (ctx) { return 'Total diterima: ' + rupiah(ctx.parsed.y); }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#475467', font: { weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#667085',
                            callback: function (value) { return rupiah(value); }
                        },
                        grid: { color: 'rgba(15, 23, 42, .06)' }
                    }
                }
            }
        });
    });
</script>
@endpush
