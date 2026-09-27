@extends('layouts.public')

@section('title', 'Direktori Guru — '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan'))
@section('meta_description', 'Direktori guru aktif dan publik '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan').'.')

@section('content')
    <section class="page-hero"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb small mb-4"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">Direktori guru</li></ol></nav><span class="eyebrow eyebrow-light">Para pendidik</span><h1>Pengetahuan tumbuh<br>dari <em class="text-gold">perjumpaan.</em></h1><p class="lead mt-4 mb-0">Kenali para pendidik aktif yang mendampingi siswa belajar, bereksperimen, dan menemukan suaranya sendiri.</p></div></section>

    <section class="section-space">
        <div class="container">
            <form class="filter-panel rounded p-3 p-md-4 mb-5" action="{{ route('teachers.index') }}" method="get" role="search">
                <div class="row g-3 align-items-end">
                    <div class="col-md-7"><label class="form-label" for="teacherSearch">Cari guru</label><input class="form-control" id="teacherSearch" name="q" type="search" value="{{ $search }}" placeholder="Contoh: Ayu Lestari atau Matematika"></div>
                    <div class="col-md-3"><label class="form-label" for="teacherSubject">Bidang mengajar</label><select class="form-select" id="teacherSubject" name="bidang"><option value="">Semua bidang</option>@foreach ($fields as $option)<option value="{{ $option }}" @selected($field === $option)>{{ $option }}</option>@endforeach</select></div>
                    <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Cari Guru</button></div>
                </div>
            </form>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-4"><div><span class="eyebrow mb-2">{{ $visibleTeacherCount }} guru aktif</span><h2 class="h1 mb-0">Direktori pendidik</h2></div>@if ($teachers->total())<p class="small text-muted-warm mb-0">Menampilkan {{ $teachers->firstItem() }}–{{ $teachers->lastItem() }} dari {{ $teachers->total() }} guru</p>@endif</div>

            @if ($teachers->isNotEmpty())
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                    @foreach ($teachers as $teacher)
                        <div class="col"><article class="card teacher-card card-hover h-100">
                            @if ($teacher->photo_path)<img class="card-img-top" src="{{ asset('storage/'.ltrim($teacher->photo_path, '/')) }}" alt="Potret {{ $teacher->name }}">@else<div class="media-placeholder media-placeholder--light" role="img" aria-label="Potret {{ $teacher->name }}"><span>Potret {{ $teacher->name }}</span></div>@endif
                            <div class="card-body p-4"><h3 class="card-title h4 mb-1">{{ $teacher->name }}</h3><p class="small fw-semibold text-forest mb-2">{{ $teacher->public_title ?: 'Tenaga Pendidik' }}</p><p class="small text-muted-warm mb-0">Pendidik aktif yang mendampingi proses belajar dan perkembangan siswa.</p></div>
                        </article></div>
                    @endforeach
                </div>
                @include('public.partials.pagination', ['paginator' => $teachers, 'label' => 'Halaman direktori guru'])
            @elseif ($hasFilters)
                <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">?</span><h3 class="h3">Guru tidak ditemukan</h3><p class="text-muted-warm">Tidak ada guru yang cocok dengan pencarian atau bidang yang dipilih.</p><a class="btn btn-outline-primary" href="{{ route('teachers.index') }}">Hapus Pencarian</a></div>
            @else
                <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><h3 class="h3">Direktori sedang disiapkan</h3><p class="text-muted-warm mb-0">Profil guru aktif dan publik akan tampil setelah diperbarui oleh sekolah.</p></div>
            @endif
        </div>
    </section>
@endsection
