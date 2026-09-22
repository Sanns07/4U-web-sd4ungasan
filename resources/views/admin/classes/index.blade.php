@extends('layouts.admin')

@section('title', 'Kelas & Penempatan — Panel Admin')
@section('admin_page', 'classes')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Data Akademik</span><span>Kelas &amp; Penempatan</span></div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div><span class="eyebrow">Rombongan belajar</span><h1>Kelas &amp; penempatan.</h1><p class="text-muted-warm mb-0">Kelola kelas, wali kelas, dan penempatan siswa per periode.</p></div>
            <a class="btn btn-primary" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
        </div>
    </header>

    <form action="{{ route('admin.classes.index') }}" method="get" class="admin-filter row g-2 align-items-end mb-4">
        <div class="col-lg-4"><label class="form-label" for="class_search">Cari kelas</label><input class="form-control" id="class_search" name="q" type="search" value="{{ request('q') }}" placeholder="Nama, ruang, atau wali kelas"></div>
        <div class="col-md-5 col-lg-3"><label class="form-label" for="class_period">Periode</label><select class="form-select" id="class_period" name="period_id">@forelse($periods as $period)<option value="{{ $period->id }}" @selected((int) $selectedPeriodId === $period->id)>{{ $period->academic_year }} · {{ $period->semester->label() }}{{ $period->is_active ? ' (aktif)' : '' }}</option>@empty<option value="">Belum ada periode</option>@endforelse</select></div>
        <div class="col-md-4 col-lg-3"><label class="form-label" for="class_status">Status</label><select class="form-select" id="class_status" name="status"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></div>
        <div class="col-md-3 col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Terapkan</button></div>
    </form>

    <section class="admin-card">
        <div class="admin-card-header"><div><span class="eyebrow">Hasil data</span><h2 class="h3 mb-0">{{ $classes->total() }} kelas ditemukan</h2></div>@if(request()->hasAny(['q', 'period_id', 'status']))<a class="small" href="{{ route('admin.classes.index') }}">Reset filter</a>@endif</div>
        @if($classes->isEmpty())
            <div class="empty-state m-3"><span class="empty-state-mark">—</span><span class="eyebrow">Belum ada data</span><h2>{{ request()->hasAny(['q', 'status']) ? 'Kelas tidak ditemukan.' : 'Kelas belum tersedia.' }}</h2><p class="text-muted-warm">{{ request()->hasAny(['q', 'status']) ? 'Ubah pencarian atau filter yang dipilih.' : 'Tambahkan kelas untuk periode akademik yang dipilih.' }}</p><a class="btn btn-primary" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a></div>
        @else
            <div class="table-responsive table-responsive-stack"><table class="table table-admin align-middle mb-0"><thead><tr><th>Kelas</th><th>Periode</th><th>Wali kelas</th><th>Siswa aktif</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
                @foreach($classes as $schoolClass)
                    <tr><td data-label="Kelas"><strong>{{ $schoolClass->name }}</strong><span class="d-block small text-muted-warm">Tingkat {{ $schoolClass->grade_level }} · {{ $schoolClass->room ?: 'Ruang belum diatur' }}</span></td><td data-label="Periode">{{ $schoolClass->academicPeriod->academic_year }}<span class="d-block small text-muted-warm">{{ $schoolClass->academicPeriod->semester->label() }}</span></td><td data-label="Wali kelas">{{ $schoolClass->homeroomTeacher?->name ?: 'Belum ditentukan' }}</td><td data-label="Siswa aktif">{{ $schoolClass->active_enrollments_count }} siswa</td><td data-label="Status"><span class="badge {{ $schoolClass->is_active ? 'badge-soft-success' : 'text-bg-secondary' }}">{{ $schoolClass->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td data-label="Aksi" class="text-end"><div class="row-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.classes.show', $schoolClass) }}">Detail</a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.classes.edit', $schoolClass) }}">Edit</a></div></td></tr>
                @endforeach
            </tbody></table></div>
            @if($classes->hasPages())<div class="admin-card-footer">{{ $classes->links() }}</div>@endif
        @endif
    </section>
@endsection
