<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login E-TPP')</title>
    <link href="{{ asset('template/iportfolio/assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/css/main.css') }}" rel="stylesheet">
    <style>
        :root {
            --sumut-primary: #0b2e59;
            --sumut-secondary: #0f5fa8;
            --sumut-accent: #149ddd;
            --sumut-success: #1a8f6d;
            --sumut-text: #0f172a;
            --sumut-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                linear-gradient(135deg, rgba(11, 46, 89, .86), rgba(15, 95, 168, .66)),
                url('{{ asset('images/gubsu.jpg') }}') center center / cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--sumut-text);
        }

        .auth-shell {
            width: 100%;
            max-width: 1040px;
            min-height: 620px;
            display: grid;
            grid-template-columns: 1.02fr .98fr;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(15, 23, 42, .28);
            backdrop-filter: blur(10px);
        }

        .auth-showcase {
            position: relative;
            padding: 34px 32px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(8, 25, 49, .10), rgba(8, 25, 49, .62));
        }

        .auth-showcase::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.22), transparent 30%),
                radial-gradient(circle at bottom right, rgba(20,157,221,.28), transparent 28%);
            pointer-events: none;
        }

        .auth-showcase > * {
            position: relative;
            z-index: 1;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            font-size: .92rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
            margin-bottom: 22px;
        }

        .showcase-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255,255,255,.14);
            padding: 10px;
            margin-bottom: 22px;
        }

        .showcase-title {
            font-size: clamp(1.5rem, 2.2vw, 2.3rem);
            font-weight: 800;
            line-height: 1.16;
            max-width: 500px;
            margin-bottom: 16px;
        }

        .showcase-text {
            display: none;
        }

        .showcase-feature-list {
            display: grid;
            gap: 12px;
            max-width: 500px;
        }

        .showcase-feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
        }

        .showcase-feature i {
            font-size: 1rem;
            margin-top: 2px;
        }

        .showcase-feature strong {
            font-size: .96rem;
        }

        .showcase-feature span {
            font-size: .9rem;
            line-height: 1.5;
        }


        .faq-shortcut-card {
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
            max-width: 500px;
        }

        .faq-shortcut-card .faq-shortcut-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .faq-shortcut-card p {
            margin: 0 0 12px;
            color: rgba(255,255,255,.84);
            line-height: 1.5;
            font-size: .9rem;
        }

        .faq-shortcut-card .btn {
            border-radius: 999px;
            padding: .6rem 1rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,.4);
            background: rgba(255,255,255,.14);
            color: #fff;
        }

        .faq-shortcut-card .btn:hover {
            background: rgba(255,255,255,.22);
            color: #fff;
            border-color: rgba(255,255,255,.55);
        }

        .showcase-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
            color: rgba(255,255,255,.88);
        }

        .showcase-footer small {
            display: block;
            font-size: .86rem;
            color: rgba(255,255,255,.72);
        }

        .auth-panel {
            background: rgba(255,255,255,.97);
            padding: 30px 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 1.3rem;
        }

        .auth-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .auth-brand-text {
            min-width: 0;
            text-align: center;
        }

        .auth-title {
            text-align: center;
            margin-bottom: .25rem;
            font-weight: 800;
            color: var(--sumut-primary);
            font-size: 1.65rem;
            line-height: 1.15;
        }

        .auth-subtitle {
            text-align: center;
            color: var(--sumut-muted);
            margin-bottom: .25rem;
            line-height: 1.45;
            font-size: .95rem;
        }

        .auth-meta {
            text-align: center;
            color: var(--sumut-secondary);
            font-size: .86rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .form-label {
            color: var(--sumut-primary);
        }

        .form-control {
            border-radius: 14px;
            padding: .78rem .95rem;
            border-color: #d7e1ea;
            min-height: 46px;
        }

        .form-control:focus {
            border-color: var(--sumut-secondary);
            box-shadow: 0 0 0 .2rem rgba(15,95,168,.12);
        }

        .btn-primary {
            border-radius: 14px;
            padding: .82rem 1rem;
            background: linear-gradient(135deg, var(--sumut-secondary), var(--sumut-success));
            border: none;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0b4f8d, #15785c);
        }

        .login-mode-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .login-mode-btn {
            width: 100%;
            border: 1px solid #dbe4ee;
            border-radius: 18px;
            background: #fff;
            padding: 13px 16px;
            display: flex;
            align-items: center;
            min-height: 76px;
            gap: 12px;
            text-align: left;
            transition: all .2s ease;
        }

        .login-mode-btn:hover {
            border-color: var(--sumut-accent);
            transform: translateY(-1px);
        }

        .login-mode-btn.active {
            border-color: var(--sumut-accent);
            background: rgba(20, 157, 221, .08);
            box-shadow: 0 10px 24px rgba(20, 157, 221, .15);
        }

        .login-mode-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 157, 221, .12);
            color: var(--sumut-accent);
            font-size: 18px;
            flex-shrink: 0;
        }

        .login-mode-btn .login-mode-label {
            min-width: 0;
            flex: 1;
        }

        .login-mode-btn .login-mode-title {
            font-size: .96rem;
            line-height: 1.2;
        }

        .login-mode-btn small {
            font-size: .74rem;
            line-height: 1.25;
        }

        .toggle-password {
            border-radius: 0 14px 14px 0;
        }

        @media (max-width: 991.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 620px;
            }

            .auth-showcase {
                min-height: 320px;
                padding: 30px 26px;
            }

            .showcase-title {
                font-size: 2rem;
                max-width: none;
            }

            .showcase-text,
            .showcase-feature-list {
                max-width: none;
            }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 14px;
            }

            .auth-shell {
                border-radius: 24px;
            }

            .auth-showcase,
            .auth-panel {
                padding: 24px 18px;
            }

            .auth-brand {
                align-items: flex-start;
                gap: 12px;
            }

            .auth-logo {
                width: 50px;
                height: 50px;
            }

            .auth-title {
                font-size: 1.45rem;
            }

            .login-mode-grid {
                grid-template-columns: 1fr;
            }

            .login-mode-btn {
                min-height: 78px;
            }

            .showcase-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-showcase">
            <div>
                <div class="showcase-title">E-TPP</div>
                <div class="showcase-feature-list">
                    <div class="showcase-feature">
                        <i class="bi bi-check2-circle"></i>
                        <div>
                            <strong class="d-block mb-1">Proses lebih efisien</strong>
                            <span>Kelola input TPP, validasi data, dan rekap pegawai dalam satu aplikasi.</span>
                        </div>
                    </div>
                    <div class="showcase-feature">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <div>
                            <strong class="d-block mb-1">Mendukung import data</strong>
                            <span>Integrasi input nilai dan dokumen pendukung untuk mempermudah pekerjaan admin unit.</span>
                        </div>
                    </div>
                    <div class="showcase-feature">
                        <i class="bi bi-graph-up-arrow"></i>
                        <div>
                            <strong class="d-block mb-1">Lebih transparan</strong>
                            <span>Memudahkan pemantauan proses dan hasil pengelolaan TPP secara konsisten.</span>
                        </div>
                    </div>
                </div>

                <div class="faq-shortcut-card">
                    <div class="faq-shortcut-label">
                        <i class="bi bi-patch-question"></i> FAQ (Frequently Asked Questions)
                    </div>
                    <p>Kumpulan pertanyaan umum dan jawaban terkait penggunaan aplikasi TPP yang dapat diakses langsung tanpa login.</p>
                    <a href="{{ route('faq.index') }}" class="btn btn-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Buka FAQ
                    </a>
                </div>
            </div>

            <div class="showcase-footer">
                <div>
                    <strong>E-TPP Pemerintah Provinsi Sumatera Utara</strong>
                    <small>Kantor Gubernur Sumatera Utara</small>
                </div>
                <div>
                    <strong class="d-block">Layanan Internal</strong>
                    <small>Akses hanya untuk pegawai dan admin berwenang</small>
                </div>
            </div>
        </aside>

        <main class="auth-panel">
            <div class="auth-card">
                <div class="auth-brand">
                    <img src="{{ asset('images/logo-sumut.png') }}" alt="Logo Sumatera Utara" class="auth-logo">
                    <div class="auth-brand-text">
                        <h2 class="auth-title">SIMPATI</h2>
                        <p class="auth-subtitle">Sistem Informasi Manajement Perhitungan TPP Yang Interaktif</p>
                        <div class="auth-meta">(Biro Administrasi Pembangunan)</div>
                    </div>
                </div>
                @yield('content', $slot ?? '')
            </div>
        </main>
    </div>
    <script src="{{ asset('template/iportfolio/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
