@extends('layouts.public')

@section('title', 'Berita & Pengumuman — '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan'))
@section('meta_description', 'Daftar berita dan pengumuman terbit '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan').'.')

@section('content')
    <section class="hero-home d-flex align-items-center py-5">
        <img src="{{ asset('image/hero-informasi.jpg') }}" alt="Informasi dan Berita SD No. 4 Ungasan" style="object-fit:cover;">
        <div class="container py-lg-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <nav aria-label="Breadcrumb">
                        <ol class="breadcrumb small mb-4">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Berita & pengumuman</li>
                        </ol>
                    </nav>
                    <span class="eyebrow eyebrow-light">Ruang informasi</span>
                    <h1 class="display-3 fw-normal mb-3">Kabar, agenda,<br>dan hal <em class="text-gold">penting.</em></h1>
                    <p class="hero-lead mb-0">Berita sekolah, prestasi siswa, agenda kegiatan, serta pengumuman resmi yang sudah diterbitkan.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space">
        <div class="container">
            <form class="filter-panel rounded p-3 p-md-4 mb-5" action="{{ route('information.index') }}" method="get" role="search">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6"><label class="form-label" for="postSearch">Cari informasi</label><input class="form-control" id="postSearch" name="q" type="search" value="{{ $search }}" placeholder="Cari judul atau ringkasan"></div>
                    <div class="col-6 col-lg-2"><label class="form-label" for="postType">Jenis</label><select class="form-select" id="postType" name="tipe"><option value="">Semua</option><option value="berita" @selected($selectedType === 'berita')>Berita</option><option value="pengumuman" @selected($selectedType === 'pengumuman')>Pengumuman</option></select></div>
                    <div class="col-6 col-lg-2"><label class="form-label" for="postYear">Tahun</label><select class="form-select" id="postYear" name="tahun"><option value="">Semua</option>@foreach ($years as $option)<option value="{{ $option }}" @selected($selectedYear === $option)>{{ $option }}</option>@endforeach</select></div>
                    <div class="col-lg-2 d-grid"><button class="btn btn-primary" type="submit">Terapkan</button></div>
                </div>
            </form>

            <ul class="nav nav-pills gap-2 mb-5" aria-label="Filter cepat jenis informasi">
                <li class="nav-item"><a class="nav-link {{ $selectedType === null ? 'active' : 'text-forest' }}" @if($selectedType === null) aria-current="page" @endif href="{{ route('information.index', request()->except('page', 'tipe')) }}">Semua <span class="badge text-bg-light ms-1">{{ $publishedCount }}</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $selectedType === 'berita' ? 'active' : 'text-forest' }}" @if($selectedType === 'berita') aria-current="page" @endif href="{{ route('information.index', array_merge(request()->except('page', 'tipe'), ['tipe' => 'berita'])) }}">Berita <span class="badge text-bg-light ms-1">{{ $typeCounts['berita'] }}</span></a></li>
                <li class="nav-item"><a class="nav-link {{ $selectedType === 'pengumuman' ? 'active' : 'text-forest' }}" @if($selectedType === 'pengumuman') aria-current="page" @endif href="{{ route('information.index', array_merge(request()->except('page', 'tipe'), ['tipe' => 'pengumuman'])) }}">Pengumuman <span class="badge text-bg-light ms-1">{{ $typeCounts['pengumuman'] }}</span></a></li>
            </ul>

            @if ($featuredPost)
                <article class="card news-card news-card-featured card-hover mb-5"><div class="row g-0">
                    <div class="col-lg-7">@if ($featuredPost->cover_image_path)<img class="w-100 h-100" src="{{ asset('storage/'.ltrim($featuredPost->cover_image_path, '/')) }}" alt="{{ $featuredPost->cover_alt_text }}">@else<div class="media-placeholder media-placeholder--light" role="img" aria-label="Sampul {{ $featuredPost->title }}"><span>{{ $featuredPost->cover_alt_text ?: $featuredPost->title }}</span></div>@endif</div>
                    <div class="col-lg-5"><div class="card-body p-4 p-lg-5 h-100 d-flex flex-column justify-content-center"><div class="d-flex flex-wrap gap-2 align-items-center mb-3"><span class="badge {{ $featuredPost->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($featuredPost->type->value) }}</span><span class="small text-muted-warm">{{ $featuredPost->published_at->locale('id')->translatedFormat('j F Y') }}</span></div><h2 class="display-6">{{ $featuredPost->title }}</h2>@if($featuredPost->excerpt)<p class="text-muted-warm">{{ $featuredPost->excerpt }}</p>@endif<a class="stretched-link fw-semibold text-forest" href="{{ route('information.show', ['post' => $featuredPost->slug]) }}">Baca selengkapnya <span aria-hidden="true">→</span></a></div></div>
                </div></article>

                @if ($remainingPosts->isNotEmpty())
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($remainingPosts as $post)
                            <div class="col"><article class="card news-card card-hover h-100">
                                @if ($post->cover_image_path)<img class="card-img-top" src="{{ asset('storage/'.ltrim($post->cover_image_path, '/')) }}" alt="{{ $post->cover_alt_text }}">@else<div class="media-placeholder media-placeholder--light" role="img" aria-label="Sampul {{ $post->title }}"><span>{{ $post->cover_alt_text ?: $post->title }}</span></div>@endif
                                <div class="card-body p-4"><div class="d-flex flex-wrap gap-2 align-items-center mb-3"><span class="badge {{ $post->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($post->type->value) }}</span><span class="small text-muted-warm">{{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</span></div><h2 class="card-title h3">{{ $post->title }}</h2>@if($post->excerpt)<p class="text-muted-warm">{{ $post->excerpt }}</p>@endif<a class="stretched-link fw-semibold text-forest" href="{{ route('information.show', ['post' => $post->slug]) }}">{{ $post->type->value === 'berita' ? 'Baca selengkapnya' : 'Baca informasi' }} →</a></div>
                            </article></div>
                        @endforeach
                    </div>
                @endif
                @include('public.partials.pagination', ['paginator' => $posts, 'label' => 'Halaman berita dan pengumuman'])
            @elseif ($hasFilters)
                <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">?</span><h2 class="h3">Informasi tidak ditemukan</h2><p class="text-muted-warm">Tidak ada berita atau pengumuman yang sesuai dengan filter yang dipilih.</p><a class="btn btn-outline-primary" href="{{ route('information.index') }}">Reset Filter</a></div>
            @else
                <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><h2 class="h3">Belum ada informasi terbit</h2><p class="text-muted-warm mb-0">Berita atau pengumuman terbaru akan muncul di halaman ini setelah diterbitkan.</p></div>
            @endif
        </div>
    </section>
@endsection
