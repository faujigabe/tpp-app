<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-TPP SIMPATI')</title>
    <meta name="description" content="Aplikasi rekapitulasi TPP dan SIPD">

    <link href="{{ asset('template/iportfolio/assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('template/iportfolio/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&family=Raleway:wght@500;600;700&display=swap" rel="stylesheet">

    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('template/iportfolio/assets/css/main.css') }}" rel="stylesheet">

    <style>
        :root {
            --accent-color: #1f7ae0;
            --surface-color: #ffffff;
            --background-color: #f5f8fc;
            --default-color: #27303f;
            --heading-color: #102033;
            --sidebar-expanded-width: 286px;
            --sidebar-collapsed-width: 96px;
        }

        body {
            background: var(--background-color);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .app-shell {
            min-height: 100vh;
            width: 100%;
        }

        .main {
            position: relative;
            min-width: 0;
            width: 100%;
        }

        .header {
            width: var(--sidebar-expanded-width);
            padding: 18px 16px 16px;
            overflow-x: hidden;
            transition: width .28s ease, padding .28s ease, box-shadow .28s ease;
        }

        .header.dark-background {
            background: linear-gradient(180deg, #06111d 0%, #0d233c 100%);
            border-right: 0;
            box-shadow: 10px 0 34px rgba(6, 17, 29, .18);
        }

        .sidebar-header-block {

            padding-top: .15rem;
        }

        .profile-img {
            width: 128px;
            height: 128px;
            margin: 0 auto 1rem;
            padding: 8px;
            border-radius: 50%;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255,255,255,.2), rgba(255,255,255,.05));
            border: 2px solid rgba(255,255,255,.16);
            box-shadow: 0 18px 35px rgba(0,0,0,.22);
        }

        .profile-img .sidebar-brand-image {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 50%;
            object-fit: cover;
            object-position: center center;
            display: block;
            background: rgba(255,255,255,.04);
        }

        .header .logo {
            line-height: 1;
            margin-bottom: .35rem;
        }

        .header .logo h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 700;
            letter-spacing: .08em;
            color: #fff;
        }

        .brand-subtitle {
            color: rgba(255,255,255,.72);
            font-size: .82rem;
            text-align: center;
            line-height: 1.45;
            max-width: 220px;
            margin: 0 auto;
        }

        .sidebar-user-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .85rem;
            margin: 1rem auto 1.1rem;
            padding: 1rem .95rem 1.05rem;
            max-width: 236px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,.16), rgba(255,255,255,.08));
            border: 1px solid rgba(255,255,255,.12);
            color: #fff;
            box-shadow: 0 18px 36px rgba(0,0,0,.18), inset 0 1px 0 rgba(255,255,255,.08);
            transition: transform .28s cubic-bezier(.2,.8,.2,1), box-shadow .28s cubic-bezier(.2,.8,.2,1), border-color .28s ease, background-color .28s ease;
            text-decoration: none;
            overflow: hidden;
            isolation: isolate;
        }

        .sidebar-user-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top, rgba(255,255,255,.16), transparent 58%);
            opacity: .75;
            z-index: -1;
        }

        .sidebar-user-card:hover {
            transform: translateY(-3px) scale(1.01);
            border-color: rgba(255,255,255,.22);
            background: linear-gradient(180deg, rgba(255,255,255,.20), rgba(255,255,255,.10));
            box-shadow: 0 24px 46px rgba(0,0,0,.24), inset 0 1px 0 rgba(255,255,255,.12);
            color: #fff;
        }

        .sidebar-user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid rgba(255,255,255,.16);
            background: radial-gradient(circle at top, rgba(255,255,255,.26), rgba(255,255,255,.08));
            box-shadow: 0 14px 28px rgba(0,0,0,.18);
            flex-shrink: 0;
            transition: transform .28s cubic-bezier(.2,.8,.2,1), box-shadow .28s ease;
        }

        .sidebar-user-avatar.has-photo {
            background: rgba(255,255,255,.08);
        }

        .sidebar-user-card:hover .sidebar-user-avatar {
            transform: scale(1.04);
            box-shadow: 0 18px 30px rgba(0,0,0,.24);
        }

        .sidebar-user-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .sidebar-user-avatar-fallback {
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: #fff;
        }

        .sidebar-user-meta {
            min-width: 0;
            width: 100%;
        }

        .sidebar-user-name {
            font-size: .98rem;
            font-weight: 700;
            line-height: 1.25;
            color: #fff;
            word-break: break-word;
        }

        .sidebar-user-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: .32rem;
            padding: .34rem .7rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #fff;
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.10);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }

        .sidebar-user-role.role-super-admin {
            background: linear-gradient(135deg, rgba(220, 38, 38, .86), rgba(153, 27, 27, .86));
            border-color: rgba(254, 202, 202, .34);
        }

        .sidebar-user-role.role-admin {
            background: linear-gradient(135deg, rgba(37, 99, 235, .88), rgba(29, 78, 216, .88));
            border-color: rgba(191, 219, 254, .34);
        }

        .sidebar-user-role.role-operator {
            background: linear-gradient(135deg, rgba(8, 145, 178, .88), rgba(14, 116, 144, .88));
            border-color: rgba(165, 243, 252, .30);
        }

        .sidebar-user-role.role-viewer {
            background: linear-gradient(135deg, rgba(22, 163, 74, .88), rgba(21, 128, 61, .88));
            border-color: rgba(187, 247, 208, .30);
        }

        .sidebar-user-role.role-default {
            background: linear-gradient(135deg, rgba(71, 85, 105, .88), rgba(51, 65, 85, .88));
            border-color: rgba(226, 232, 240, .25);
        }


        .sidebar-image-trigger {
            cursor: zoom-in;
        }

        .sidebar-image-modal .modal-content {
            background: rgba(7, 14, 24, 0.96);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(0,0,0,.35);
        }

        .sidebar-image-modal .modal-header {
            border-bottom: 1px solid rgba(255,255,255,.08);
            padding: .9rem 1rem;
        }

        .sidebar-image-modal .modal-title {
            color: #fff;
            font-weight: 600;
        }

        .sidebar-image-modal .btn-close {
            filter: invert(1) grayscale(100%);
            opacity: .85;
        }

        .sidebar-image-modal .modal-body {
            padding: 1rem;
        }

        .sidebar-image-preview-wrap {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 240px;
            max-height: calc(100vh - 180px);
            overflow: auto;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01));
        }

        .sidebar-image-preview-wrap img {
            max-width: 100%;
            max-height: calc(100vh - 220px);
            width: auto;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(0,0,0,.28);
            object-fit: contain;
            display: block;
        }

        .sidebar-user-unit {
            margin-top: .35rem;
            font-size: .78rem;
            line-height: 1.35;
            color: rgba(255,255,255,.80);
            word-break: break-word;
        }

        .navmenu {
            margin-top: .85rem;
        }

        .navmenu ul {
            padding: 0;
            margin: 0;
        }

        .navmenu ul {
            list-style: none;
        }

        .nav-section-label {
            margin: 14px 10px 8px;
            padding: 0 4px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, .46);
        }

        .navmenu .dropdown ul {
            display: none;
        }


        .navmenu a,
        .navmenu a:focus {
            color: rgba(226, 232, 240, .86);
            padding: 13px 14px;
            font-family: var(--nav-font);
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-radius: 16px;
            white-space: nowrap;
            width: 100%;
            transition: color .28s ease, background-color .28s ease, transform .28s cubic-bezier(.2,.8,.2,1), box-shadow .28s ease, border-color .28s ease;
            margin-bottom: 8px;
        }

        .navmenu a .navicon,
        .navmenu a:focus .navicon {
            font-size: 20px;
            margin-right: 12px;
            color: rgba(226, 232, 240, .8);
            transition: .25s ease;
        }

        .navmenu a .toggle-dropdown,
        .navmenu a:focus .toggle-dropdown {
            background-color: rgba(255,255,255,.08);
            color: rgba(255,255,255,.86);
        }

        .navmenu a:hover,
        .navmenu .active,
        .navmenu .active:focus {
            color: #fff;
            background: linear-gradient(135deg, rgba(31,122,224,.30), rgba(20,157,221,.20));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
        }

        .navmenu a:hover .navicon,
        .navmenu .active .navicon,
        .navmenu .active:focus .navicon,
        .navmenu a:hover i,
        .navmenu .active i,
        .navmenu .active:focus i {
            color: #fff;
        }

        .navmenu .dropdown ul {
            padding: 8px;
            margin: 4px 0 10px 14px;
            background-color: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            box-shadow: none;
            transition: all .3s ease;
        }

        .navmenu .dropdown ul a,
        .navmenu .dropdown ul a:focus {
            padding: 10px 12px;
            margin-bottom: 0;
            border-radius: 12px;
            font-size: 14px;
        }

        .navmenu .dropdown > .dropdown-active {
            display: block;
            background-color: transparent;
        }

        .sidebar-footer-actions {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            padding-top: 1rem;
            margin-top: auto;
        }

        .sidebar-desktop-toggle {
            min-height: 48px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(255,255,255,.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            font-weight: 600;
            box-shadow: none;
        }

        .sidebar-desktop-toggle:hover {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.22);
            color: #fff;
        }

        .sidebar-action-label {
            white-space: nowrap;
        }

        .sidebar-toggle-btn {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, .08);
            background: #fff;
            color: #102033;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            flex-shrink: 0;
        }

        .sidebar-toggle-btn:hover {
            background: #f8fafc;
        }

        #header .header-toggle {
            color: #fff;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.12);
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            cursor: pointer;
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 12;
            transition: background-color .3s ease, transform .2s ease;
        }

        #header .header-toggle:hover {
            color: #fff;
            background: rgba(255,255,255,.18);
            transform: translateY(-1px);
        }

        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: transparent;
            padding: 0;
        }

        .app-topbar-inner {
            width: 100%;
            max-width: none;
            margin: 0;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(15, 23, 42, .06);
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            padding-top: .45rem !important;
            padding-bottom: .45rem !important;
        }

        .topbar-title-wrap {
            display: flex;
            align-items: center;
            gap: .9rem;
            min-width: 0;
            flex: 1 1 auto;
        }

        .page-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.15;
            color: #0f172a;
        }

        .page-subtitle {
            color: #6b7280;
            margin: .12rem 0 0;
            font-size: .78rem;
            line-height: 1.25;
            max-width: none;
        }

        .app-content { padding: 18px 0 22px; }

        .app-card,
        .card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .08);
        }

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(15, 23, 42, .06);
            padding-top: 1.2rem;
        }

        .table > :not(caption) > * > * { padding: .9rem .85rem; }

        .table-responsive {
            border-radius: 18px;
            border: 1px solid rgba(15, 23, 42, .06);
            background: #fff;
        }

        .table thead th {
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #475467;
            background: #f8fafc !important;
            border-bottom-color: #e5e7eb;
            white-space: nowrap;
        }

        .table tbody td { border-color: #eef2f7; }
        .table tbody tr:hover td { background: #fbfdff; }
        .table td .badge { border-radius: 999px; padding: .5rem .75rem; }
        .btn, .form-control, .form-select, .input-group-text { border-radius: 14px; }
        .btn { font-weight: 600; }
        .btn-icon { display: inline-flex; align-items: center; gap: .5rem; }
        .form-label { font-weight: 600; color: #344054; margin-bottom: .55rem; }

        .form-control,
        .form-select {
            min-height: 48px;
            border: 1px solid #d0d5dd;
            box-shadow: none;
        }

        textarea.form-control { min-height: 110px; }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(31, 122, 224, .55);
            box-shadow: 0 0 0 .2rem rgba(31, 122, 224, .12);
        }

        .input-group-text {
            border: 1px solid #d0d5dd;
            background: #f8fafc;
            color: #475467;
        }

        .card-header .small.text-muted,
        .text-muted { color: #667085 !important; }

        .shadow-soft { box-shadow: 0 14px 40px rgba(15, 23, 42, .08); }

        .form-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #101828;
            margin-bottom: .2rem;
        }

        .form-section-subtitle {
            font-size: .92rem;
            color: #667085;
            margin-bottom: 0;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #667085;
        }

        .pagination { gap: .35rem; }

        .page-link {
            border: 0;
            border-radius: 12px !important;
            color: #344054;
            padding: .65rem .9rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
        }

        .page-item.active .page-link {
            background: #1f7ae0;
            color: #fff;
        }

        .alert { border: 0; border-radius: 16px; }
        .content-section { background: transparent; }
        .mobile-logout { display: none; }

        @media (min-width: 1200px) {
            #header {
                left: 0;
                width: var(--sidebar-expanded-width);
            }

            .header ~ main,
            .header ~ #footer,
            .main {
                margin-left: var(--sidebar-expanded-width);
                width: calc(100% - var(--sidebar-expanded-width));
                transition: margin-left .28s ease, width .28s ease;
            }

            body.sidebar-collapsed #header {
                width: var(--sidebar-collapsed-width);
                padding-left: 10px;
                padding-right: 10px;
            }

            body.sidebar-collapsed .header ~ main,
            body.sidebar-collapsed .header ~ #footer,
            body.sidebar-collapsed .main {
                margin-left: var(--sidebar-collapsed-width) !important;
                width: calc(100% - var(--sidebar-collapsed-width));
            }

            body.sidebar-collapsed .profile-img {
                width: 62px;
                height: 62px;
                padding: 4px;
                margin-bottom: .8rem;
            }

            body.sidebar-collapsed .sidebar-user-card {
                width: 62px;
                min-height: 62px;
                margin: .85rem auto 1rem;
                padding: 0;
                border-radius: 999px;
                background: transparent;
                border: 0;
                box-shadow: none;
                gap: 0;
            }

            body.sidebar-collapsed .sidebar-user-card::before,
            body.sidebar-collapsed .sidebar-user-meta,
            body.sidebar-collapsed .sidebar-header-block .logo,
            body.sidebar-collapsed .brand-subtitle,
            body.sidebar-collapsed .sidebar-action-label,
            body.sidebar-collapsed .navmenu a > span,
            body.sidebar-collapsed .navmenu a .toggle-dropdown,
            body.sidebar-collapsed .nav-section-label,
            body.sidebar-collapsed .mobile-logout {
                display: none !important;
            }

            body.sidebar-collapsed .sidebar-user-card:hover {
                transform: translateY(-2px);
                background: transparent;
                border-color: transparent;
                box-shadow: none;
            }

            body.sidebar-collapsed .sidebar-user-avatar {
                width: 62px;
                height: 62px;
                border-width: 2px;
                box-shadow: 0 12px 22px rgba(0,0,0,.18);
            }

            body.sidebar-collapsed .sidebar-user-avatar-fallback {
                font-size: 1rem;
            }

            body.sidebar-collapsed .navmenu a,
            body.sidebar-collapsed .navmenu a:focus {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            body.sidebar-collapsed .navmenu a .navicon,
            body.sidebar-collapsed .navmenu a:focus .navicon {
                margin-right: 0;
                font-size: 1.2rem;
            }

            body.sidebar-collapsed .navmenu .dropdown ul {
                display: none !important;
            }

            body.sidebar-collapsed .sidebar-footer-actions .btn {
                padding-left: 0;
                padding-right: 0;
                border-radius: 16px;
            }

            body.sidebar-collapsed .sidebar-footer-actions .btn i {
                margin-right: 0 !important;
            }
        }

        @media (max-width: 1199px) {
            #header {
                width: min(86vw, 320px);
                max-width: 320px;
            }

            .main,
            .header ~ main,
            .header ~ #footer {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .mobile-logout { display: block; }
            .app-topbar {
                padding: 0;
            }
            .app-topbar-inner {
                border-radius: 0 0 16px 16px;
                padding-top: .42rem !important;
                padding-bottom: .42rem !important;
            }
            .app-content {
                padding-top: 16px;
            }
            .page-title {
                font-size: 1rem;
            }
            .page-subtitle {
                font-size: .75rem;
            }
            .profile-img {
                width: 118px;
                height: 118px;
            }

            .sidebar-user-card {
                max-width: 100%;
                margin-top: .9rem;
            }

            .sidebar-user-avatar {
                width: 78px;
                height: 78px;
            }
        }

        /* Sidebar polish */
        .sidebar-header-block {
            padding-bottom: .5rem;
            margin-bottom: .2rem;
        }

        .app-topbar-inner {
            max-width: none;
        }

        .page-subtitle {
            max-width: none;
        }

        .navmenu {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
        }

        .navmenu ul {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .navmenu li {
            list-style: none;
        }

        .navmenu a,
        .navmenu a:focus {
            position: relative;
            min-height: 52px;
            margin-bottom: 0;
            padding: 12px 14px 12px 18px;
            border: 1px solid transparent;
            background: transparent;
        }

        .navmenu a::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 50%;
            width: 4px;
            height: 22px;
            border-radius: 999px;
            background: transparent;
            transform: translateY(-50%);
            transition: background-color .25s ease, opacity .25s ease;
            opacity: 0;
        }

        .navmenu a .navicon,
        .navmenu a:focus .navicon {
            width: 22px;
            margin-right: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .navmenu a .toggle-dropdown,
        .navmenu a:focus .toggle-dropdown {
            width: 26px;
            height: 26px;
            margin-left: auto;
            border-radius: 999px;
            transition: transform .25s ease, background-color .25s ease;
        }

        .navmenu a:hover,
        .navmenu a:hover:focus,
        .navmenu > ul > li > a.active,
        .navmenu > ul > li > a.active:focus,
        .navmenu .menu-open > a,
        .navmenu .menu-open > a:focus {
            color: #fff;
            background: linear-gradient(135deg, rgba(31,122,224,.34), rgba(22,93,178,.22));
            border-color: rgba(255,255,255,.08);
            box-shadow: 0 14px 28px rgba(2, 12, 27, .18);
            transform: translateX(2px);
        }

        .navmenu a:hover::before,
        .navmenu > ul > li > a.active::before,
        .navmenu .menu-open > a::before {
            background: rgba(255,255,255,.95);
            opacity: 1;
        }

        .navmenu a:hover .navicon,
        .navmenu .menu-open > a .navicon,
        .navmenu > ul > li > a.active .navicon,
        .navmenu a:hover i,
        .navmenu .menu-open > a i,
        .navmenu > ul > li > a.active i {
            color: #fff;
        }

        .navmenu .menu-open > a .toggle-dropdown,
        .navmenu > ul > li > a.active .toggle-dropdown {
            background: rgba(255,255,255,.14);
            transform: rotate(180deg);
        }

        .navmenu .dropdown ul {
            padding: 8px;
            margin: 2px 0 4px 12px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,.08);
            background-color: rgba(255,255,255,.05);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.02);
        }

        .navmenu .dropdown ul li + li {
            margin-top: 4px;
        }

        .navmenu .dropdown ul a,
        .navmenu .dropdown ul a:focus {
            min-height: 44px;
            padding: 10px 12px 10px 14px;
            border-radius: 14px;
            border: 1px solid transparent;
            color: rgba(226, 232, 240, .8);
            background: rgba(255,255,255,.02);
        }

        .navmenu .dropdown ul a::before {
            left: 9px;
            width: 6px;
            height: 6px;
            background: rgba(255,255,255,.34);
            opacity: 1;
        }

        .navmenu .dropdown ul a:hover,
        .navmenu .dropdown ul a.active,
        .navmenu .dropdown ul a.active:focus {
            color: #fff;
            background: rgba(255,255,255,.11);
            border-color: rgba(255,255,255,.07);
            box-shadow: none;
        }

        .navmenu .dropdown ul a:hover::before,
        .navmenu .dropdown ul a.active::before {
            background: #fff;
        }

        .sidebar-footer-actions {
            gap: .8rem;
            padding-top: 1.1rem;
            margin-top: 1.2rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-desktop-toggle,
        .sidebar-footer-actions .btn-primary {
            min-height: 48px;
            font-size: .95rem;
            letter-spacing: .01em;
        }

        .sidebar-footer-actions .btn-primary {
            background: linear-gradient(135deg, #1f7ae0, #165db2);
            border-color: rgba(255,255,255,.06);
            box-shadow: 0 12px 24px rgba(10, 25, 45, .2);
        }

        .sidebar-footer-actions .btn-primary:hover {
            background: linear-gradient(135deg, #2b86ec, #1965c1);
        }

        @media (min-width: 1200px) {
            body.sidebar-collapsed .navmenu ul {
                gap: 8px;
            }

            body.sidebar-collapsed .navmenu a::before {
                display: none;
            }

            body.sidebar-collapsed .sidebar-footer-actions {
                padding-top: .85rem;
            }
        }

        @media (max-width: 1199px) {
            .navmenu ul {
                gap: 6px;
            }

            .sidebar-footer-actions {
                padding-top: .95rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    @include('partials.navbar')

    <main class="main">
        <div class="app-topbar">
            <div class="app-topbar-inner px-3 px-lg-4 py-1 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                <div class="topbar-title-wrap">
                    <button type="button" class="sidebar-toggle-btn d-xl-none" id="mobileSidebarOpen" aria-label="Buka sidebar" title="Buka sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <div class="page-title">@yield('title', 'E-TPP SIMPATI')</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-start justify-content-lg-end">
                    @auth
                        <span class="badge rounded-pill text-bg-light px-3 py-2 text-dark border">{{ match(Auth::user()->role) { 'viewer' => 'Pegawai', 'super_admin' => 'Super Admin', 'admin' => 'Admin', 'operator' => 'Operator', default => str_replace('_', ' ', Auth::user()->role) } }}</span>
                        <span class="badge rounded-pill text-bg-light px-3 py-2 text-dark border">{{ Auth::user()->unitKerja->nama_unit ?? 'Semua Unit' }}</span>
                        <span class="badge rounded-pill px-3 py-2" style="background:#102033;color:#fff;">{{ Auth::user()->name }}</span>
                    @endauth
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 px-lg-5 app-content">
            @if(session('success'))
                <div class="alert alert-success shadow-sm" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <section class="content-section" data-aos="fade-up" data-aos-delay="100">
                @yield('content')
            </section>
        </div>
    </main>
</div>


<div class="modal fade sidebar-image-modal" id="sidebarImageModal" tabindex="-1" aria-labelledby="sidebarImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sidebarImageModalLabel">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="sidebar-image-preview-wrap">
                    <img src="" alt="Preview gambar sidebar" id="sidebarImageModalPreview">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('template/iportfolio/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/iportfolio/assets/vendor/aos/aos.js') }}"></script>
<script src="{{ asset('template/iportfolio/assets/js/main.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.AOS) {
            AOS.init({ duration: 650, once: true });
        }

        const body = document.body;
        const header = document.getElementById('header');
        const toggleBtn = document.getElementById('sidebarCollapseToggle');
        const mobileSidebarOpen = document.getElementById('mobileSidebarOpen');
        const mobileHeaderToggle = document.querySelector('#header .header-toggle');
        const dropdownTriggers = document.querySelectorAll('#header .navmenu .dropdown > a');
        const dropdownToggles = document.querySelectorAll('#header .navmenu .toggle-dropdown');
        const storageKey = 'etpp-sidebar-collapsed';
        const imageModalElement = document.getElementById('sidebarImageModal');
        const imageModalPreview = document.getElementById('sidebarImageModalPreview');
        const imageModalLabel = document.getElementById('sidebarImageModalLabel');
        const imageTriggers = document.querySelectorAll('[data-sidebar-image-preview]');
        const imageModalInstance = imageModalElement && window.bootstrap ? new bootstrap.Modal(imageModalElement) : null;

        const syncSidebarState = () => {
            const isCollapsed = body.classList.contains('sidebar-collapsed');

            if (toggleBtn) {
                toggleBtn.innerHTML = isCollapsed
                    ? '<i class="bi bi-layout-sidebar"></i><span class="sidebar-action-label">Lebarkan Sidebar</span>'
                    : '<i class="bi bi-layout-sidebar-inset"></i><span class="sidebar-action-label">Ciutkan Sidebar</span>';
                toggleBtn.setAttribute('aria-label', isCollapsed ? 'Lebarkan sidebar' : 'Ciutkan sidebar');
                toggleBtn.setAttribute('title', isCollapsed ? 'Lebarkan sidebar' : 'Ciutkan sidebar');
            }
        };

        const syncMobileSidebarState = () => {
            if (!mobileSidebarOpen || !header) return;

            const isOpen = header.classList.contains('header-show');
            mobileSidebarOpen.innerHTML = isOpen
                ? '<i class="bi bi-x-lg"></i>'
                : '<i class="bi bi-list"></i>';
            mobileSidebarOpen.setAttribute('aria-label', isOpen ? 'Tutup sidebar' : 'Buka sidebar');
            mobileSidebarOpen.setAttribute('title', isOpen ? 'Tutup sidebar' : 'Buka sidebar');
        };

        if (window.innerWidth >= 1200 && localStorage.getItem(storageKey) === 'true') {
            body.classList.add('sidebar-collapsed');
        }

        syncSidebarState();
        syncMobileSidebarState();

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                if (window.innerWidth < 1200) return;

                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(storageKey, body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
                syncSidebarState();
            });
        }

        if (mobileSidebarOpen && mobileHeaderToggle) {
            mobileSidebarOpen.addEventListener('click', function () {
                mobileHeaderToggle.click();
                setTimeout(syncMobileSidebarState, 10);
            });
        }

        dropdownTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                if (window.innerWidth >= 1200 && body.classList.contains('sidebar-collapsed')) {
                    event.preventDefault();
                    body.classList.remove('sidebar-collapsed');
                    localStorage.setItem(storageKey, 'false');
                    syncSidebarState();
                }
            });
        });

        dropdownToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const parentItem = this.closest('li.dropdown');
                const parentLink = this.parentElement;
                const submenu = parentItem ? parentItem.querySelector(':scope > ul') : null;

                setTimeout(function () {
                    const isOpen = Boolean(submenu && submenu.classList.contains('dropdown-active'));

                    if (parentItem) {
                        parentItem.classList.toggle('menu-open', isOpen);
                    }

                    if (parentLink) {
                        parentLink.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    }
                }, 0);
            });
        });


        imageTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const imageSrc = trigger.getAttribute('data-image-src');
                const imageTitle = trigger.getAttribute('data-image-title') || 'Preview Gambar';

                if (!imageSrc || !imageModalPreview || !imageModalInstance) {
                    return;
                }

                imageModalPreview.setAttribute('src', imageSrc);
                imageModalPreview.setAttribute('alt', imageTitle);

                if (imageModalLabel) {
                    imageModalLabel.textContent = imageTitle;
                }

                imageModalInstance.show();
            });
        });

        if (imageModalElement) {
            imageModalElement.addEventListener('hidden.bs.modal', function () {
                if (imageModalPreview) {
                    imageModalPreview.setAttribute('src', '');
                    imageModalPreview.setAttribute('alt', 'Preview gambar sidebar');
                }

                if (imageModalLabel) {
                    imageModalLabel.textContent = 'Preview Gambar';
                }
            });
        }

        if (header && window.MutationObserver) {
            const observer = new MutationObserver(syncMobileSidebarState);
            observer.observe(header, { attributes: true, attributeFilter: ['class'] });
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth < 1200) {
                body.classList.remove('sidebar-collapsed');
            } else if (localStorage.getItem(storageKey) === 'true') {
                body.classList.add('sidebar-collapsed');
            }

            syncSidebarState();
            syncMobileSidebarState();
        });
    });
</script>
@stack('scripts')
</body>
</html>
