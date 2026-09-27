@extends('layouts.public')

@section('title', 'Profil Sekolah — '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan'))
@section('meta_description', 'Profil, visi misi, dan struktur organisasi '.($schoolProfile?->school_name ?: 'SD No. 4 Ungasan').'.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <nav aria-label="Breadcrumb"><ol class="breadcrumb small mb-4"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">Profil sekolah</li></ol></nav>
            <span class="eyebrow eyebrow-light">Tentang sekolah</span>
            <h1>Berakar pada nilai.<br><em class="text-gold">Bergerak bersama zaman.</em></h1>
            <p class="lead mt-4 mb-0">Sejak 1987, kami merawat lingkungan belajar yang menjadikan kecakapan dan kemanusiaan tumbuh beriringan.</p>
        </div>
    </section>

    <section class="section-space bg-cream-soft">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="portrait-frame">
                        @if ($schoolProfile?->logo_path)
                            <div class="media-placeholder media-placeholder--light p-5"><img class="mw-100 h-auto" src="{{ asset('storage/'.ltrim($schoolProfile->logo_path, '/')) }}" alt="Logo {{ $schoolProfile->school_name }}"></div>
                        @else
                            <div class="media-placeholder media-placeholder--light" role="img" aria-label="Gedung utama sekolah"><span>Gedung utama {{ $schoolProfile?->school_name ?: 'SMA Negeri Cendekia' }}</span></div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <span class="eyebrow">01 · Identitas</span>
                    <h2 class="display-5 mb-4">{{ $schoolProfile?->school_name ?: 'Profil sekolah sedang diperbarui' }}</h2>
                    <p class="fs-5 font-serif fst-italic text-forest">Sekolah menengah yang berpihak pada pertumbuhan setiap siswa.</p>
                    @if ($schoolProfile)
                        <p class="text-muted-warm">Berada di jantung Kota Denpasar, sekolah kami mempertemukan tradisi lokal, wawasan global, dan pembelajaran berbasis pengalaman dalam komunitas yang inklusif.</p>
                        <dl class="row small mt-4 mb-0">
                            <dt class="col-sm-4 py-2 border-top border-soft">Nama sekolah</dt><dd class="col-sm-8 py-2 border-top border-soft">{{ $schoolProfile->school_name }}</dd>
                            <dt class="col-sm-4 py-2 border-top border-soft">Alamat</dt><dd class="col-sm-8 py-2 border-top border-soft">{{ $schoolProfile->address ?: 'Belum tersedia' }}</dd>
                            <dt class="col-sm-4 py-2 border-top border-soft">Telepon</dt><dd class="col-sm-8 py-2 border-top border-soft">{{ $schoolProfile->phone ?: 'Belum tersedia' }}</dd>
                            <dt class="col-sm-4 py-2 border-top border-bottom border-soft">Email</dt><dd class="col-sm-8 py-2 border-top border-bottom border-soft">{{ $schoolProfile->email ?: 'Belum tersedia' }}</dd>
                        </dl>
                    @else
                        <div class="empty-state mt-4"><span class="empty-state-mark" aria-hidden="true">—</span><h3 class="h4">Profil sedang diperbarui</h3><p class="text-muted-warm mb-0">Identitas dan kontak sekolah akan ditampilkan kembali setelah pembaruan selesai.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="section-space">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5"><span class="eyebrow">02 · Arah kami</span><h2 class="display-5">Visi yang menjadi kompas.</h2></div>
                <div class="col-lg-7">
                    <div class="card border-0 bg-forest text-white mb-4">
                        <div class="card-body p-4 p-md-5"><span class="small tracking-wide text-uppercase text-gold">Visi</span><blockquote class="font-serif display-6 mt-3 mb-0">“{{ $schoolProfile?->vision ?: 'Visi sekolah sedang diperbarui.' }}”</blockquote></div>
                    </div>
                    <h3 class="h4 mt-5 mb-4">Misi sekolah</h3>
                    @if (count($missionItems))
                        <div class="d-grid gap-0">
                            @foreach ($missionItems as $mission)
                                <div class="row g-3 py-4 border-top {{ $loop->last ? 'border-bottom' : '' }} border-soft"><div class="col-2 col-sm-1 feature-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div><div class="col"><h4 class="h5">{{ $mission }}</h4></div></div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><h4 class="h4">Misi sedang diperbarui</h4><p class="text-muted-warm mb-0">Rumusan misi sekolah belum tersedia saat ini.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="section-space bg-cream-soft" id="struktur">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5"><div class="section-heading sticky-lg-top" style="top: 7rem"><span class="eyebrow">03 · Struktur organisasi</span><h2>Orang-orang yang menjaga arah sekolah.</h2><p>Struktur ditampilkan sesuai urutan aktif yang dikelola admin. Anggota tanpa profil guru tetap dapat ditampilkan sebagai anggota organisasi.</p></div></div>
                <div class="col-lg-7">
                    @if ($organizationMembers->isNotEmpty())
                        <div class="organization-line d-grid gap-5">
                            @foreach ($organizationMembers as $member)
                                @php($memberPhoto = $member->photo_path ?: $member->teacher?->photo_path)
                                <article class="org-item">
                                    <div class="card card-hover">
                                        @if ($memberPhoto)
                                            <div class="row g-0"><div class="col-sm-4"><img class="w-100 h-100" src="{{ asset('storage/'.ltrim($memberPhoto, '/')) }}" alt="Potret {{ $member->displayName() }}"></div><div class="col-sm-8"><div class="card-body p-4"><span class="badge {{ $member->category->value === 'komite' ? 'badge-announcement' : 'badge-news' }} mb-3">{{ $member->category->label() }}</span><h3 class="h3 mb-1">{{ $member->displayName() }}</h3><p class="text-muted-warm mb-0">{{ $member->position_name }}</p></div></div></div>
                                        @else
                                            <div class="card-body p-4"><span class="badge {{ $member->category->value === 'komite' ? 'badge-announcement' : 'badge-news' }} mb-3">{{ $member->category->label() }}</span><h3 class="h4 mb-1">{{ $member->displayName() }}</h3><p class="text-muted-warm mb-0">{{ $member->position_name }}</p></div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><h3 class="h3">Struktur sedang diperbarui</h3><p class="text-muted-warm mb-0">Informasi susunan organisasi sekolah akan ditampilkan kembali setelah proses pembaruan selesai.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
