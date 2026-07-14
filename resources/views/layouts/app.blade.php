<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory Management System for SPDRRMO')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --ink: #14262b;
            --muted: #64777c;
            --sidebar: #0f2b31;
            --sidebar-deep: #0b2126;
            --accent: #e8930c;
            --accent-soft: rgba(232,147,12,0.14);
            --surface: #f4f6f4;
            --card: #ffffff;
            --line: #e2e6e2;
        }
        * { font-family: 'Archivo', system-ui, sans-serif; }
        body { background-color: var(--surface); color: var(--ink); }

        .sidebar {
            width: 244px; min-height: 100vh;
            background-color: var(--sidebar);
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column;
        }
        .brand-block {
            background-color: var(--sidebar-deep);
            padding: 1.15rem 1.25rem;
            display: flex; align-items: center; gap: 0.7rem;
            text-decoration: none;
        }
        .brand-mark {
            width: 34px; height: 34px; border-radius: 8px;
            background-color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            color: var(--sidebar-deep); font-weight: 800; font-size: 1.05rem;
            flex-shrink: 0;
        }
        .brand-text { line-height: 1.1; }
        .brand-text .name { color: #fff; font-weight: 700; font-size: 1rem; letter-spacing: .02em; }
        .brand-text .sub { color: rgba(255,255,255,.55); font-size: .68rem; letter-spacing: .09em; text-transform: uppercase; }

        .sidebar .nav { padding: 0.75rem 0; }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.78);
            padding: 0.7rem 1.25rem;
            display: flex; align-items: center; gap: 0.7rem;
            border-left: 3px solid transparent;
            font-size: 0.94rem; font-weight: 500;
            transition: background-color .15s ease, color .15s ease;
        }
        .sidebar .nav-link i { font-size: 1.05rem; opacity: .85; }
        .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.06); color: #fff; }
        .sidebar .nav-link.active {
            background-color: rgba(232,147,12,0.10);
            color: #fff; border-left-color: var(--accent); font-weight: 600;
        }
        .sidebar .nav-link.active i { color: var(--accent); opacity: 1; }
        .sidebar-foot { margin-top: auto; padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.08); }
        .sidebar-foot .small { color: rgba(255,255,255,.4); font-size: .68rem; letter-spacing: .04em; }

        .content-wrap { margin-left: 244px; }
        .topbar {
            background-color: var(--card);
            border-bottom: 1px solid var(--line);
            padding: 0.7rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar .eyebrow { font-size: .66rem; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
        .topbar .page-title { font-size: 1.12rem; font-weight: 700; color: var(--ink); line-height: 1.15; }

        .role-badge {
            font-size: .66rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: .18rem .5rem; border-radius: 999px; vertical-align: middle;
        }
        .role-admin { background-color: var(--accent-soft); color: #b9700a; border: 1px solid rgba(232,147,12,.3); }
        .role-staff { background-color: #e8edea; color: #4a5b60; border: 1px solid #d3dcd7; }
        .user-name { font-weight: 600; font-size: .92rem; color: var(--ink); }

        .card { border: 1px solid var(--line); box-shadow: none; border-radius: 10px; }
        .card .table thead.table-light th { background-color: #f1f4f1; color: var(--muted); font-weight: 600; font-size: .82rem; letter-spacing: .02em; }

        .btn-primary { background-color: var(--sidebar); border-color: var(--sidebar); }
        .btn-primary:hover, .btn-primary:focus { background-color: #0b2126; border-color: #0b2126; }

        .notif-item { transition: background-color 0.15s ease; border-radius: 6px; }
        .notif-item:hover { background-color: #f1f5f9; }

        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; min-height: auto; }
            .content-wrap { margin-left: 0; }
        }
    </style>
</head>
<body>
    @auth
    @include('partials.sidebar')

    <div class="content-wrap">
        @include('partials.topbar')

        <main class="container-fluid py-4 px-4">
            @if(session('success'))
    <div id="flash-success" data-message="{{ session('success') }}" hidden></div>
    @push('scripts')
    <script>
        (function () {
            var el = document.getElementById('flash-success');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: el.dataset.message,
                timer: 2000,
                showConfirmButton: false
            });
        })();
    </script>
    @endpush
@endif

@if(session('error'))
    <div id="flash-error" data-message="{{ session('error') }}" hidden></div>
    @push('scripts')
    <script>
        (function () {
            var el = document.getElementById('flash-error');
            Swal.fire({
                icon: 'error',
                title: 'Oops',
                text: el.dataset.message
            });
        })();
    </script>
    @endpush
@endif
            @yield('content')
        </main>
    </div>
    @endauth

    @guest
    <main class="container py-4">
        @yield('content')
    </main>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
</script>
    @stack('scripts')
</body>
</html>