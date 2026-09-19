@extends('layouts.admin')

@section('title', 'Struktur Organisasi — Panel Admin')
@section('admin_page', 'organization')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Struktur Organisasi</span></div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div><span class="eyebrow">Website sekolah</span><h1>Struktur organisasi.</h1><p class="text-muted-warm mb-0">Atur anggota, jabatan, kategori, status publik, dan urutan tampil.</p></div>
            <a class="btn btn-primary" href="{{ route('admin.organization-members.create') }}">+ Tambah Anggota</a>
        </div>
    </header>

    <section class="admin-card">
        <div class="admin-card-header"><div><span class="eyebrow">Urutan publik</span><h2 class="h3 mb-1">Susunan organisasi</h2><p class="small text-muted-warm mb-0">Nomor urutan lebih kecil tampil lebih dahulu.</p></div></div>
        @if ($members->isEmpty())
            <div class="empty-state m-3"><span class="empty-state-mark">—</span><span class="eyebrow">Belum ada data</span><h2>Struktur organisasi belum tersedia.</h2><p class="text-muted-warm">Tambahkan anggota pertama untuk mulai menyusun organisasi sekolah.</p><a class="btn btn-primary" href="{{ route('admin.organization-members.create') }}">Tambah Anggota</a></div>
        @else
            <div class="table-responsive table-responsive-stack">
                <table class="table table-admin align-middle mb-0">
                    <thead><tr><th>Urutan</th><th>Anggota</th><th>Jabatan</th><th>Kategori</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td data-label="Urutan">{{ $member->display_order }}</td>
                                <td data-label="Nama"><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm">{{ strtoupper(mb_substr($member->displayName(), 0, 2)) }}</span><div><strong>{{ $member->displayName() }}</strong>@if($member->teacher)<span class="d-block small text-muted-warm">Terhubung dengan data guru</span>@endif</div></div></td>
                                <td data-label="Jabatan">{{ $member->position_name }}</td>
                                <td data-label="Kategori"><span class="badge text-bg-light">{{ $member->category->label() }}</span></td>
                                <td data-label="Status"><span class="badge {{ $member->is_active ? 'badge-soft-success' : 'text-bg-light' }}">{{ $member->is_active ? 'Aktif' : 'Disembunyikan' }}</span></td>
                                <td data-label="Aksi" class="text-end"><div class="row-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.organization-members.edit', $member) }}">Edit</a><form action="{{ route('admin.organization-members.destroy', $member) }}" method="post" data-confirm="Hapus {{ $member->displayName() }} dari struktur organisasi?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($members->hasPages())<div class="admin-card-footer">{{ $members->links() }}</div>@endif
        @endif
    </section>
@endsection
