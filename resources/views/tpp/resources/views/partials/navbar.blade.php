<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" style="box-shadow: 0 10px 30px rgba(16,24,40,.10);">
  <div class="container container-page">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
      <span class="d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:10px;background:rgba(255,255,255,.12)">
        <i class="bi bi-calculator"></i>
      </span>
      <span>TPP</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navTPP">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navTPP">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
             href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2 me-1"></i>Dashboard</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('tpp.*') ? 'active' : '' }}"
             href="{{ route('tpp.index') }}"><i class="bi bi-receipt me-1"></i>TPP</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}"
             href="{{ route('pegawai.index') }}"><i class="bi bi-people me-1"></i>Pegawai</a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('kelas-jabatan.*') ? 'active' : '' }}"
             href="{{ route('kelas-jabatan.index') }}"><i class="bi bi-diagram-3 me-1"></i>Kelas Jabatan</a>
        </li>

        @auth
          @if(Auth::user()->role === 'admin')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                 href="{{ route('users.index') }}"><i class="bi bi-person-gear me-1"></i>User</a>
            </li>
          @endif
        @endauth

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->is('tpp*') ? 'active' : '' }}"
             href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-file-earmark-text me-1"></i>Laporan
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('tpp.index') }}">Daftar TPP</a></li>
            <li><a class="dropdown-item" href="{{ route('tpp.create') }}">Input TPP</a></li>

            <li><hr class="dropdown-divider"></li>

            <li><a class="dropdown-item" href="{{ route('tpp.index') }}#hapus-massal">Hapus Massal</a></li>
            <li><a class="dropdown-item" href="{{ route('tpp.index') }}#edit-massal">Edit Massal</a></li>

            <li><hr class="dropdown-divider"></li>

            <li><a class="dropdown-item" href="{{ route('tpp.cetak', request()->all()) }}">Cetak PDF</a></li>

            <li><a class="dropdown-item" href="{{ route('tpp.export', request()->all()) }}">Export Excel</a></li>

            <li><a class="dropdown-item" href="{{ route('tpp.rekap', request()->all()) }}">Rekap TPP</a>
            </li>
          </ul>
        </li>

      </ul>

      @auth
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
            <span class="ms-1 badge rounded-pill text-bg-light">{{ Auth::user()->role }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item">Logout</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
      @endauth

      @guest
        <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Login</a>
      @endguest

    </div>
  </div>
</nav>