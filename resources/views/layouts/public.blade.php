@php
    $schoolProfile = $schoolProfile ?? null;
    $publicSchoolName = $schoolProfile?->school_name ?: 'SD No. 4 Ungasan';
    $publicSchoolPhone = $schoolProfile?->phone ?: '(0361) 701 234';
    $publicSchoolEmail = $schoolProfile?->email ?: 'info@sd4ungasan.sch.id';
    $publicSchoolAddress = $schoolProfile?->address ?: 'Jl. Uluwatu, Ungasan, Kuta Selatan, Badung, Bali';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', $publicSchoolName.' — unggul, berkarakter, dan berdampak.')">
    <title>@yield('title', $publicSchoolName)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="btn btn-primary skip-link" href="#main-content">Lewati ke konten</a>
    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-lg" aria-label="Navigasi utama">
            <div class="container py-2">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}" aria-label="{{ $publicSchoolName }}, beranda">
                    <span class="school-mark overflow-hidden p-0" aria-hidden="true">
                        @if ($schoolProfile?->logo_path)
                            <img class="h-100 w-100" src="{{ asset('storage/'.ltrim($schoolProfile->logo_path, '/')) }}" alt="">
                        @else
                            SC
                        @endif
                    </span>
                    <span class="d-flex flex-column">
                        <span class="brand-wordmark">{{ $publicSchoolName }}</span>
                        <span class="brand-subtitle">Unggul · Berkarakter · Berdampak</span>
                    </span>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Buka navigasi">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="publicNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif href="{{ route('home') }}">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" @if(request()->routeIs('profile')) aria-current="page" @endif href="{{ route('profile') }}">Profil</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}" @if(request()->routeIs('teachers.*')) aria-current="page" @endif href="{{ route('teachers.index') }}">Guru</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('information.*') ? 'active' : '' }}" @if(request()->routeIs('information.*')) aria-current="page" @endif href="{{ route('information.index') }}">Informasi</a></li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-primary w-100" href="{{ route('login') }}">Masuk Portal</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content">@yield('content')</main>

    <footer class="footer pt-5 pb-4">
        <div class="container">
            <div class="row g-4 pb-5">
                <div class="col-lg-5">
                    <a class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3" href="{{ route('home') }}">
                        <span class="school-mark overflow-hidden p-0" aria-hidden="true">
                            @if ($schoolProfile?->logo_path)
                                <img class="h-100 w-100" src="{{ asset('storage/'.ltrim($schoolProfile->logo_path, '/')) }}" alt="">
                            @else
                                SC
                            @endif
                        </span>
                        <span class="font-serif fs-4 text-white">{{ $publicSchoolName }}</span>
                    </a>
                    <p class="mb-0 col-lg-9">Ruang belajar yang menumbuhkan pengetahuan, karakter, dan keberanian untuk memberi dampak.</p>
                </div>
                <div class="col-6 col-lg-2 ms-lg-auto">
                    <h2 class="h6 text-white mb-3">Jelajahi</h2>
                    <ul class="list-unstyled small d-grid gap-2 mb-0">
                        <li><a href="{{ route('profile') }}">Profil sekolah</a></li>
                        <li><a href="{{ route('teachers.index') }}">Direktori guru</a></li>
                        <li><a href="{{ route('information.index') }}">Berita &amp; pengumuman</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h2 class="h6 text-white mb-3">Kontak</h2>
                    <address class="small mb-0">
                        {!! nl2br(e($publicSchoolAddress)) !!}<br>
                        @if ($publicSchoolPhone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $publicSchoolPhone) }}">{{ $publicSchoolPhone }}</a><br>@endif
                        @if ($publicSchoolEmail)<a href="mailto:{{ $publicSchoolEmail }}">{{ $publicSchoolEmail }}</a>@endif
                    </address>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 pt-3 border-top footer-rule small">
                <span>© <span data-current-year>{{ now()->year }}</span> {{ $publicSchoolName }}</span>
                <span>Waktu sistem: Asia/Makassar</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
