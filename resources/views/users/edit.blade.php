@extends('layouts.main')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="mb-1">Edit User</h3>
        <p class="text-muted mb-0">Perbarui profil akun dan akses user.</p>
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
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($allowedRoles as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
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
                                <div class="form-text">Akun ini tetap dikelola dalam unit kerja yang sama.</div>
                            @endif
                            @error('unit_kerja_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Relasi Pegawai <span class="text-muted fw-normal">(wajib untuk viewer)</span></label>
                            <select name="pegawai_id" class="form-select @error('pegawai_id') is-invalid @enderror">
                                <option value="">-- Pilih pegawai --</option>
                                @foreach($pegawais as $pegawai)
                                    <option value="{{ $pegawai->id }}" {{ (string) old('pegawai_id', $user->pegawai_id) === (string) $pegawai->id ? 'selected' : '' }}>
                                        {{ $pegawai->nama }}{{ $pegawai->nip ? ' - ' . $pegawai->nip : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Gunakan relasi ini untuk membatasi akun viewer agar hanya melihat data TPP pribadinya.</div>
                            @error('pegawai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100 rounded-4 bg-light border p-3">
                                <div class="small text-muted mb-1">Reset password</div>
                                <div class="fw-semibold">Opsional</div>
                                <div class="small text-muted">Kosongkan password bila tidak ingin mengubahnya.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Isi jika ingin reset password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                        <a href="{{ route('users.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary btn-icon">
                            <i class="bi bi-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
