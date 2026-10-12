@extends('layouts.public')

@section('title', 'Akses Ditolak — SIM Akademik')

@section('content')
    <section class="section-space"><div class="container"><div class="empty-state"><span class="empty-state-mark" aria-hidden="true">403</span><span class="eyebrow">Akses ditolak</span><h1>Halaman ini bukan untuk akunmu.</h1><p class="text-muted-warm">Masuk dengan akun yang memiliki izin sesuai, atau kembali ke halaman portalmu.</p><a class="btn btn-primary mt-3" href="{{ auth()->user()?->hasRole('admin') ? route('admin.dashboard') : (auth()->check() ? route('student.home') : route('login')) }}">Kembali</a></div></div></section>
@endsection
