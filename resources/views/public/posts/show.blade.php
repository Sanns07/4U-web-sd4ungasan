@extends('layouts.public')

@section('title', $post->title.' — '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan'))
@section('meta_description', $post->excerpt ?: 'Informasi '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan').'.')

@section('content')
    <article>
        <header class="section-space-sm bg-cream-soft">
            <div class="container article-shell pt-lg-4">
                <nav aria-label="Breadcrumb"><ol class="breadcrumb small mb-4" style="--bs-breadcrumb-divider-color: rgba(44,38,32,.35)"><li class="breadcrumb-item"><a class="text-muted-warm" href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a class="text-muted-warm" href="{{ route('information.index', ['tipe' => $post->type->value]) }}">{{ ucfirst($post->type->value) }}</a></li><li class="breadcrumb-item active text-forest" aria-current="page">{{ $post->title }}</li></ol></nav>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-4"><span class="badge {{ $post->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($post->type->value) }}</span><span class="small text-muted-warm">{{ $post->published_at->locale('id')->translatedFormat('j F Y') }} · {{ $readingMinutes }} menit baca</span></div>
                <h1 class="article-title mb-4">{{ $post->title }}</h1>
                @if ($post->excerpt)<p class="fs-4 font-serif fst-italic text-muted-warm mb-4">{{ $post->excerpt }}</p>@endif
                <div class="d-flex align-items-center gap-3 pt-3 border-top border-soft"><span class="school-mark overflow-hidden p-1 bg-white" aria-hidden="true"><img class="h-100 w-100" src="{{ asset('image/logo_sekolah.png') }}" alt="" style="object-fit: contain;"></span><div><span class="d-block small fw-semibold">Tim Redaksi Sekolah</span><span class="d-block small text-muted-warm">{{ $schoolProfile?->school_name ?: 'SD No. 4 Ungasan' }}</span></div></div>
            </div>
        </header>

        @if ($post->cover_image_path)
            <figure class="container-xl px-0 px-sm-3 mb-0"><img class="article-cover" src="{{ asset('storage/'.ltrim($post->cover_image_path, '/')) }}" alt="{{ $post->cover_alt_text }}"></figure>
        @else
            <div class="container-xl px-0 px-sm-3"><div class="media-placeholder media-placeholder--light" role="img" aria-label="Sampul {{ $post->title }}"><span>{{ $post->cover_alt_text ?: $post->title }}</span></div></div>
        @endif

        <div class="container article-shell section-space-sm article-body">
            {!! $post->renderedBodyHtml() !!}
            <div class="share-box d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 py-4 mt-5"><div><span class="small fw-semibold d-block">Bagikan artikel</span><span class="small text-muted-warm">Gunakan tautan resmi halaman ini.</span></div><div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-primary" type="button" data-copy-link>Salin Tautan</button><a class="btn btn-primary" href="{{ route('information.index') }}">Kembali ke Informasi</a></div></div>
        </div>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <section class="section-space-sm bg-cream-soft" aria-labelledby="related-title"><div class="container"><div class="d-flex justify-content-between align-items-end gap-3 mb-4"><div><span class="eyebrow">Baca berikutnya</span><h2 class="h1 mb-0" id="related-title">Informasi terkait</h2></div><a class="d-none d-sm-inline fw-semibold text-forest" href="{{ route('information.index') }}">Semua informasi →</a></div><div class="row g-4">
            @foreach ($relatedPosts as $relatedPost)<div class="col-md-6"><article class="card news-card card-hover h-100"><div class="card-body p-4"><div class="d-flex gap-2 align-items-center mb-3"><span class="badge {{ $relatedPost->type->value === 'berita' ? 'badge-news' : 'badge-announcement' }}">{{ ucfirst($relatedPost->type->value) }}</span><span class="small text-muted-warm">{{ $relatedPost->published_at->locale('id')->translatedFormat('j F Y') }}</span></div><h3 class="h3">{{ $relatedPost->title }}</h3><a class="stretched-link fw-semibold text-forest" href="{{ route('information.show', ['post' => $relatedPost->slug]) }}">Baca selengkapnya →</a></div></article></div>@endforeach
        </div></div></section>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelector('[data-copy-link]')?.addEventListener('click', async (event) => {
            if (!navigator.clipboard?.writeText) {
                return;
            }

            await navigator.clipboard.writeText(window.location.href);
            event.currentTarget.textContent = 'Tautan Tersalin';
        });
    </script>
@endpush
