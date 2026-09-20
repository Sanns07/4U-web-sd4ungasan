@extends('layouts.admin')

@section('title', 'Data Guru — Panel Admin')
@section('admin_page', 'teachers')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Guru</span></div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3"><div><span class="eyebrow">Data tenaga pendidik</span><h1>Data guru.</h1><p class="text-muted-warm mb-0">Guru dikelola sebagai data akademik dan tidak memiliki akun login.</p></div><a class="btn btn-primary" href="{{ route('admin.teachers.create') }}">+ Tambah Guru</a></div>
    </header>

    <form action="{{ route('admin.teachers.index') }}" method="get" class="admin-filter row g-2 align-items-end mb-4">
        <div class="col-lg-5"><label class="form-label" for="teacher_search">Cari guru</label><input class="form-control" id="teacher_search" name="q" type="search" value="{{ request('q') }}" placeholder="Nama atau NIP"></div>
        <div class="col-sm-6 col-lg-2"><label class="form-label" for="teacher_status">Status</label><select class="form-select" id="teacher_status" name="status"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></div>
        <div class="col-sm-6 col-lg-3"><label class="form-label" for="teacher_public">Direktori publik</label><select class="form-select" id="teacher_public" name="public"><option value="">Semua</option><option value="shown" @selected(request('public') === 'shown')>Ditampilkan</option><option value="hidden" @selected(request('public') === 'hidden')>Disembunyikan</option></select></div>
        <div class="col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Terapkan</button></div>
    </form>

    <section class="admin-card">
        <div class="admin-card-header"><div><span class="eyebrow">Hasil data</span><h2 class="h3 mb-0">{{ $teachers->total() }} guru ditemukan</h2></div>@if(request()->hasAny(['q','status','public']))<a class="small" href="{{ route('admin.teachers.index') }}">Reset filter</a>@endif</div>
        @if ($teachers->isEmpty())
            <div class="empty-state m-3"><span class="empty-state-mark">—</span><span class="eyebrow">Tidak ada hasil</span><h2>{{ request()->hasAny(['q','status','public']) ? 'Guru tidak ditemukan.' : 'Data guru belum tersedia.' }}</h2><p class="text-muted-warm">{{ request()->hasAny(['q','status','public']) ? 'Ubah kata kunci atau filter yang digunakan.' : 'Tambahkan guru pertama untuk mulai mengelola direktori dan data akademik.' }}</p></div>
        @else
            <div class="table-responsive table-responsive-stack"><table class="table table-admin"><thead><tr><th>Guru</th><th>NIP</th><th>Kontak</th><th>Relasi</th><th>Status</th><th>Direktori</th><th class="text-end">Aksi</th></tr></thead><tbody>
                @foreach($teachers as $teacher)
                    <tr>
                        <td data-label="Guru"><div class="d-flex align-items-center gap-2"><span class="avatar">{{ strtoupper(mb_substr($teacher->name, 0, 2)) }}</span><div><strong>{{ $teacher->name }}</strong><span class="d-block small text-muted-warm">{{ $teacher->public_title }}</span></div></div></td>
                        <td data-label="NIP">{{ $teacher->nip ?: '—' }}</td><td data-label="Kontak">{{ $teacher->email ?: '—' }}<span class="d-block small text-muted-warm">{{ $teacher->phone }}</span></td>
                        <td data-label="Relasi">{{ $teacher->teaching_assignments_count }} penugasan<span class="d-block small text-muted-warm">{{ $teacher->homeroom_classes_count }} wali · {{ $teacher->organization_memberships_count }} organisasi</span></td>
                        <td data-label="Status"><span class="badge {{ $teacher->is_active ? 'badge-soft-success' : 'text-bg-light' }}">{{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td data-label="Direktori"><span class="badge {{ $teacher->is_public ? 'badge-soft-success' : 'text-bg-light' }}">{{ $teacher->is_public ? 'Tampil' : 'Disembunyikan' }}</span></td>
                        <td data-label="Aksi" class="text-end"><div class="row-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.teachers.show', $teacher) }}">Detail</a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.teachers.edit', $teacher) }}">Edit</a><form action="{{ route('admin.teachers.toggle-status', $teacher) }}" method="post" data-confirm="{{ $teacher->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $teacher->name }}?">@csrf @method('PATCH')<button class="btn btn-sm {{ $teacher->is_active ? 'btn-outline-danger' : 'btn-outline-primary' }}" type="submit">{{ $teacher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div></td>
                    </tr>
                @endforeach
            </tbody></table></div>
            @if($teachers->hasPages())<div class="admin-card-footer">{{ $teachers->links() }}</div>@endif
        @endif
    </section>
@endsection
