@extends('layouts.admin')

@section('title', 'Berita & Pengumuman — Panel Admin')
@section('admin_page', 'posts')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Konten Website</span><span>Berita &amp; Pengumuman</span></div>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div><span class="eyebrow">Publikasi sekolah</span><h1>Berita &amp; pengumuman.</h1><p class="text-muted-warm mb-0">Kelola draft, publikasi, arsip, satu cover, dan maksimal 10 gambar inline.</p></div>
            <a class="btn btn-primary" href="{{ route('admin.posts.create') }}">+ Tulis Konten</a>
        </div>
    </header>

    <section class="admin-card">
        <div class="admin-card-body border-bottom">
            <form action="{{ route('admin.posts.index') }}" method="get" class="row g-2 align-items-end">
                <div class="col-lg-4"><label class="form-label" for="post_search">Cari konten</label><input class="form-control" id="post_search" name="q" type="search" value="{{ request('q') }}" placeholder="Judul, ringkasan, atau penulis"></div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="post_type_filter">Jenis</label><select class="form-select" id="post_type_filter" name="type"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="post_status_filter">Status</label><select class="form-select" id="post_status_filter" name="status"><option value="">Semua status</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="post_month">Bulan terbit</label><select class="form-select" id="post_month" name="month"><option value="">Semua bulan</option>@foreach($months as $value => $label)<option value="{{ $value }}" @selected(request('month') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Terapkan</button></div>
            </form>
        </div>
        <div class="admin-card-header">
            <div><span class="eyebrow">Hasil data</span><h2 class="h3 mb-0">{{ $posts->total() }} konten</h2></div>
            @if(request()->hasAny(['q', 'type', 'status', 'month']))<a class="small" href="{{ route('admin.posts.index') }}">Reset filter</a>@endif
        </div>

        @if($posts->isEmpty())
            <div class="empty-state m-3"><span class="empty-state-mark">—</span><span class="eyebrow">Tidak ada hasil</span><h2>{{ request()->hasAny(['q', 'type', 'status', 'month']) ? 'Konten tidak ditemukan.' : 'Konten belum tersedia.' }}</h2><p class="text-muted-warm">{{ request()->hasAny(['q', 'type', 'status', 'month']) ? 'Ubah kata kunci atau filter yang dipilih.' : 'Tulis berita atau pengumuman pertama untuk halaman publik sekolah.' }}</p>@unless(request()->hasAny(['q', 'type', 'status', 'month']))<a class="btn btn-primary" href="{{ route('admin.posts.create') }}">+ Tulis Konten</a>@endunless</div>
        @else
            <div class="table-responsive table-responsive-stack">
                <table class="table table-admin align-middle mb-0">
                    <thead><tr><th>Konten</th><th>Jenis</th><th>Tanggal publikasi</th><th>Media</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($posts as $post)
                            @php
                                $isScheduled = $post->status === \App\Enums\PostStatus::Published && $post->published_at?->isFuture();
                                $statusClass = match ($post->status) {
                                    \App\Enums\PostStatus::Published => $isScheduled ? 'text-bg-warning' : 'badge-soft-success',
                                    \App\Enums\PostStatus::Archived => 'text-bg-light',
                                    default => 'text-bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td data-label="Konten"><strong>{{ $post->title }}</strong><span class="d-block small text-muted-warm">/informasi/{{ $post->slug }} · {{ $post->author?->username ?: 'Penulis tidak tersedia' }}</span></td>
                                <td data-label="Jenis"><span class="badge {{ $post->type === \App\Enums\PostType::Announcement ? 'text-bg-warning' : 'text-bg-light' }}">{{ $post->type->label() }}</span></td>
                                <td data-label="Tanggal publikasi">{{ $post->published_at?->locale('id')->translatedFormat('d M Y · H.i') ?: 'Belum ditentukan' }}</td>
                                <td data-label="Media">{{ $post->cover_image_path ? '1 cover' : 'Tanpa cover' }} · {{ $post->images_count }} inline</td>
                                <td data-label="Status"><span class="badge {{ $statusClass }}">{{ $isScheduled ? 'Terjadwal' : $post->status->label() }}</span></td>
                                <td data-label="Aksi" class="text-end"><div class="row-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.posts.preview', $post) }}">Preview</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                                    @if($post->status === \App\Enums\PostStatus::Published)
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#archivePost{{ $post->id }}">Arsipkan</button>
                                    @else
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#publishPost{{ $post->id }}">Terbitkan</button>
                                    @endif
                                    @if($post->status === \App\Enums\PostStatus::Archived)<button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deletePost{{ $post->id }}">Hapus</button>@endif
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($posts->hasPages())<div class="admin-card-footer">{{ $posts->links() }}</div>@endif
        @endif
    </section>

    @foreach($posts as $post)
        @if($post->status === \App\Enums\PostStatus::Published)
            <div class="modal fade" id="archivePost{{ $post->id }}" tabindex="-1" aria-labelledby="archivePostLabel{{ $post->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4"><span class="eyebrow text-danger">Konfirmasi arsip</span><h2 class="h3" id="archivePostLabel{{ $post->id }}">Arsipkan konten?</h2><p><strong>{{ $post->title }}</strong> tidak lagi terlihat di publik. Cover dan gambar inline tetap disimpan.</p><div class="d-flex justify-content-end gap-2"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><form action="{{ route('admin.posts.archive', $post) }}" method="post">@csrf @method('PATCH')<button class="btn btn-danger" type="submit">Arsipkan</button></form></div></div></div></div></div>
        @else
            <div class="modal fade" id="publishPost{{ $post->id }}" tabindex="-1" aria-labelledby="publishPostLabel{{ $post->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4"><span class="eyebrow">Konfirmasi terbit</span><h2 class="h3" id="publishPostLabel{{ $post->id }}">Publikasikan sekarang?</h2><p><strong>{{ $post->title }}</strong> akan langsung tersedia pada halaman informasi publik.</p><div class="d-flex justify-content-end gap-2"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Periksa Lagi</button><form action="{{ route('admin.posts.publish', $post) }}" method="post">@csrf @method('PATCH')<button class="btn btn-primary" type="submit">Ya, Publikasikan</button></form></div></div></div></div></div>
        @endif
        @if($post->status === \App\Enums\PostStatus::Archived)
            <div class="modal fade" id="deletePost{{ $post->id }}" tabindex="-1" aria-labelledby="deletePostLabel{{ $post->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4"><span class="eyebrow text-danger">Hapus permanen</span><h2 class="h3" id="deletePostLabel{{ $post->id }}">Hapus konten arsip?</h2><p>Cover dan seluruh gambar inline <strong>{{ $post->title }}</strong> ikut dihapus. Tindakan ini tidak dapat dibatalkan.</p><div class="d-flex justify-content-end gap-2"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><form action="{{ route('admin.posts.destroy', $post) }}" method="post">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Hapus Permanen</button></form></div></div></div></div></div>
        @endif
    @endforeach
@endsection
