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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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