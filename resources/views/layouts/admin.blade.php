<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Panel Admin SIM Akademik.">
    <title>@yield('title', 'Panel Admin — SIM Akademik')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo_sekolah.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body" data-admin-page="@yield('admin_page', 'dashboard')">
    <a class="btn btn-primary skip-link" href="#main-content">Lewati ke konten</a>
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Navigasi admin desktop">
            <div class="admin-sidebar__brand"><a class="d-flex align-items-center gap-2 text-decoration-none" href="{{ route('admin.dashboard') }}"><span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true"><img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;"></span><span><span class="brand-wordmark d-block text-white">SIM Akademik</span><span class="brand-subtitle d-block text-white-50">SD No. 4 Ungasan</span></span></a></div>
            <nav class="admin-sidebar__scroll">
                @include('layouts.partials.admin-navigation')
            </nav>
            <div class="admin-sidebar__footer">
                <div class="d-flex align-items-center gap-2"><span class="admin-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->username, 0, 2)) }}</span><span class="flex-grow-1"><strong class="d-block text-white small">{{ auth()->user()->username }}</strong><span class="d-block small opacity-50">Administrator</span></span></div>
                <form class="mt-3" action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-sm btn-outline-light w-100" type="submit">Keluar</button></form>
            </div>
        </aside>
        <header class="admin-mobile-header"><div class="container-fluid d-flex align-items-center justify-content-between gap-3 py-2"><a class="d-flex align-items-center gap-2 text-decoration-none" href="{{ route('admin.dashboard') }}"><span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true"><img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;"></span><span class="text-white font-serif">Panel Admin</span></a><button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileNav" aria-controls="adminMobileNav">Menu</button></div></header>
        <div class="offcanvas offcanvas-start admin-offcanvas bg-forest text-white" tabindex="-1" id="adminMobileNav" aria-labelledby="adminMobileNavLabel"><div class="offcanvas-header border-bottom footer-rule"><h2 class="offcanvas-title h4 text-white" id="adminMobileNavLabel">SIM Akademik</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button></div><div class="offcanvas-body d-flex flex-column"><nav class="flex-grow-1">@include('layouts.partials.admin-navigation')</nav><form class="pt-3 mt-3 border-top footer-rule" action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-sm btn-outline-light w-100" type="submit">Keluar</button></form></div></div>
        <main class="admin-main" id="main-content"><div class="admin-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="status"><strong>Berhasil.</strong> {{ session('success') }}<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Belum berhasil.</strong> {{ session('error') }}<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
            @endif
            @yield('content')
        </div></main>
    </div>
    @stack('scripts')
</body>
</html>
