<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portal siswa read-only berisi identitas ringkas dan jadwal kelas.">
    <title>@yield('title', 'Jadwal Saya — Portal Siswa')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo_sekolah.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body">
    <a class="btn btn-primary skip-link" href="#main-content">Lewati ke konten</a>
    <header class="portal-header sticky-top">
        <nav class="navbar navbar-dark navbar-expand" aria-label="Navigasi portal siswa">
            <div class="container py-2 d-flex flex-wrap gap-3 align-items-center">
                <a class="navbar-brand d-flex align-items-center gap-2 me-3" href="{{ route('student.home') }}">
                    <span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true">
                        <img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;">
                    </span>
                    <span class="d-flex flex-column"><span class="brand-wordmark">Portal Siswa</span><span class="brand-subtitle">{{ $schoolProfile?->school_name ?? 'SD No. 4 Ungasan' }}</span></span>
                </a>
                <ul class="navbar-nav me-auto flex-row gap-3">
                    <li class="nav-item">
                        <a class="nav-link px-2 py-1 rounded {{ request()->routeIs('student.home') ? 'active text-white fw-semibold bg-white bg-opacity-10' : 'text-white-50' }}" href="{{ route('student.home') }}">Jadwal Belajar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2 py-1 rounded {{ request()->routeIs('student.journal.*') ? 'active text-white fw-semibold bg-white bg-opacity-10' : 'text-white-50' }}" href="{{ route('student.journal.index') }}">Jurnal 7 Kebiasaan</a>
                    </li>
                </ul>
                <div class="portal-user d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="portal-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->student?->name ?? auth()->user()->username, 0, 2)) }}</span>
                        <span><span class="portal-user-name d-block">{{ auth()->user()->student?->name ?? auth()->user()->username }}</span><span class="portal-user-meta d-block">{{ auth()->user()->student ? 'NISN '.auth()->user()->student->nisn : 'Profil belum terhubung' }}</span></span>
                    </div>
                    <form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-sm btn-outline-light" type="submit">Keluar</button></form>
                </div>
            </div>
        </nav>
    </header>
    <main class="portal-main" id="main-content">@yield('content')</main>
    <footer class="portal-footer py-4"><div class="container d-flex flex-column flex-sm-row justify-content-between gap-2 small"><span>Portal Siswa · {{ $schoolProfile?->school_name ?? 'SIM Akademik Sekolah' }}</span><span>Data periode aktif · Asia/Makassar</span></div></footer>
</body>
</html>
