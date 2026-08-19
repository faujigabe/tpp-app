@extends('layouts.main')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'operator' => 'Operator',
        'viewer' => 'Pegawai / Viewer',
    ];
    $roleLabel = $roleLabels[strtolower((string) ($user->role ?? ''))] ?? ucfirst($user->role ?? 'Pengguna');
    $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
    $initials = collect($nameParts)->filter()->take(2)->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))->implode('');
    $initials = $initials !== '' ? $initials : strtoupper(mb_substr((string) $user->name, 0, 1));
    $memberSince = optional($user->created_at)->translatedFormat('d F Y') ?? '-';
    $pegawai = $user->pegawai;
    $verifiedAt = optional($user->email_verified_at)->translatedFormat('d F Y H:i');
    $lastLoginValue = property_exists($user, 'last_login_at') || isset($user->last_login_at)
        ? optional($user->last_login_at)->translatedFormat('d F Y H:i')
        : null;
    $fotoProfilStamp = $pegawai?->updated_at ?: $user->updated_at;
    $fotoProfilUrl = ($pegawai && $pegawai->foto_profil) || !empty($user->foto_profil)
        ? route('profile.photo') . '?v=' . urlencode((string) $fotoProfilStamp)
        : null;
    $isPegawaiRole = in_array(strtolower((string) ($user->role ?? '')), ['viewer', 'pegawai'], true);
@endphp
<div class="container-fluid py-4">
    <style>
        .profile-page-wrap { max-width: 1220px; margin: 0 auto; }
        .profile-hero-card,
        .profile-panel-card { border: 0; border-radius: 22px; box-shadow: 0 16px 45px rgba(15, 35, 95, 0.08); }
        .profile-hero-card { overflow: hidden; background: linear-gradient(135deg, #0f2747 0%, #1c5fb8 100%); color: #fff; }
        .profile-hero-pattern { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 28%), radial-gradient(circle at bottom left, rgba(255,255,255,.12), transparent 25%); pointer-events: none; }
        .profile-avatar { width: 88px; height: 88px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.24); box-shadow: inset 0 1px 0 rgba(255,255,255,.18); overflow: hidden; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-photo-preview { width: 112px; height: 112px; border-radius: 24px; overflow: hidden; border: 1px solid #d9e2ef; background: #f4f7fb; display: flex; align-items: center; justify-content: center; }
        .profile-photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .profile-stat { border-radius: 18px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); padding: 14px 16px; min-height: 100%; }
        .profile-panel-card .card-header { background: #fff; border-bottom: 1px solid #eef2f7; padding: 1.1rem 1.35rem; }
        .profile-panel-card .card-body { padding: 1.35rem; }
        .profile-panel-title { font-weight: 700; color: #183153; margin-bottom: .2rem; }
        .profile-muted { color: #6c7a90; }
        .profile-label { font-weight: 600; color: #344054; margin-bottom: .45rem; }
        .profile-input, .profile-select, .profile-textarea { border-radius: 14px !important; border: 1px solid #d9e2ef !important; box-shadow: none !important; }
        .profile-input, .profile-select { min-height: 48px; }
        .profile-textarea { min-height: 120px; }
        .profile-input:focus, .profile-select:focus, .profile-textarea:focus { border-color: #1f7ae0 !important; box-shadow: 0 0 0 .2rem rgba(31, 122, 224, .12) !important; }
        .profile-btn-primary { border: 0; border-radius: 14px; padding: .78rem 1.1rem; font-weight: 600; background: linear-gradient(135deg, #1765c1 0%, #1f7ae0 100%); color: #fff; }
        .profile-btn-primary:hover { color: #fff; opacity: .96; }
        .profile-btn-outline { border-radius: 14px; padding: .75rem 1rem; font-weight: 600; }
        .profile-badge-soft { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .75rem; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.18); font-size: .88rem; }
        .danger-zone { border: 1px solid rgba(220, 53, 69, .16); background: linear-gradient(180deg, #fff 0%, #fff7f8 100%); }
        .danger-zone .card-header { border-bottom-color: rgba(220, 53, 69, .12); }
        .data-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .65rem; border-radius: 999px; background: #f3f7fb; color: #345; font-size: .84rem; }
        @media (max-width: 991.98px) {
            .profile-hero-card .row > div + div { margin-top: 1rem; }
        }
    </style>

    @php
    $profileNip = $pegawai?->nip ?: ($user->nip ?: null);
    $hasPegawaiProfile = (bool) $pegawai;
@endphp

<div class="profile-page-wrap">
        <div class="card profile-hero-card mb-4 position-relative">
            <div class="profile-hero-pattern"></div>
            <div class="card-body p-4 p-lg-5 position-relative">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            <div class="profile-avatar">
                                @if($fotoProfilUrl)
                                    <img src="{{ $fotoProfilUrl }}" alt="Foto Profil {{ $user->name }}">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                            <div>
                                <div class="profile-badge-soft mb-2"><i class="bi bi-person-badge"></i><span>{{ $roleLabel }}</span></div>
                                <h1 class="h3 fw-bold mb-1">Profil Saya</h1>
                                <p class="mb-0 opacity-75">Kelola akun dan data pribadi pegawai dari satu halaman.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-3">
                            <div class="col-sm-6"><div class="profile-stat"><div class="small text-white-50 mb-1">Nama Pengguna</div><div class="fw-semibold">{{ $user->name }}</div></div></div>
                            <div class="col-sm-6"><div class="profile-stat"><div class="small text-white-50 mb-1">Email Aktif</div><div class="fw-semibold text-break">{{ $user->email }}</div></div></div>
                            <div class="col-sm-6"><div class="profile-stat"><div class="small text-white-50 mb-1">NIP</div><div class="fw-semibold">{{ $profileNip ?: '-' }}</div></div></div>
                            <div class="col-sm-6"><div class="profile-stat"><div class="small text-white-50 mb-1">Terdaftar Sejak</div><div class="fw-semibold">{{ $memberSince }}</div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">Profil berhasil diperbarui.</div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">Password berhasil diperbarui.</div>
        @endif
        @if (session('status') === 'verification-link-sent')
            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">Link verifikasi email baru telah dikirim ke alamat email Anda.</div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card profile-panel-card h-100">
                    <div class="card-header">
                        <div class="profile-panel-title">Informasi Akun & Data Pribadi</div>
                        <div class="profile-muted small">Pegawai dapat memperbarui identitas akun, kontak, data administrasi pribadi, dan foto profil di bawah ini.</div>
                    </div>
                    <div class="card-body">
                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded-4 p-3 p-lg-4 bg-light-subtle">
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                                            <div class="profile-photo-preview" id="profile-photo-preview" data-default-icon="true">
                                                @if($fotoProfilUrl)
                                                    <img src="{{ $fotoProfilUrl }}" alt="Foto Profil {{ $user->name }}" id="profile-photo-preview-image">
                                                @else
                                                    <i class="bi bi-person-circle fs-1 text-secondary" id="profile-photo-preview-icon"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <label for="foto_profil" class="profile-label">Foto Profil</label>
                                                <input id="foto_profil" name="foto_profil" type="file" class="form-control profile-input @error('foto_profil') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                                                <input type="hidden" name="hapus_foto_profil" id="hapus_foto_profil" value="0">
                                                <div class="form-text">Format: JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2 MB. Preview akan berubah otomatis setelah file dipilih.</div>
                                                @error('foto_profil')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                @error('hapus_foto_profil')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                    <label for="foto_profil" class="btn btn-outline-primary profile-btn-outline mb-0"><i class="bi bi-upload me-2"></i>Ganti Foto</label>
                                                    @if($fotoProfilUrl)
                                                        <button type="button" class="btn btn-outline-danger profile-btn-outline" id="btn-hapus-foto"><i class="bi bi-trash3 me-2"></i>Hapus Foto</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="name" class="profile-label">Nama Lengkap</label>
                                    <input id="name" name="name" type="text" class="form-control profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="profile-label">Alamat Email</label>
                                    <input id="email" name="email" type="email" class="form-control profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                @if ($pegawai)
                                    <div class="col-md-6">
                                        <label for="nip" class="profile-label">NIP</label>
                                        <input id="nip" name="nip" type="text" class="form-control profile-input @error('nip') is-invalid @enderror" value="{{ old('nip', $pegawai->nip) }}" required>
                                        @error('nip')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nik" class="profile-label">NIK</label>
                                        <input id="nik" name="nik" type="text" class="form-control profile-input @error('nik') is-invalid @enderror" value="{{ old('nik', $pegawai->nik) }}">
                                        @error('nik')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="no_npwp" class="profile-label">No NPWP</label>
                                        <input id="no_npwp" name="no_npwp" type="text" class="form-control profile-input @error('no_npwp') is-invalid @enderror" value="{{ old('no_npwp', $pegawai->no_npwp) }}">
                                        @error('no_npwp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nomor_rekening" class="profile-label">No Rekening</label>
                                        <input id="nomor_rekening" name="nomor_rekening" type="text" class="form-control profile-input @error('nomor_rekening') is-invalid @enderror" value="{{ old('nomor_rekening', $pegawai->nomor_rekening) }}">
                                        @error('nomor_rekening')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="no_hp" class="profile-label">No HP</label>
                                        <input id="no_hp" name="no_hp" type="text" class="form-control profile-input @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $pegawai->no_hp) }}">
                                        @error('no_hp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="golongan" class="profile-label">Golongan</label>
                                        <select id="golongan" name="golongan" class="form-select profile-select @error('golongan') is-invalid @enderror">
                                            <option value="">Pilih golongan</option>
                                            @foreach (['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E'] as $golongan)
                                                <option value="{{ $golongan }}" @selected(old('golongan', $pegawai->golongan) === $golongan)>{{ $golongan }}</option>
                                            @endforeach
                                        </select>
                                        @error('golongan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jabatan" class="profile-label">Jabatan</label>
                                        <input id="jabatan" name="jabatan" type="text" class="form-control profile-input @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $pegawai->jabatan) }}">
                                        @error('jabatan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat" class="profile-label">Alamat</label>
                                        <textarea id="alamat" name="alamat" class="form-control profile-textarea @error('alamat') is-invalid @enderror">{{ old('alamat', $pegawai->alamat) }}</textarea>
                                        @error('alamat')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label for="nip" class="profile-label">NIP</label>
                                        <input id="nip" name="nip" type="text" class="form-control profile-input @error('nip') is-invalid @enderror" value="{{ old('nip', $user->nip) }}" placeholder="Masukkan NIP akun ini bila ada">
                                        <div class="form-text">NIP ini disimpan pada akun sistem dan ditampilkan sebagai informasi profil.</div>
                                        @error('nip')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info rounded-4 border-0 mb-0">Akun ini digunakan sebagai akun sistem dan tidak terhubung dengan master data pegawai. Anda tetap dapat mengubah foto profil, nama, email, password, dan NIP akun.</div>
                                    </div>
                                @endif
                            </div>

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="alert alert-warning rounded-4 border-0 mt-3 mb-0">
                                    <div class="fw-semibold mb-1">Email Anda belum terverifikasi.</div>
                                    <div class="small mb-2">Silakan kirim ulang email verifikasi untuk mengamankan akses akun.</div>
                                    <button form="send-verification" class="btn btn-outline-warning profile-btn-outline" type="submit">Kirim Ulang Verifikasi</button>
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 pt-4">
                                <button type="submit" class="btn profile-btn-primary"><i class="bi bi-check2-circle me-1"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card profile-panel-card mb-4">
                    <div class="card-header">
                        <div class="profile-panel-title">Ringkasan Profil</div>
                        <div class="profile-muted small">Informasi identitas dan status akun Anda ditampilkan ringkas di sini.</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12"><div class="border rounded-4 p-3"><div class="small text-uppercase text-muted fw-semibold mb-1">Role Pengguna</div><div class="fw-semibold text-dark">{{ $roleLabel }}</div><div class="small text-muted mt-1">Hak akses mengikuti pengaturan admin sistem.</div></div></div>
                            <div class="col-12"><div class="border rounded-4 p-3"><div class="small text-uppercase text-muted fw-semibold mb-1">Status Email</div><div class="fw-semibold text-dark">@if (!($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) || $user->hasVerifiedEmail()) Terverifikasi @else Belum diverifikasi @endif</div><div class="small text-muted mt-1">{{ $verifiedAt ? 'Diverifikasi pada ' . $verifiedAt : 'Belum ada waktu verifikasi tersimpan.' }}</div></div></div>
                            <div class="col-12"><div class="border rounded-4 p-3"><div class="small text-uppercase text-muted fw-semibold mb-1">Login Terakhir</div><div class="fw-semibold text-dark">{{ $lastLoginValue ?? 'Belum tersedia' }}</div><div class="small text-muted mt-1">Kolom ini otomatis tampil jika aplikasi menyimpan riwayat login.</div></div></div>
                            <div class="col-12"><div class="border rounded-4 p-3"><div class="small text-uppercase text-muted fw-semibold mb-1">Data Pegawai Terkait</div><div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="data-chip"><i class="bi bi-hash"></i>ID Akun #{{ str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="data-chip"><i class="bi bi-person-vcard"></i>{{ $profileNip ?: 'Belum tersedia' }}</span>
                                <span class="data-chip"><i class="bi bi-briefcase"></i>{{ $pegawai?->jabatan ?: 'Jabatan belum diisi' }}</span>
                            </div></div></div>
                        </div>
                    </div>
                </div>

                <div class="card profile-panel-card mb-4">
                    <div class="card-header"><div class="profile-panel-title">Keamanan Akun</div><div class="profile-muted small">Ganti password secara berkala untuk menjaga keamanan akses.</div></div>
                    <div class="card-body">
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')
                            <div class="mb-3"><label for="update_password_current_password" class="profile-label">Password Saat Ini</label><input id="update_password_current_password" name="current_password" type="password" class="form-control profile-input @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">@error('current_password', 'updatePassword')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                            <div class="mb-3"><label for="update_password_password" class="profile-label">Password Baru</label><input id="update_password_password" name="password" type="password" class="form-control profile-input @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">@error('password', 'updatePassword')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                            <div class="mb-0"><label for="update_password_password_confirmation" class="profile-label">Konfirmasi Password Baru</label><input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control profile-input @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">@error('password_confirmation', 'updatePassword')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                            <div class="pt-3"><button type="submit" class="btn profile-btn-primary w-100"><i class="bi bi-shield-lock me-1"></i> Perbarui Password</button></div>
                        </form>
                    </div>
                </div>

                @unless($isPegawaiRole)
                <div class="card profile-panel-card danger-zone">
                    <div class="card-header"><div class="profile-panel-title text-danger">Zona Berbahaya</div><div class="profile-muted small">Gunakan fitur ini hanya jika Anda benar-benar ingin menghapus akun.</div></div>
                    <div class="card-body">
                        <p class="profile-muted mb-3">Setelah akun dihapus, seluruh akses akun akan dihentikan secara permanen. Pastikan data penting sudah diamankan.</p>
                        <button type="button" class="btn btn-outline-danger profile-btn-outline w-100" data-bs-toggle="modal" data-bs-target="#confirmDeleteProfileModal"><i class="bi bi-trash3 me-1"></i> Hapus Akun</button>
                    </div>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>

@unless($isPegawaiRole)
<div class="modal fade" id="confirmDeleteProfileModal" tabindex="-1" aria-labelledby="confirmDeleteProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="confirmDeleteProfileModalLabel">Konfirmasi Hapus Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted mb-3">Tindakan ini tidak dapat dibatalkan. Masukkan password Anda untuk melanjutkan proses penghapusan akun.</p>
                    <label for="delete_password" class="profile-label">Password</label>
                    <input id="delete_password" name="password" type="password" class="form-control profile-input @error('password', 'userDeletion') is-invalid @enderror" placeholder="Masukkan password">
                    @error('password', 'userDeletion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light profile-btn-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger profile-btn-outline">Ya, Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('foto_profil');
    const removeInput = document.getElementById('hapus_foto_profil');
    const removeButton = document.getElementById('btn-hapus-foto');
    const preview = document.getElementById('profile-photo-preview');

    const setPreviewToDefault = function () {
        if (!preview) {
            return;
        }

        preview.innerHTML = '<i class="bi bi-person-circle fs-1 text-secondary" id="profile-photo-preview-icon"></i>';
    };

    const setPreviewToImage = function (src) {
        if (!preview) {
            return;
        }

        preview.innerHTML = '<img src="' + src + '" alt="Preview Foto Profil" id="profile-photo-preview-image">';
    };

    if (fileInput && removeInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length > 0) {
                removeInput.value = '0';

                const selectedFile = fileInput.files[0];
                if (selectedFile && selectedFile.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        if (event.target?.result) {
                            setPreviewToImage(event.target.result);
                        }
                    };
                    reader.readAsDataURL(selectedFile);
                }

                if (removeButton) {
                    removeButton.classList.remove('disabled');
                    removeButton.removeAttribute('aria-disabled');
                    removeButton.innerHTML = '<i class="bi bi-trash3 me-2"></i>Hapus Foto';
                }
            }
        });
    }

    if (removeButton && fileInput && removeInput) {
        removeButton.addEventListener('click', function () {
            if (!window.confirm('Hapus foto profil saat ini?')) {
                return;
            }

            removeInput.value = '1';
            fileInput.value = '';
            setPreviewToDefault();
            this.classList.add('disabled');
            this.setAttribute('aria-disabled', 'true');
            this.innerHTML = '<i class="bi bi-check2 me-2"></i>Foto akan dihapus';
        });
    }
});
</script>
@endpush

@endsection
