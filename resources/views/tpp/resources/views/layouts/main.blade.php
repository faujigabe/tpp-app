<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aplikasi TPP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --tpp-radius: 14px; }
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f6f7fb;
        }
        .page-wrap { min-height: calc(100vh - 56px); }
        .container-page { max-width: 1200px; }
        .card { border: 0; border-radius: var(--tpp-radius); }
        .shadow-soft { box-shadow: 0 10px 30px rgba(16, 24, 40, .08) !important; }
        .table thead th { position: sticky; top: 0; z-index: 2; }
        .btn { border-radius: 12px; }
        .btn-icon { display: inline-flex; align-items: center; gap: .4rem; }
        .form-control, .form-select { border-radius: 12px; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .12); }
    </style>
    @stack('styles')
</head>
<body>
    {{-- navbar bootstrap kamu --}}
    @include('partials.navbar') {{-- optional, kalau kamu pisah navbar --}}
    <div class="page-wrap">
        <div class="container container-page py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-soft" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-soft" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>