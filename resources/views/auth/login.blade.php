@extends('layouts.auth')

@section('content')
    <a class="btn btn-primary skip-link" href="#login-form">Lewati ke formulir masuk</a>
    <main class="login-page">
        <div class="container-fluid">
            <div class="row min-vh-100">
                <aside class="col-lg-6 d-none d-lg-flex login-visual p-5" aria-label="Pesan sekolah">
                    <img src="{{ asset('image/hero-beranda.jpg') }}" alt="SD No. 4 Ungasan">
                    <div class="login-visual-content d-flex flex-column justify-content-between w-100">
                        <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-white" href="{{ route('home') }}">
                            <span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true"><img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;"></span>
                            <span class="font-serif fs-4">SD No. 4 Ungasan</span>
                        </a>
                        <div class="col-xl-9"><span class="eyebrow eyebrow-light">Portal akademik</span><blockquote class="display-5 mb-3">“Belajar adalah perjalanan yang tumbuh dari kebiasaan kecil setiap hari.”</blockquote><p class="text-white-50 mb-0">Siswa melihat jadwal kelas; admin mengelola data akademik.</p></div>
                    </div>
                </aside>
                <section class="col-lg-6 d-flex align-items-center p-3 p-sm-5" aria-labelledby="login-title">
                    <div class="login-panel mx-auto py-4">
                        <a class="d-inline-flex d-lg-none align-items-center gap-2 text-decoration-none mb-5" href="{{ route('home') }}">
                            <span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true"><img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;"></span>
                            <span class="brand-wordmark">SD No. 4 Ungasan</span>
                        </a>
                        <a class="small text-muted-warm text-decoration-none d-inline-block mb-5" href="{{ route('home') }}"><span aria-hidden="true">←</span> Kembali ke beranda</a>
                        <span class="eyebrow">Akun sekolah</span>
                        <h1 class="display-5 mb-3" id="login-title">Selamat datang kembali.</h1>
                        <p class="text-muted-warm mb-4">Masukkan username dan password yang diberikan oleh admin sekolah.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger d-flex gap-3" role="alert" id="loginError">
                                <span class="fw-bold" aria-hidden="true">!</span>
                                <div><strong class="d-block">Tidak dapat masuk</strong><span class="small">{{ $errors->first('username', 'Periksa kembali data yang dimasukkan.') }}</span></div>
                            </div>
                        @endif

                        <form id="login-form" action="{{ route('login.store') }}" method="post" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="username">Username <span class="text-danger" aria-hidden="true">*</span></label>
                                <input @class(['form-control', 'is-invalid' => $errors->any()]) id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus aria-describedby="usernameHelp">
                                <div class="invalid-feedback">Periksa kembali username dan password.</div>
                                <div class="form-text" id="usernameHelp">Siswa dapat menggunakan NISN sebagai username awal.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password <span class="text-danger" aria-hidden="true">*</span></label>
                                <div class="input-group">
                                    <input @class(['form-control', 'is-invalid' => $errors->any()]) id="password" name="password" type="password" autocomplete="current-password" required>
                                    <button class="btn btn-outline-primary" type="button" data-password-toggle="password" aria-pressed="false">Tampilkan</button>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                                <div class="form-check"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))><label class="form-check-label small" for="remember">Ingat saya di perangkat ini</label></div>
                                <span class="small text-muted-warm">Lupa akses? Hubungi admin sekolah.</span>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Masuk ke Portal <span class="ms-2" aria-hidden="true">→</span></button>
                        </form>
                        <div class="mt-4 p-3 rounded border border-soft bg-white bg-opacity-50"><p class="small fw-semibold mb-1">Akses terbatas</p><p class="small text-muted-warm mb-0">Tidak tersedia registrasi publik. Akun siswa dan admin dibuat serta dikelola oleh sekolah.</p></div>
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
