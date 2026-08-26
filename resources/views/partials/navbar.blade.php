@php
    use Illuminate\Support\Str;

    $isDashboard = request()->routeIs('dashboard');
    $isTppList = request()->routeIs('tpp.index');
    $isTppInput = request()->routeIs('tpp.create') || request()->routeIs('tpp.store');
    $isTppEditItem = request()->routeIs('tpp.edit') || request()->routeIs('tpp.update') || request()->routeIs('tpp.destroy');
    $isTppGroup = $isTppList || $isTppInput || $isTppEditItem;

    $isPegawai = request()->routeIs('pegawai.*');
    $isKelasJabatan = request()->routeIs('kelas-jabatan.*');
    $isMasterData = $isPegawai || $isKelasJabatan;

    $isUsers = request()->routeIs('users.*');
    $isUnitKerja = request()->routeIs('unit-kerja.*');
    $isProfile = request()->routeIs('profile.*');

    $isRekapTpp = request()->routeIs('tpp.rekap') || request()->routeIs('tpp.rekap.export');
    $isRekapSipd = request()->routeIs('tpp.rekap.sipd') || request()->routeIs('tpp.rekap.sipd.export');
    $isLaporan = $isRekapTpp || $isRekapSipd;
    $authUser = Auth::user();
    $userRole = $authUser->role ?? null;
    $isViewer = $userRole === 'viewer';
    $profilePhotoPath = $authUser?->pegawai?->foto_profil ?: $authUser?->foto_profil;
    $profilePhotoStamp = optional($authUser?->pegawai?->updated_at ?? $authUser?->updated_at)?->timestamp;
    $profilePhotoUrl = $profilePhotoPath
        ? route('profile.photo') . ($profilePhotoStamp ? '?v=' . urlencode((string) $profilePhotoStamp) : '')
        : null;
    $profileInitials = Str::of($authUser->name ?? 'User')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $profileRoleLabel = match ($userRole) {
        'viewer' => 'Pegawai',
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'operator' => 'Operator',
        default => Str::of(str_replace('_', ' ', $userRole ?? 'pengguna'))->title()->toString(),
    };
    $profileRoleClass = match ($userRole) {
        'super_admin' => 'role-super-admin',
        'admin' => 'role-admin',
        'operator' => 'role-operator',
        'viewer' => 'role-viewer',
        default => 'role-default',
    };
@endphp

<header id="header" class="header dark-background d-flex flex-column">
    <i class="header-toggle d-xl-none bi bi-list" aria-label="Tutup sidebar" title="Tutup sidebar"></i>

    <div class="sidebar-header-block">
        <div class="profile-img sidebar-image-trigger" data-sidebar-image-preview="true" data-image-src="{{ asset('images/sidebar-brand-square.png') }}" data-image-title="Logo Aplikasi SIMPATI">
            <img src="{{ asset('images/sidebar-brand-square.png') }}" alt="E-TPP SIMPATI" class="img-fluid sidebar-brand-image">
        </div>

        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center justify-content-center text-center">
            <h1 class="sitename">SIMPATI</h1>
        </a>
        <div class="brand-subtitle">Sistem Informasi Manajemen<br>Perhitungan TPP Interaktif</div>

        @auth
        <a href="{{ route('profile.edit') }}" class="sidebar-user-card text-decoration-none">
            <div class="sidebar-user-avatar sidebar-image-trigger {{ $profilePhotoUrl ? 'has-photo' : 'has-initials' }}" @if($profilePhotoUrl) data-sidebar-image-preview="true" data-image-src="{{ $profilePhotoUrl }}" data-image-title="Foto profil {{ $authUser->name }}" @endif>
                @if($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $authUser->name }}" class="sidebar-user-avatar-image">
                @else
                    <span class="sidebar-user-avatar-fallback">{{ $profileInitials }}</span>
                @endif
            </div>
            <div class="sidebar-user-meta text-center">
                <div class="sidebar-user-name">{{ $authUser->name }}</div>
                <div class="sidebar-user-role {{ $profileRoleClass }}">{{ $profileRoleLabel }}</div>
                <div class="sidebar-user-unit">{{ $authUser->unitKerja->nama_unit ?? 'Semua Unit' }}</div>
            </div>
        </a>
        @endauth
    </div>

    <nav id="navmenu" class="navmenu">
        <ul>
            <li class="nav-section-label">Ringkasan</li>
            <li>
                <a href="{{ route('dashboard') }}" class="{{ $isDashboard ? 'active' : '' }}" title="Dashboard">
                    <i class="bi bi-grid navicon"></i><span>{{ $isViewer ? 'Dashboard Saya' : 'Dashboard' }}</span>
                </a>
            </li>

            <li class="nav-section-label">{{ $isViewer ? 'TPP Pribadi' : 'Operasional TPP' }}</li>

            @if($isViewer)
                <li>
                    <a href="{{ route('tpp.index') }}" class="{{ $isTppList ? 'active' : '' }}" title="Riwayat TPP Saya">
                        <i class="bi bi-wallet2 navicon"></i><span>Riwayat TPP Saya</span>
                    </a>
                </li>
            @else
                <li class="dropdown {{ $isTppGroup ? 'menu-open' : '' }}">
                    <a href="#" class="{{ $isTppGroup ? 'active' : '' }}" title="Proses TPP" aria-expanded="{{ $isTppGroup ? 'true' : 'false' }}">
                        <i class="bi bi-receipt-cutoff navicon"></i><span>Proses TPP</span><i class="bi bi-chevron-down toggle-dropdown"></i>
                    </a>
                    <ul class="{{ $isTppGroup ? 'dropdown-active' : '' }}">
                        <li><a href="{{ route('tpp.index') }}" class="{{ $isTppList || $isTppEditItem ? 'active' : '' }}">Daftar Data TPP</a></li>
                        @if(in_array($userRole, ['admin', 'operator']))
                        <li><a href="{{ route('tpp.create', ['bulan' => now()->startOfMonth()->subMonth()->month, 'tahun' => now()->startOfMonth()->subMonth()->year]) }}" class="{{ $isTppInput ? 'active' : '' }}">Input TPP</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(in_array($userRole, ['super_admin', 'admin', 'operator']))
                <li class="nav-section-label">Master Data</li>
                <li class="dropdown {{ $isMasterData ? 'menu-open' : '' }}">
                    <a href="#" class="{{ $isMasterData ? 'active' : '' }}" title="Master Data" aria-expanded="{{ $isMasterData ? 'true' : 'false' }}">
                        <i class="bi bi-database navicon"></i><span>Master Data</span><i class="bi bi-chevron-down toggle-dropdown"></i>
                    </a>
                    <ul class="{{ $isMasterData ? 'dropdown-active' : '' }}">
                        <li><a href="{{ route('pegawai.index') }}" class="{{ $isPegawai ? 'active' : '' }}">Pegawai</a></li>
                        @if(in_array($userRole, ['admin', 'operator']))
                        <li><a href="{{ route('kelas-jabatan.index') }}" class="{{ $isKelasJabatan ? 'active' : '' }}">Kelas Jabatan</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(in_array($userRole, ['super_admin', 'admin', 'operator']))
                <li class="nav-section-label">Laporan & Output</li>
                <li class="dropdown {{ $isLaporan ? 'menu-open' : '' }}">
                    <a href="#" class="{{ $isLaporan ? 'active' : '' }}" title="Laporan dan Output" aria-expanded="{{ $isLaporan ? 'true' : 'false' }}">
                        <i class="bi bi-file-earmark-text navicon"></i><span>Laporan & Output</span><i class="bi bi-chevron-down toggle-dropdown"></i>
                    </a>
                    <ul class="{{ $isLaporan ? 'dropdown-active' : '' }}">
                        @if(in_array($userRole, ['super_admin', 'admin']))
                        <li><a href="{{ route('tpp.rekap', request()->all()) }}" class="{{ $isRekapTpp ? 'active' : '' }}">Rekap TPP</a></li>
                        <li><a href="{{ route('tpp.rekap.sipd', request()->all()) }}" class="{{ $isRekapSipd ? 'active' : '' }}">Rekap SIPD</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <li class="nav-section-label">Akun & Sistem</li>

            @if($userRole === 'super_admin')
                <li>
                    <a href="{{ route('unit-kerja.index') }}" class="{{ $isUnitKerja ? 'active' : '' }}" title="Unit Kerja">
                        <i class="bi bi-building navicon"></i><span>Unit Kerja</span>
                    </a>
                </li>
            @endif

            @if(in_array($userRole, ['super_admin', 'admin']))
                <li>
                    <a href="{{ route('users.index') }}" class="{{ $isUsers ? 'active' : '' }}" title="Manajemen User">
                        <i class="bi bi-person-gear navicon"></i><span>Manajemen User</span>
                    </a>
                </li>
            @endif

            <li>
                <a href="{{ route('profile.edit') }}" class="{{ $isProfile ? 'active' : '' }}" title="Profil Saya">
                    <i class="bi bi-person-circle navicon"></i><span>Profil Saya</span>
                </a>
            </li>

            <li class="mobile-logout">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-100 text-start border-0 bg-transparent" style="padding:0;">
                        <span style="display:flex;align-items:center;color:var(--nav-color);padding:15px 10px;font-family:var(--nav-font);font-size:15px;font-weight:500;">
                            <i class="bi bi-box-arrow-right navicon"></i><span>Logout</span>
                        </span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer-actions mt-auto">
        <button type="button" class="btn sidebar-desktop-toggle d-none d-xl-inline-flex w-100" id="sidebarCollapseToggle" aria-label="Ciutkan sidebar" title="Ciutkan sidebar">
            <i class="bi bi-layout-sidebar-inset"></i>
            <span class="sidebar-action-label">Ciutkan Sidebar</span>
        </button>

        @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-primary w-100 rounded-pill py-2">
                <i class="bi bi-box-arrow-right me-2"></i><span class="sidebar-action-label">Logout</span>
            </button>
        </form>
        @endauth
    </div>
</header>
