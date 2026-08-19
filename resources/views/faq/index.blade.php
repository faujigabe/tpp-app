<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FAQ Aplikasi TPP</title>
    <link href="{{ asset('template/iportfolio/assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        :root {
            --faq-primary: #0b2e59;
            --faq-secondary: #0f5fa8;
            --faq-accent: #149ddd;
            --faq-success: #1a8f6d;
            --faq-surface: #ffffff;
            --faq-background: #f3f7fb;
            --faq-text: #0f172a;
            --faq-muted: #64748b;
        }
        body {
            background: linear-gradient(180deg, #eef4fb 0%, #f9fbfd 100%);
            color: var(--faq-text);
        }
        .hero {
            background: linear-gradient(135deg, rgba(11,46,89,.96), rgba(15,95,168,.88));
            color: #fff;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .12);
        }
        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 999px;
            font-size: .92rem;
            font-weight: 600;
        }
        .hero-search,
        .faq-search {
            border-radius: 16px;
            padding: .95rem 1rem;
            border: 1px solid #d6e1ee;
            box-shadow: none;
        }
        .hero-search:focus,
        .faq-search:focus {
            border-color: var(--faq-accent);
            box-shadow: 0 0 0 .2rem rgba(20,157,221,.12);
        }
        .summary-card,
        .category-card,
        .faq-card,
        .contact-card {
            background: var(--faq-surface);
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
        }
        .summary-card {
            padding: .9rem .8rem;
            min-height: 132px;
            height: 100%;
        }
        .summary-card .stat-number {
            color: var(--faq-primary);
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .summary-card .stat-label {
            color: var(--faq-muted);
            font-size: .82rem;
        }
        .summary-card .icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(20,157,221,.12);
            color: var(--faq-accent);
            font-size: 1rem;
            margin-bottom: .55rem;
        }
        .category-card {
            padding: 1.1rem 1.2rem;
            transition: transform .18s ease, box-shadow .18s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 46px rgba(15, 23, 42, .1);
            color: inherit;
        }
        .category-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(26,143,109,.12);
            color: var(--faq-success);
            font-size: 1.05rem;
            margin-bottom: .85rem;
        }
        .faq-section-title {
            color: var(--faq-primary);
            font-weight: 800;
        }
        .faq-card {
            padding: 1.35rem;
        }
        .faq-question {
            font-weight: 700;
            color: var(--faq-primary);
            margin-bottom: .55rem;
        }
        .faq-answer,
        .faq-solution {
            color: #334155;
            line-height: 1.7;
            margin-bottom: .55rem;
        }
        .faq-solution strong,
        .faq-answer strong {
            color: var(--faq-primary);
        }
        .section-anchor {
            scroll-margin-top: 88px;
        }
        .topbar-link {
            border-radius: 999px;
            padding: .7rem 1rem;
            font-weight: 600;
        }
        .topbar-link.btn-outline-light:hover {
            color: var(--faq-primary);
        }
        .back-to-login {
            background: #fff;
            color: var(--faq-primary);
            border-radius: 999px;
            font-weight: 700;
            padding: .78rem 1.15rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .back-to-login:hover { color: var(--faq-primary); }
        .faq-hidden { display: none !important; }
        .badge-soft {
            background: rgba(20,157,221,.12);
            color: var(--faq-secondary);
            border-radius: 999px;
            padding: .4rem .7rem;
            font-size: .8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <section class="hero mb-5">
        <div class="container py-4 py-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
                <div>
                    <span class="hero-chip mb-3"><i class="bi bi-patch-question"></i> Portal Bantuan Publik</span>
                    <h1 class="display-5 fw-bold mb-3">FAQ Aplikasi TPP</h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="back-to-login"><i class="bi bi-box-arrow-in-right"></i> Kembali ke Login</a>
                </div>
            </div>

            <div class="row g-3 align-items-stretch">
                <div class="col-lg-7">
                    <input id="faqSearch" type="text" class="form-control hero-search" placeholder="Cari pertanyaan, misalnya: login, import pegawai, submit periode, export...">
                </div>
                <div class="col-lg-5">
                    <div class="row g-3 h-100">
                        <div class="col-sm-4">
                            <div class="summary-card text-center">
                                <div class="icon-wrap mx-auto"><i class="bi bi-folder2-open"></i></div>
                                <div class="stat-number">{{ count($faqCategories) }}</div>
                                <div class="stat-label">Kategori</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="summary-card text-center">
                                <div class="icon-wrap mx-auto"><i class="bi bi-chat-left-text"></i></div>
                                <div class="stat-number">{{ $totalQuestions }}</div>
                                <div class="stat-label">Pertanyaan</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="summary-card text-center">
                                <div class="icon-wrap mx-auto"><i class="bi bi-lightning-charge"></i></div>
                                <div class="stat-number">24/7</div>
                                <div class="stat-label">Akses FAQ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container pb-5">
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="faq-section-title mb-1">Kategori FAQ</h2>
                    <p class="text-muted mb-0">Pilih kategori untuk langsung menuju topik yang dibutuhkan.</p>
                </div>
                <span class="badge-soft">Akses tanpa login</span>
            </div>
            <div class="row g-3" id="categoryGrid">
                @foreach ($faqCategories as $category)
                    <div class="col-md-6 col-xl-4 category-item">
                        <a href="#{{ $category['slug'] }}" class="category-card">
                            <span class="category-icon"><i class="bi {{ $category['icon'] }}"></i></span>
                            <h3 class="h5 mb-2">{{ $category['title'] }}</h3>
                            <p class="text-muted mb-0">{{ count($category['items']) }} pertanyaan tersedia pada kategori ini.</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mb-4">
            <div class="contact-card p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 mb-2 faq-section-title">Belum menemukan jawaban?</h2>
                        <p class="text-muted mb-0">Silakan gunakan kolom pencarian di atas. Jika kendala masih berlanjut, hubungi admin aplikasi atau operator unit kerja dengan menyertakan detail pesan error dan langkah yang sudah dilakukan.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-arrow-left-circle me-2"></i>Ke Halaman Login
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="faqContent" class="d-grid gap-4">
            @foreach ($faqCategories as $category)
                <div class="faq-group section-anchor" id="{{ $category['slug'] }}" data-category-title="{{ strtolower($category['title']) }}">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="category-icon mb-0"><i class="bi {{ $category['icon'] }}"></i></span>
                        <div>
                            <h2 class="h3 mb-1 faq-section-title">{{ $category['title'] }}</h2>
                            <p class="text-muted mb-0">{{ count($category['items']) }} pertanyaan dalam kategori ini.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach ($category['items'] as $item)
                            <div class="col-12 faq-item" data-search="{{ strtolower($category['title'].' '.$item['question'].' '.$item['answer'].' '.$item['solution']) }}">
                                <div class="faq-card h-100">
                                    <div class="faq-question">{{ $item['question'] }}</div>
                                    <div class="faq-answer"><strong>Jawaban:</strong> {{ $item['answer'] }}</div>
                                    <div class="faq-solution mb-0"><strong>Solusi:</strong> {{ $item['solution'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>

        <div id="emptyState" class="contact-card p-5 text-center faq-hidden mt-4">
            <div class="mb-3 text-primary fs-1"><i class="bi bi-search"></i></div>
            <h3 class="h4 faq-section-title">Pertanyaan tidak ditemukan</h3>
            <p class="text-muted mb-0">Coba gunakan kata kunci lain seperti login, pegawai, kelas jabatan, perhitungan, submit, atau export.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('faqSearch');
            const faqItems = Array.from(document.querySelectorAll('.faq-item'));
            const faqGroups = Array.from(document.querySelectorAll('.faq-group'));
            const emptyState = document.getElementById('emptyState');

            function applySearch() {
                const keyword = (searchInput.value || '').toLowerCase().trim();
                let visibleCount = 0;

                faqItems.forEach(function (item) {
                    const text = item.dataset.search || '';
                    const match = keyword === '' || text.includes(keyword);
                    item.classList.toggle('faq-hidden', !match);
                    if (match) {
                        visibleCount += 1;
                    }
                });

                faqGroups.forEach(function (group) {
                    const visibleItems = group.querySelectorAll('.faq-item:not(.faq-hidden)').length;
                    group.classList.toggle('faq-hidden', visibleItems === 0);
                });

                emptyState.classList.toggle('faq-hidden', visibleCount > 0);
            }

            searchInput.addEventListener('input', applySearch);
        });
    </script>
</body>
</html>
