@extends('layouts.public')

@section('title', 'Beranda — '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan'))
@section('meta_description', 'Beranda '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan').' — sekolah dasar unggul, berkarakter, dan berbudaya.')

@section('content')
    <section class="hero-section text-white py-5">
        <div class="container py-lg-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="eyebrow eyebrow-light">{{ $activePeriod ? 'Tahun akademik '.$activePeriod->academic_year : 'SD No. 4 Ungasan' }}</span>
                    <h1 class="display-3 fw-normal mb-3">Membentuk Karakter Unggul &amp; Pembelajaran Bermakna.</h1>
                    <p class="hero-lead mb-4">{{ $schoolProfile?->vision ?: 'SD No. 4 Ungasan adalah ruang belajar yang mempertemukan rasa ingin tahu, karakter 7 Kebiasaan Anak Hebat, dan budaya Bali.' }}</p>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a class="btn btn-light" href="{{ route('profile') }}">Mengenal Sekolah <span class="ms-2" aria-hidden="true">→</span></a>
                        <a class="btn btn-outline-light" href="{{ route('information.index') }}">Informasi Terbaru</a>
                    </div>
                    <div class="hero-meta d-flex flex-wrap gap-3 gap-md-5 mt-5 pt-3">
                        <span>Denpasar, Bali</span><span>Terakreditasi A</span><span>Sejak 1987</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space bg-cream-soft">
        <div class="container">
            <div class="row align-items-end g-4 mb-5">
                <div class="col-lg-8">
                    <span class="eyebrow">01 · Tentang kami</span>
                    <h2 class="display-4 mb-0">Sekolah yang memberi ruang<br><em class="text-forest">untuk setiap kemungkinan.</em></h2>
                </div>
                <div class="col-lg-4">
                    <p class="text-muted-warm mb-0">Kami menyelenggarakan pendidikan yang utuh: kuat dalam pengetahuan, hangat dalam relasi, dan relevan dengan perubahan zaman.</p>
                </div>
            </div>
            <div class="row g-0 border-top border-bottom border-soft">
                @foreach ($statistics as $statistic)
                    <div class="col-6 col-lg-3 stat-card"><strong>{{ number_format($statistic['value'], 0, ',', '.') }}</strong><span>{{ $statistic['label'] }}</span></div>
                @endforeach
            </div>
            <div class="row g-4 mt-4">
                <div class="col-md-4"><div class="h-100 pt-3 border-top border-soft"><span class="feature-number">01</span><h3 class="h4 mt-4">Bernalar jernih</h3><p class="text-muted-warm mb-0">Pembelajaran mendorong siswa bertanya, menelaah, dan menyusun keputusan yang dapat dipertanggungjawabkan.</p></div></div>
                <div class="col-md-4"><div class="h-100 pt-3 border-top border-soft"><span class="feature-number">02</span><h3 class="h4 mt-4">Berkarakter kuat</h3><p class="text-muted-warm mb-0">Disiplin, empati, dan integritas tumbuh melalui kebiasaan sehari-hari dan keteladanan komunitas sekolah.</p></div></div>
                <div class="col-md-4"><div class="h-100 pt-3 border-top border-soft"><span class="feature-number">03</span><h3 class="h4 mt-4">Memberi dampak</h3><p class="text-muted-warm mb-0">Pengetahuan diarahkan menjadi tindakan nyata untuk lingkungan, budaya, dan masyarakat sekitar.</p></div></div>
            </div>
        </div>
    </section>

    <section class="section-space">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-5">
                <div class="section-heading mb-0"><span class="eyebrow">02 · Informasi</span><h2 class="mb-0">Kabar terbaru sekolah.</h2></div>
                <a class="btn btn-outline-primary align-self-start" href="{{ route('information.index') }}">Lihat Semua Informasi</a>
            </div>

            @if ($latestPosts->isEmpty())
                <div class="empty-state">
                    <span class="empty-state-mark" aria-hidden="true">—</span>
                    <h3 class="h3">Belum ada informasi terbit</h3>
                    <p class="text-muted-warm mb-0">Berita atau pengumuman terbaru akan muncul setelah diterbitkan oleh sekolah.</p>
                </div>
            @else
                @php($featuredPost = $latestPosts->first())
                <div class="row g-4">
                    <div class="{{ $latestPosts->count() > 1 ? 'col-lg-7' : 'col-12' }}">
                        <article class="card news-card news-card-featured card-hover h-100">
                            @if ($featuredPost->cover_image_path)
                                <img class="card-img-top" src="{{ asset('storage/'.ltrim($featuredPost->cover_image_path, '/')) }}" alt="{{ $featuredPost->cover_alt_text }}">
                            @else
                                <div class="media-placeholder media-placeholder--light" role="img" aria-label="Sampul {{ $featuredPost->title }}"><span>{{ $featuredPost->cover_alt_text ?: $featuredPost->title }}</span></div>
                            @endif
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-3"><span class="badge {{ $featuredPost->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($featuredPost->type->value) }}</span><span class="small text-muted-warm">{{ $featuredPost->published_at->locale('id')->translatedFormat('j F Y') }}</span></div>
                                <h3 class="card-title h2">{{ $featuredPost->title }}</h3>
                                @if ($featuredPost->excerpt)<p class="card-text text-muted-warm">{{ $featuredPost->excerpt }}</p>@endif
                                <a class="stretched-link fw-semibold text-forest" href="{{ route('information.show', $featuredPost) }}">Baca selengkapnya <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    </div>
                    @if ($latestPosts->count() > 1)
                        <div class="col-lg-5 d-grid gap-4">
                            @foreach ($latestPosts->skip(1) as $post)
                                <article class="card news-card card-hover">
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3"><span class="badge {{ $post->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($post->type->value) }}</span><span class="small text-muted-warm">{{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</span></div>
                                        <h3 class="card-title">{{ $post->title }}</h3>
                                        @if ($post->excerpt)<p class="card-text text-muted-warm">{{ $post->excerpt }}</p>@endif
                                        <a class="stretched-link fw-semibold text-forest" href="{{ route('information.show', $post) }}">{{ $post->type->value === 'berita' ? 'Baca selengkapnya' : 'Baca informasi' }} <span aria-hidden="true">→</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="section-space bg-forest text-white">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="eyebrow eyebrow-light">03 · Para pendidik</span>
                    <h2 class="display-4">Mereka yang menjaga rasa ingin tahu tetap hidup.</h2>
                    <p class="text-white-50">Setiap guru membawa pengalaman, perhatian, dan cara pandang yang memperkaya perjalanan belajar siswa.</p>
                    <a class="btn btn-light mt-2" href="{{ route('teachers.index') }}">Temui Para Guru</a>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        @forelse ($featuredTeachers as $teacher)
                            <div class="col-6 {{ $loop->last ? 'pt-5' : '' }}">
                                @if ($teacher->photo_path)
                                    <img class="w-100 media-placeholder--portrait" src="{{ asset('storage/'.ltrim($teacher->photo_path, '/')) }}" alt="Potret {{ $teacher->name }}">
                                @else
                                    <div class="media-placeholder media-placeholder--light media-placeholder--portrait" role="img" aria-label="Potret {{ $teacher->name }}"><span>Potret {{ $teacher->name }}</span></div>
                                @endif
                            </div>
                        @empty
                            <div class="col-12"><div class="empty-state border-light text-white bg-transparent"><span class="empty-state-mark" aria-hidden="true">—</span><h3 class="h3">Direktori sedang disiapkan</h3><p class="text-white-50 mb-0">Profil pendidik akan tampil setelah diperbarui oleh sekolah.</p></div></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space-sm bg-cream-soft">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8"><span class="eyebrow">Portal akademik</span><h2 class="display-6 mb-2">Jadwal akademikmu ada di satu tempat.</h2><p class="text-muted-warm mb-0">Portal hanya tersedia untuk akun siswa dan admin yang dibuat sekolah.</p></div>
                <div class="col-lg-4 text-lg-end"><a class="btn btn-primary btn-lg" href="{{ route('login') }}">Masuk ke Portal <span class="ms-2" aria-hidden="true">→</span></a></div>
            </div>
        </div>
    </section>
@endsection
