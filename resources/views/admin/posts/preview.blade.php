@extends('layouts.admin')

@section('title', 'Preview '.$post->title.' — Panel Admin')
@section('admin_page', 'posts')

@section('content')
    <header class="admin-page-head"><div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span><a href="{{ route('admin.posts.index') }}">Berita &amp; Pengumuman</a></span><span>Preview</span></div><div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3"><div><span class="eyebrow">Pratinjau Admin · tidak selalu publik</span><h1>{{ $post->title }}</h1><p class="text-muted-warm mb-0">Tampilan memakai HTML yang sudah disanitasi serta media yang dimiliki artikel ini.</p></div><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.posts.edit', $post) }}">Edit Konten</a>@if($post->status === \App\Enums\PostStatus::Published && ! $post->published_at?->isFuture())<a class="btn btn-primary" href="{{ route('information.show', ['post' => $post->slug]) }}" target="_blank" rel="noopener">Lihat Halaman Publik</a>@endif</div></div></header>

    <div class="alert alert-warning" role="status"><strong>Status:</strong> {{ $post->status->label() }}@if($post->status === \App\Enums\PostStatus::Published && $post->published_at?->isFuture()) · Terjadwal {{ $post->published_at->locale('id')->translatedFormat('d M Y, H.i') }}@endif. Preview Admin dapat menampilkan draft dan arsip yang tidak tersedia untuk publik.</div>
    <article class="admin-card overflow-hidden">
        <header class="admin-card-body bg-cream-soft"><div class="d-flex flex-wrap gap-2 align-items-center mb-3"><span class="badge {{ $post->type === \App\Enums\PostType::Announcement ? 'text-bg-warning' : 'badge-news' }}">{{ $post->type->label() }}</span><span class="small text-muted-warm">{{ $post->published_at?->locale('id')->translatedFormat('d F Y · H.i') ?: 'Tanggal publikasi belum ditentukan' }}</span></div><h2 class="display-6 mb-3">{{ $post->title }}</h2><p class="fs-5 font-serif fst-italic text-muted-warm mb-0">{{ $post->excerpt }}</p></header>
        @if($post->cover_image_path)<img class="article-cover" src="{{ asset('storage/'.ltrim($post->cover_image_path, '/')) }}" alt="{{ $post->cover_alt_text }}">@else<div class="media-placeholder media-placeholder--light" role="img" aria-label="Cover belum tersedia"><span>Cover belum tersedia</span></div>@endif
        <div class="admin-card-body article-shell article-body">{!! $renderedBodyHtml !!}</div>
    </article>
@endsection
