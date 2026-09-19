@extends('layouts.admin')

@php($editing = $member->exists)
@section('title', ($editing ? 'Edit' : 'Tambah').' Anggota Organisasi — Panel Admin')
@section('admin_page', 'organization')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span><a href="{{ route('admin.organization-members.index') }}">Struktur Organisasi</a></span><span>{{ $editing ? 'Edit' : 'Tambah' }}</span></div>
        <span class="eyebrow">Struktur sekolah</span><h1>{{ $editing ? 'Edit anggota.' : 'Tambah anggota.' }}</h1><p class="text-muted-warm mb-0">Hubungkan dengan guru atau isi nama manual untuk anggota non-guru.</p>
    </header>
    @include('admin.partials.validation-errors')
    <form action="{{ $editing ? route('admin.organization-members.update', $member) : route('admin.organization-members.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        @if($editing) @method('PUT') @endif
        <section class="admin-card">
            <div class="admin-card-header"><div><span class="eyebrow">Data anggota</span><h2 class="h3 mb-0">Identitas dan posisi</h2></div></div>
            <div class="admin-card-body">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="teacher_id">Hubungkan dengan guru</label><select class="form-select @error('teacher_id') is-invalid @enderror" id="teacher_id" name="teacher_id"><option value="">Tidak terhubung / nama manual</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $member->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>@endforeach</select>@error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label class="form-label" for="name">Nama manual</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $member->name) }}"><div class="form-text">Wajib bila tidak memilih guru.</div>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label class="form-label" for="position_name">Jabatan <span class="required-mark">*</span></label><input class="form-control @error('position_name') is-invalid @enderror" id="position_name" name="position_name" value="{{ old('position_name', $member->position_name) }}" required>@error('position_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label class="form-label" for="category">Kategori <span class="required-mark">*</span></label><select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->value }}" @selected(old('category', $member->category?->value) === $category->value)>{{ $category->label() }}</option>@endforeach</select>@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label class="form-label" for="display_order">Urutan tampil <span class="required-mark">*</span></label><input class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', $member->display_order ?? 0) }}" required>@error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $member->exists ? $member->is_active : true))><label class="form-check-label" for="is_active">Tampilkan di halaman publik</label></div></div>
                        </div>
                    </div>
                    <div class="col-lg-4"><div class="upload-zone {{ $member->photo_path ? 'has-preview' : '' }}" id="organizationPhotoPreview" @if($member->photo_path) style="background-image:url('{{ asset('storage/'.$member->photo_path) }}')" @endif><span>{{ $member->photo_path ? 'Foto saat ini' : 'Foto anggota opsional' }}</span></div><label class="form-label mt-3" for="photo">Foto</label><input class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="organizationPhotoPreview">@error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">JPEG, PNG, atau WebP. Maksimal 2 MB.</div></div>
                </div>
            </div>
            <div class="admin-card-footer d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('admin.organization-members.index') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Anggota</button></div>
        </section>
    </form>
@endsection
