@extends('layouts.main')

@section('title', 'Tambah User')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Tambah User</h3>
        <p class="text-muted mb-0">Buat akun baru sesuai unit kerja dan jenis akses yang dibutuhkan.</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-icon">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-soft border-0">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Periksa kembali data yang diisi.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-soft">
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($allowedRoles as $role)
                                    <option value="{{ $role }}" {{ old('role', 'operator') === $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unit Kerja</label>
                            @if(auth()->user()->isSuperAdmin())
                                <select name="unit_kerja_id" class="form-select @error('unit_kerja_id') is-invalid @enderror" required>
                                    <option value="">Pilih unit kerja</option>
                                    @foreach($unitKerjas as $unit)
                                        <option value="{{ $unit->id }}" {{ (string) old('unit_kerja_id', $selectedUnitKerjaId) === (string) $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="unit_kerja_id" value="{{ $selectedUnitKerjaId }}">
                                <input type="text" class="form-control" value="{{ optional($unitKerjas->first())->nama_unit }}" readonly>
                                <div class="form-text">Admin hanya dapat mengelola operator dan viewer pada unit kerjanya sendiri.</div>
                            @endif
                            @error('unit_kerja_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Relasi Pegawai <span class="text-muted fw-normal">(wajib untuk viewer)</span></label>
                            <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror">
                                <option value="">-- Pilih pegawai --</option>
                                @foreach($pegawais as $pegawai)
                                    <option value="{{ $pegawai->id }}" {{ (string) old('pegawai_id') === (string) $pegawai->id ? 'selected' : '' }}>
                                        {{ $pegawai->nama }}{{ $pegawai->nip ? ' - ' . $pegawai->nip : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Akun viewer/pegawai hanya dapat melihat riwayat TPP milik pegawai yang dipilih di sini.</div>
                            @error('pegawai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100 rounded-4 bg-light border p-3">
                                <div class="small text-muted mb-1">Hak akses</div>
                                <div class="fw-semibold">Sesuai peran dan unit kerja</div>
                                <div class="small text-muted">Super admin lintas unit, admin/operator fokus pada unitnya, viewer hanya untuk pegawai terhubung.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="bi bi-save"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
