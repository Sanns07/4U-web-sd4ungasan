@extends('layouts.admin')

@section('title', 'Profil Sekolah — Panel Admin')
@section('admin_page', 'school')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Profil Sekolah</span></div>
        <span class="eyebrow">Identitas sekolah</span>
        <h1>Profil sekolah.</h1>
        <p class="text-muted-warm mb-0">Kelola identitas, kontak, visi, misi, dan logo yang menjadi sumber halaman publik.</p>
    </header>

    @unless($profile)
        <div class="alert alert-warning" role="status"><strong>Profil belum tersedia.</strong> Isi formulir berikut untuk membuat profil sekolah pertama.</div>
    @endunless

    @include('admin.partials.validation-errors')

    <form action="{{ route('admin.school-profile.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-xl-8">
                <section class="admin-card">
                    <div class="admin-card-header"><div><span class="eyebrow">Konten utama</span><h2 class="h3 mb-0">Data sekolah</h2></div></div>
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-8"><label class="form-label" for="school_name">Nama sekolah <span class="required-mark">*</span></label><input class="form-control @error('school_name') is-invalid @enderror" id="school_name" name="school_name" value="{{ old('school_name', $profile?->school_name) }}" required>@error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-4"><label class="form-label" for="phone">Telepon <span class="required-mark">*</span></label><input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $profile?->phone) }}" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label class="form-label" for="address">Alamat <span class="required-mark">*</span></label><textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ old('address', $profile?->address) }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-12"><label class="form-label" for="email">Email <span class="required-mark">*</span></label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $profile?->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div>
                        <div class="admin-form-section mt-4">
                            <h3 class="admin-form-section-title">Arah sekolah</h3>
                            <div class="mb-3"><label class="form-label" for="vision">Visi <span class="required-mark">*</span></label><textarea class="form-control @error('vision') is-invalid @enderror" id="vision" name="vision" rows="4" required>{{ old('vision', $profile?->vision) }}</textarea>@error('vision')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div><label class="form-label" for="mission">Misi <span class="required-mark">*</span></label><textarea class="form-control @error('mission') is-invalid @enderror" id="mission" name="mission" rows="8" required>{{ old('mission', $profile?->mission) }}</textarea>@error('mission')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Pisahkan setiap butir misi dengan baris baru.</div></div>
                        </div>
                    </div>
                    <div class="admin-card-footer d-flex flex-wrap justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Profil</button></div>
                </section>
            </div>
            <div class="col-xl-4">
                <section class="admin-card">
                    <div class="admin-card-header"><div><span class="eyebrow">Identitas visual</span><h2 class="h3 mb-0">Logo sekolah</h2></div></div>
                    <div class="admin-card-body">
                        <div class="upload-zone {{ $profile?->logo_path ? 'has-preview' : '' }}" id="logoPreview" @if($profile?->logo_path) style="background-image:url('{{ asset('storage/'.$profile->logo_path) }}')" @endif><span>{{ $profile?->logo_path ? 'Logo saat ini' : 'Belum ada logo' }}</span></div>
                        <label class="form-label mt-3" for="logo">{{ $profile?->logo_path ? 'Ganti logo' : 'Unggah logo' }}</label>
                        <input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="logoPreview">
                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">JPEG, PNG, atau WebP. Maksimal 2 MB.</div>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection
