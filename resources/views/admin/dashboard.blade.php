@extends('layouts.admin')

@section('title', 'Dashboard — Panel Admin')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span>Admin</span><span>Dashboard</span></div>
        <span class="eyebrow">Ringkasan sistem</span>
        <h1>Selamat datang, {{ auth()->user()->username }}.</h1>
        <p class="text-muted-warm mb-0">Kelola data sekolah, alur akademik, jadwal, dan publikasi dari satu panel.</p>
    </header>
    <section class="row row-cols-2 row-cols-xl-4 g-3 mb-4" aria-label="Statistik utama">
        <div class="col"><div class="admin-stat"><span class="admin-stat-label">Siswa aktif</span><strong class="admin-stat-value">{{ $stats['students'] }}</strong><span class="admin-stat-meta"><span class="status-dot"></span>Data siswa</span></div></div>
        <div class="col"><div class="admin-stat"><span class="admin-stat-label">Guru aktif</span><strong class="admin-stat-value">{{ $stats['teachers'] }}</strong><span class="admin-stat-meta"><span class="status-dot"></span>Tanpa akun login</span></div></div>
        <div class="col"><div class="admin-stat"><span class="admin-stat-label">Kelas aktif</span><strong class="admin-stat-value">{{ $stats['classes'] }}</strong><span class="admin-stat-meta"><span class="status-dot"></span>Periode berjalan</span></div></div>
        <div class="col"><div class="admin-stat"><span class="admin-stat-label">Konten terbit</span><strong class="admin-stat-value">{{ $stats['posts'] }}</strong><span class="admin-stat-meta"><span class="status-dot"></span>Berita & pengumuman</span></div></div>
    </section>
    <div class="row g-4">
        <div class="col-xl-8"><section class="admin-card h-100"><div class="admin-card-header"><div><span class="eyebrow mb-1">Periode aktif</span><h2 class="h3 mb-0">{{ $activePeriod ? $activePeriod->academic_year.' · Semester '.$activePeriod->semester->label() : 'Belum tersedia' }}</h2></div>@if($activePeriod)<span class="badge badge-news">Aktif</span>@endif</div><div class="admin-card-body"><p class="text-muted-warm">Statistik dibaca langsung dari database. Gunakan pintasan berikut untuk pekerjaan harian.</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.classes.index') }}">Kelas &amp; Penempatan</a><a class="btn btn-outline-primary" href="{{ route('admin.schedules.index') }}">Susun Jadwal</a><a class="btn btn-primary" href="{{ route('admin.posts.create') }}">Tulis Informasi</a></div></div></section></div>
        <div class="col-xl-4"><section class="admin-card h-100"><div class="admin-card-header"><div><span class="eyebrow mb-1">Status aplikasi</span><h2 class="h3 mb-0">Alur utama aktif</h2></div></div><div class="admin-card-body"><ul class="activity-list mb-0"><li><strong class="d-block small">Autentikasi session</strong><span class="small text-muted-warm">Admin dan siswa</span></li><li><strong class="d-block small">Administrasi akademik</strong><span class="small text-muted-warm">Enrollment, penugasan, dan deteksi bentrok jadwal</span></li><li><strong class="d-block small">Publikasi sekolah</strong><span class="small text-muted-warm">Draft, terbit, arsip, cover, dan gambar inline</span></li><li class="pb-0"><strong class="d-block small">Portal siswa</strong><span class="small text-muted-warm">Informasi kelas dan jadwal read-only</span></li></ul></div></section></div>
    </div>
@endsection
