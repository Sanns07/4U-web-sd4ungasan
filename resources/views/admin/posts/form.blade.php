@extends('layouts.admin')

@php($editing = $post->exists)
@section('title', ($editing ? 'Edit' : 'Tulis').' Konten — Panel Admin')
@section('admin_page', 'posts')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span><a href="{{ route('admin.posts.index') }}">Berita &amp; Pengumuman</a></span><span>{{ $editing ? 'Edit' : 'Tulis' }}</span></div>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3"><div><span class="eyebrow">Editor konten</span><h1>{{ $editing ? 'Edit berita/pengumuman.' : 'Tulis berita/pengumuman.' }}</h1><p class="text-muted-warm mb-0">HTML dibersihkan di server; gambar inline hanya berasal dari storage aplikasi.</p></div>@if($editing)<a class="btn btn-outline-primary" href="{{ route('admin.posts.preview', $post) }}">Preview Tersimpan</a>@endif</div>
    </header>
    @include('admin.partials.validation-errors')

    <form action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="post" enctype="multipart/form-data" data-post-editor novalidate>
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-xl-8">
                <section class="admin-card">
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-8"><label class="form-label" for="post_title">Judul <span class="required-mark">*</span></label><input class="form-control @error('title') is-invalid @enderror" id="post_title" name="title" value="{{ old('title', $post->title) }}" maxlength="255" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-4"><label class="form-label" for="post_type">Jenis <span class="required-mark">*</span></label><select class="form-select @error('type') is-invalid @enderror" id="post_type" name="type" required><option value="">Pilih jenis</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $post->type?->value) === $type->value)>{{ $type->label() }}</option>@endforeach</select>@error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label class="form-label" for="post_slug">Slug</label><div class="input-group"><span class="input-group-text">/informasi/</span><input class="form-control @error('slug') is-invalid @enderror" id="post_slug" name="slug" value="{{ old('slug', $post->slug) }}" maxlength="255" placeholder="otomatis-dari-judul"></div>@error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<div class="form-text">Kosongkan agar dibuat otomatis dari judul. Huruf kecil, angka, dan tanda hubung saja.</div></div>
                            <div class="col-12"><label class="form-label" for="post_excerpt">Ringkasan <span class="required-mark">*</span></label><textarea class="form-control @error('excerpt') is-invalid @enderror" id="post_excerpt" name="excerpt" rows="3" maxlength="220" required>{{ old('excerpt', $post->excerpt) }}</textarea>@error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text"><span data-excerpt-count>0</span>/220 karakter.</div></div>
                            <div class="col-12">
                                <label class="form-label d-block" id="post_body_label">Isi artikel <span class="required-mark">*</span></label>
                                <div class="editor-toolbar" role="toolbar" aria-label="Format artikel">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="bold" aria-label="Tebal"><strong>B</strong></button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="italic" aria-label="Miring"><em>I</em></button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="formatBlock" data-editor-value="p">Paragraf</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="formatBlock" data-editor-value="h2">H2</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="formatBlock" data-editor-value="h3">H3</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="insertUnorderedList">Daftar</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-editor-command="createLink">Tautan</button>
                                </div>
                                <div class="editor-surface @error('body_html') is-invalid @enderror" contenteditable="true" role="textbox" aria-labelledby="post_body_label" aria-multiline="true" data-editor-surface>{!! $editorHtml !!}</div>
                                <textarea class="visually-hidden" id="post_body" name="body_html" data-editor-output>{{ old('body_html', $post->body_html) }}</textarea>
                                @error('body_html')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div class="form-text">Format yang didukung: paragraf, H2/H3, tebal, miring, daftar, kutipan, dan tautan aman.</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="admin-card mt-4">
                    <div class="admin-card-header"><div><span class="eyebrow">Media artikel</span><h2 class="h4 mb-0">Gambar inline</h2><span class="small text-muted-warm">Alt text wajib · caption opsional · maksimal 10</span></div><div class="d-flex align-items-center gap-2"><span class="image-counter" data-image-counter>0/10 gambar inline</span><button class="btn btn-sm btn-outline-primary" type="button" data-add-inline-image>+ Sisipkan Gambar</button></div></div>
                    <div class="admin-card-body">
                        @error('inline_images')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
                        <div class="vstack gap-3" data-inline-image-list>
                            @foreach($post->images as $image)
                                <div class="inline-image-item" data-existing-image-item="{{ $image->id }}"><div class="row g-3 align-items-start"><div class="col-sm-3"><img class="editor-media-thumb" src="{{ asset('storage/'.ltrim($image->file_path, '/')) }}" alt=""></div><div class="col-sm-9"><div class="row g-2"><div class="col-md-6"><label class="form-label" for="existing_alt_{{ $image->id }}">Alt text <span class="required-mark">*</span></label><input class="form-control @error('existing_images.'.$image->id.'.alt_text') is-invalid @enderror" id="existing_alt_{{ $image->id }}" name="existing_images[{{ $image->id }}][alt_text]" value="{{ old('existing_images.'.$image->id.'.alt_text', $image->alt_text) }}" maxlength="255" required></div><div class="col-md-6"><label class="form-label" for="existing_caption_{{ $image->id }}">Caption</label><input class="form-control" id="existing_caption_{{ $image->id }}" name="existing_images[{{ $image->id }}][caption]" value="{{ old('existing_images.'.$image->id.'.caption', $image->caption) }}" maxlength="500"></div><div class="col-12"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-existing-image="{{ $image->id }}">Hapus dari Artikel</button></div></div></div></div></div>
                            @endforeach
                            @foreach($newImageSlots as $token => $metadata)
                                <div class="inline-image-item" data-new-image-item="{{ $token }}"><div class="row g-2"><div class="col-md-5"><label class="form-label" for="inline_file_{{ $token }}">File gambar</label><input class="form-control" id="inline_file_{{ $token }}" name="inline_images[{{ $token }}][file]" type="file" accept="image/jpeg,image/png,image/webp" required data-inline-file><div class="form-text">Pilih ulang file setelah validasi gagal.</div></div><div class="col-md-3"><label class="form-label" for="inline_alt_{{ $token }}">Alt text <span class="required-mark">*</span></label><input class="form-control" id="inline_alt_{{ $token }}" name="inline_images[{{ $token }}][alt_text]" value="{{ $metadata['alt_text'] ?? '' }}" maxlength="255" required></div><div class="col-md-3"><label class="form-label" for="inline_caption_{{ $token }}">Caption</label><input class="form-control" id="inline_caption_{{ $token }}" name="inline_images[{{ $token }}][caption]" value="{{ $metadata['caption'] ?? '' }}" maxlength="500"></div><div class="col-md-1 d-grid align-items-end"><button class="btn btn-outline-danger" type="button" data-remove-inline-image="{{ $token }}" aria-label="Hapus gambar inline">×</button></div></div></div>
                            @endforeach
                        </div>
                        <div class="empty-state empty-state--compact mt-3" data-inline-empty><span class="empty-state-mark">+</span><h3 class="h5">Belum ada gambar inline.</h3><p class="small text-muted-warm mb-0">Letakkan kursor pada isi artikel lalu pilih “Sisipkan Gambar”.</p></div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="h5 mb-0">Gambar cover</h2></div>
                    <div class="admin-card-body">
                        <div class="upload-zone {{ $post->cover_image_path ? 'has-preview' : '' }}" id="coverPreview" @if($post->cover_image_path) style="background-image:url('{{ asset('storage/'.ltrim($post->cover_image_path, '/')) }}')" @endif><div><strong class="d-block" data-preview-label>{{ $post->cover_image_path ? 'Cover tersimpan' : 'Pilih gambar cover' }}</strong><span class="small">JPG/PNG/WebP · maks. 2 MB</span></div></div>
                        <label class="form-label mt-3" for="post_cover">{{ $post->cover_image_path ? 'Ganti file cover' : 'File cover' }} @unless($post->cover_image_path)<span class="required-mark">*</span>@endunless</label><input class="form-control @error('cover_image') is-invalid @enderror" id="post_cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="coverPreview" @required(!$post->cover_image_path)>@error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <label class="form-label mt-3" for="post_cover_alt">Alt text cover <span class="required-mark">*</span></label><input class="form-control @error('cover_alt_text') is-invalid @enderror" id="post_cover_alt" name="cover_alt_text" value="{{ old('cover_alt_text', $post->cover_alt_text) }}" maxlength="255" required>@error('cover_alt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </section>
                <section class="admin-card">
                    <div class="admin-card-header"><h2 class="h5 mb-0">Publikasi</h2></div>
                    <div class="admin-card-body">
                        <label class="form-label" for="post_status">Status <span class="required-mark">*</span></label><select class="form-select @error('status') is-invalid @enderror" id="post_status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $post->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <label class="form-label mt-3" for="post_published_at">Tanggal &amp; waktu publikasi</label><input class="form-control @error('published_at') is-invalid @enderror" id="post_published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">@error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Status Terbit dengan waktu mendatang akan dipublikasikan sesuai jadwal. Tombol “Terbitkan” pada daftar memakai waktu saat ini.</div>
                    </div>
                    <div class="admin-card-footer d-flex flex-wrap justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('admin.posts.index') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Konten</button></div>
                </section>
            </div>
        </div>
    </form>

    <template data-inline-image-template>
        <div class="inline-image-item" data-new-image-item="__TOKEN__"><div class="row g-2"><div class="col-md-5"><label class="form-label" for="inline_file___TOKEN__">File gambar</label><input class="form-control" id="inline_file___TOKEN__" name="inline_images[__TOKEN__][file]" type="file" accept="image/jpeg,image/png,image/webp" required data-inline-file><div class="editor-file-preview" data-inline-preview></div></div><div class="col-md-3"><label class="form-label" for="inline_alt___TOKEN__">Alt text <span class="required-mark">*</span></label><input class="form-control" id="inline_alt___TOKEN__" name="inline_images[__TOKEN__][alt_text]" maxlength="255" required></div><div class="col-md-3"><label class="form-label" for="inline_caption___TOKEN__">Caption</label><input class="form-control" id="inline_caption___TOKEN__" name="inline_images[__TOKEN__][caption]" maxlength="500"></div><div class="col-md-1 d-grid align-items-end"><button class="btn btn-outline-danger" type="button" data-remove-inline-image="__TOKEN__" aria-label="Hapus gambar inline">×</button></div></div></div>
    </template>
@endsection
