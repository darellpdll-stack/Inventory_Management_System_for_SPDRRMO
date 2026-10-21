<!DOCTYPE html>
<html lang="en">
<head>
    <script>
    (function () {
        try {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        } catch (e) {}
    })();
</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory Management System for SPDRRMO')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v=8" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

</head>
<body>
    @auth
    @include('partials.sidebar')

    {{-- shade behind the drawer on narrow screens; tapping it closes the menu --}}
    <div class="nav-backdrop" id="navBackdrop" hidden></div>

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
    <script>
    (function () {
        const root = document.documentElement;
        const toggle = document.getElementById('themeToggle');
        if (!toggle) return;

        toggle.checked = root.getAttribute('data-bs-theme') === 'dark';

        toggle.addEventListener('change', function () {
            const next = toggle.checked ? 'dark' : 'light';
            root.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        });
    })();
</script>
    <script>
    (function () {
        var toggle = document.getElementById('navToggle');
        var sidebar = document.querySelector('.sidebar');
        var backdrop = document.getElementById('navBackdrop');

        if (!toggle || !sidebar || !backdrop) return;

        function setOpen(open) {
            sidebar.classList.toggle('is-open', open);
            backdrop.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            // stop the page scrolling underneath the drawer
            document.body.style.overflow = open ? 'hidden' : '';
        }

        toggle.addEventListener('click', function () {
            setOpen(!sidebar.classList.contains('is-open'));
        });

        backdrop.addEventListener('click', function () { setOpen(false); });

        // following a link should leave the menu behind
        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a')) setOpen(false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });

        // a drawer left open while the window grows would strand the backdrop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) setOpen(false);
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>