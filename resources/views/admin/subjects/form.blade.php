@extends('layouts.admin')

@php($editing = $subject->exists)
@section('title', ($editing ? 'Edit' : 'Tambah').' Mata Pelajaran — Panel Admin')
@section('admin_page', 'subjects')

@section('content')
    <header class="admin-page-head"><div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span><a href="{{ route('admin.subjects.index') }}">Mata Pelajaran</a></span><span>{{ $editing ? 'Edit' : 'Tambah' }}</span></div><span class="eyebrow">Data akademik</span><h1>{{ $editing ? 'Edit mata pelajaran.' : 'Tambah mata pelajaran.' }}</h1><p class="text-muted-warm mb-0">Kode wajib unik dan digunakan sebagai identitas singkat mapel.</p></header>
    @include('admin.partials.validation-errors')
    <form action="{{ $editing ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}" method="post">@csrf @if($editing) @method('PUT') @endif<section class="admin-card"><div class="admin-card-header"><div><span class="eyebrow">Data mapel</span><h2 class="h3 mb-0">Identitas mata pelajaran</h2></div></div><div class="admin-card-body"><div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="code">Kode <span class="required-mark">*</span></label><input class="form-control text-uppercase @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $subject->code) }}" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-8"><label class="form-label" for="name">Nama mata pelajaran <span class="required-mark">*</span></label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $subject->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label" for="description">Deskripsi singkat</label><textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $subject->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $subject->exists ? $subject->is_active : true))><label class="form-check-label" for="is_active">Aktif dan dapat dipilih pada penugasan</label></div></div>
    </div></div><div class="admin-card-footer d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('admin.subjects.index') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Mata Pelajaran</button></div></section></form>
@endsection
