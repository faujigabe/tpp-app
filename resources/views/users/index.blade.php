@extends('layouts.main')

@section('title', 'Manajemen User')

@section('content')
@php
    $collection = $users->getCollection();
    $totalUsers = $users->total();
    $superAdminCount = $collection->where('role', 'super_admin')->count();
    $adminCount = $collection->where('role', 'admin')->count();
    $operatorCount = $collection->where('role', 'operator')->count();
    $viewerCount = $collection->where('role', 'viewer')->count();
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Manajemen User</h3>
        <div class="text-muted">
            @if(auth()->user()->isSuperAdmin())
                Kelola semua user lintas unit kerja atau filter per unit sesuai kebutuhan.
            @else
                Kelola user pada unit kerja Anda sendiri.
            @endif
        </div>
    </div>
    <div>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-icon">
            <i class="bi bi-person-plus"></i>Tambah User
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total User</div>
                <div class="fs-3 fw-bold">{{ number_format($totalUsers, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Super Admin / Admin</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($superAdminCount + $adminCount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Operator</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($operatorCount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Viewer</div>
                <div class="fs-3 fw-bold text-secondary">{{ number_format($viewerCount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-soft border-0">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="fw-semibold">Daftar User</div>
                <div class="small text-muted">Tampilan user berdasarkan unit kerja, nama, email, dan role.</div>
            </div>
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                @if(auth()->user()->isSuperAdmin())
                    <div class="col-auto" style="min-width: 240px;">
                        <label class="form-label small text-muted mb-1">Unit Kerja</label>
                        <select name="unit_kerja_id" class="form-select">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjas as $unit)
                                <option value="{{ $unit->id }}" {{ (string) $selectedUnitKerjaId === (string) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-auto" style="min-width: 260px;">
                    <label class="form-label small text-muted mb-1">Pencarian</label>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Cari user...">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary btn-icon">
                        <i class="bi bi-search"></i>Filter
                    </button>
                    @if($search || $selectedUnitKerjaId)
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-icon">
                            <i class="bi bi-arrow-clockwise"></i>Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 72px;">No</th>
                        <th>User</th>
                        <th>Kontak</th>
                        <th>Role</th>
                        <th>Unit Kerja</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleClass = [
                                'super_admin' => 'text-bg-dark',
                                'admin' => 'text-bg-primary',
                                'operator' => 'text-bg-success',
                                'viewer' => 'text-bg-secondary',
                            ][$user->role] ?? 'text-bg-light';
                            $unitKerjaLabel = optional($user->unitKerja)->nama_unit
                                ?? optional(optional($user->pegawai)->unitKerja)->nama_unit
                                ?? '-';
                        @endphp
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="small text-muted">ID: {{ $user->id }}</div>
                            </td>
                            <td>
                                <div>{{ $user->email }}</div>
                                <div class="small text-muted">Dibuat: {{ optional($user->created_at)->format('d M Y H:i') }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $roleClass }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $unitKerjaLabel }}</div>
                                @if($user->pegawai)
                                    <div class="small text-muted">Pegawai: {{ $user->pegawai->nama }}</div>
                                @endif
                            </td>
                            <td>
                                @if(auth()->id() === $user->id)
                                    <span class="badge text-bg-warning">Akun login aktif</span>
                                @else
                                    <span class="badge text-bg-light">Dapat dikelola</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning btn-icon">
                                        <i class="bi bi-pencil-square"></i>Edit
                                    </a>

                                    @if(auth()->id() != $user->id)
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" data-confirm data-confirm-title="Hapus akun pengguna?" data-confirm-message="Akun {{ $user->name }} ({{ $user->email }}) akan dihapus dan tidak dapat digunakan untuk masuk kembali." data-confirm-label="Hapus Akun" data-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger btn-icon">
                                                <i class="bi bi-trash"></i>Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary btn-icon" disabled>
                                            <i class="bi bi-shield-lock"></i>Terkunci
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
