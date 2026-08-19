@extends('layouts.guest')

@section('title', 'Login E-TPP')

@section('content')
@php
    $selectedLogin = old('login_as', $errors->has('email') ? 'admin' : 'pegawai');
@endphp

<x-auth-session-status class="mb-4" :status="session('status')" />


<div class="login-mode-wrapper mb-4">
    <div class="login-mode-grid">
        <button type="button"
                class="login-mode-btn {{ $selectedLogin === 'pegawai' ? 'active' : '' }}"
                data-mode="pegawai">
            <span class="login-mode-icon"><i class="bi bi-person-badge"></i></span>
            <span class="login-mode-label">
                <span class="d-block fw-semibold login-mode-title">Pegawai</span>
                <small class="text-muted">Masuk menggunakan NIP</small>
            </span>
        </button>

        <button type="button"
                class="login-mode-btn {{ $selectedLogin === 'admin' ? 'active' : '' }}"
                data-mode="admin">
            <span class="login-mode-icon"><i class="bi bi-shield-lock"></i></span>
            <span class="login-mode-label">
                <span class="d-block fw-semibold login-mode-title is-admin">Admin</span>
                <small class="text-muted">Untuk super admin, admin, dan operator</small>
            </span>
        </button>
    </div>
</div>

<form method="POST" action="{{ route('login') }}" class="login-form {{ $selectedLogin === 'pegawai' ? '' : 'd-none' }}" data-form="pegawai">
    @csrf
    <input type="hidden" name="login_as" value="pegawai">

    <div class="mb-3">
        <label for="nip" class="form-label fw-semibold">NIP</label>
        <input id="nip" class="form-control" type="text" name="nip" value="{{ old('login_as') === 'pegawai' ? old('nip') : '' }}" {{ $selectedLogin === 'pegawai' ? 'required' : '' }} autofocus autocomplete="username" placeholder="Masukkan NIP pegawai" />
        <x-input-error :messages="$errors->get('nip')" class="mt-2 text-danger small" />
    </div>

    <div class="mb-3">
        <label for="pegawai_password" class="form-label fw-semibold">Password</label>
        <div class="input-group">
            <input id="pegawai_password" class="form-control border-end-0" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
            <button class="btn btn-outline-secondary border-start-0 toggle-password" type="button" data-target="pegawai_password" aria-label="Tampilkan password" aria-pressed="false">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
    </div>

    <div class="form-check mb-4">
        <input id="remember_pegawai" type="checkbox" class="form-check-input" name="remember" {{ old('login_as') === 'pegawai' && old('remember') ? 'checked' : '' }}>
        <label for="remember_pegawai" class="form-check-label">Ingat saya</label>
    </div>

    <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Pegawai
    </button>
</form>

<form method="POST" action="{{ route('login') }}" class="login-form {{ $selectedLogin === 'admin' ? '' : 'd-none' }}" data-form="admin">
    @csrf
    <input type="hidden" name="login_as" value="admin">

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input id="email" class="form-control" type="email" name="email" value="{{ old('login_as') === 'admin' ? old('email') : '' }}" {{ $selectedLogin === 'admin' ? 'required' : '' }} autocomplete="username" placeholder="nama@email.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
    </div>

    <div class="mb-3">
        <label for="admin_password" class="form-label fw-semibold">Password</label>
        <div class="input-group">
            <input id="admin_password" class="form-control border-end-0" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
            <button class="btn btn-outline-secondary border-start-0 toggle-password" type="button" data-target="admin_password" aria-label="Tampilkan password" aria-pressed="false">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="form-check">
            <input id="remember_admin" type="checkbox" class="form-check-input" name="remember" {{ old('login_as') === 'admin' && old('remember') ? 'checked' : '' }}>
            <label for="remember_admin" class="form-check-label">Ingat saya</label>
        </div>

        @if (Route::has('password.request'))
            <a class="small" href="{{ route('password.request') }}">Lupa password?</a>
        @endif
    </div>

    <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Admin
    </button>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modeButtons = document.querySelectorAll('.login-mode-btn');
        const forms = document.querySelectorAll('.login-form');

        function switchMode(mode) {
            let activeInput = null;

            modeButtons.forEach(function (button) {
                button.classList.toggle('active', button.dataset.mode === mode);
            });

            forms.forEach(function (form) {
                const isActive = form.dataset.form === mode;
                form.classList.toggle('d-none', !isActive);

                form.querySelectorAll('input').forEach(function (input) {
                    if (input.type === 'hidden') {
                        return;
                    }

                    input.disabled = !isActive;

                    if (input.name === 'nip') {
                        input.required = isActive && mode === 'pegawai';
                    } else if (input.name === 'email') {
                        input.required = isActive && mode === 'admin';
                    } else if (input.name === 'password') {
                        input.required = isActive;
                    }
                });

                if (isActive) {
                    activeInput = form.querySelector(mode === 'pegawai' ? 'input[name="nip"]' : 'input[name="email"]');
                }
            });

            if (activeInput) {
                setTimeout(function () {
                    activeInput.focus();
                    activeInput.select?.();
                }, 50);
            }
        }

        modeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                switchMode(this.dataset.mode);
            });
        });

        document.querySelectorAll('.toggle-password').forEach(function (toggleButton) {
            toggleButton.addEventListener('click', function () {
                const target = document.getElementById(this.dataset.target);

                if (!target) {
                    return;
                }

                const isHidden = target.getAttribute('type') === 'password';
                target.setAttribute('type', isHidden ? 'text' : 'password');
                this.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                this.innerHTML = isHidden
                    ? '<i class="bi bi-eye-slash"></i>'
                    : '<i class="bi bi-eye"></i>';
            });
        });

        switchMode(@json($selectedLogin));
    });
</script>
@endpush
