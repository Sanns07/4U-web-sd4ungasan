@extends('layouts.admin')

@section('title', 'Rekap Jurnal 7 Kebiasaan — Panel Admin')
@section('admin_page', 'journal')

@section('content')
    <header class="admin-page-head">
        <div class="admin-breadcrumb">
            <span><a href="{{ route('admin.dashboard') }}">Admin</a></span>
            <span>Rekap Jurnal 7 Kebiasaan</span>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="eyebrow">Pemantauan Karakter Siswa</span>
                <h1>Rekap Jurnal 7 Kebiasaan Anak Hebat.</h1>
                <p class="text-muted-warm mb-0">Pantau dan ekspor catatan pembiasaan karakter harian siswa.</p>
            </div>
            <div>
                <a
                    href="{{ route('admin.journal.export', request()->query()) }}"
                    class="btn btn-success d-flex align-items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    Export ke Excel/CSV
                </a>
            </div>
        </div>
    </header>

    <form action="{{ route('admin.journal.index') }}" method="get" class="admin-filter row g-2 align-items-end mb-4">
        <div class="col-lg-3">
            <label class="form-label" for="filter_month">Bulan</label>
            <input
                type="month"
                id="filter_month"
                name="month"
                class="form-control"
                value="{{ request('month') }}"
            >
        </div>
        <div class="col-lg-3">
            <label class="form-label" for="filter_class">Kelas</label>
            <select id="filter_class" name="class_id" class="form-select">
                <option value="">Semua kelas aktif</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label class="form-label" for="filter_search">Cari siswa</label>
            <input
                type="search"
                id="filter_search"
                name="q"
                class="form-control"
                placeholder="Nama atau NISN..."
                value="{{ request('q') }}"
            >
        </div>
        <div class="col-lg-2 d-grid">
            <button class="btn btn-outline-primary" type="submit">Terapkan</button>
        </div>
    </form>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <span class="eyebrow">Hasil Data</span>
                <h2 class="h3 mb-0">{{ $journals->total() }} entri jurnal ditemukan</h2>
            </div>
            @if(request()->hasAny(['month', 'class_id', 'q']))
                <a class="small" href="{{ route('admin.journal.index') }}">Reset filter</a>
            @endif
        </div>

        @if($journals->isEmpty())
            <div class="empty-state m-3">
                <span class="empty-state-mark">—</span>
                <span class="eyebrow">Tidak ada hasil</span>
                <h2>Data jurnal belum ditemukan.</h2>
                <p class="text-muted-warm">
                    {{ request()->hasAny(['month', 'class_id', 'q']) ? 'Ubah filter atau kata kunci pencarian yang dipilih.' : 'Belum ada entri jurnal yang diisi oleh siswa.' }}
                </p>
            </div>
        @else
            <div class="table-responsive table-responsive-stack">
                <table class="table table-admin align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Agama</th>
                            <th>1. Bangun</th>
                            <th>2. Ibadah</th>
                            <th>3. Olahraga</th>
                            <th>4. Makan (P/S/M)</th>
                            <th>5. Belajar</th>
                            <th>6. Sosial</th>
                            <th>7. Tidur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journals as $journal)
                            @php
                                $student = $journal->student;
                                $activeClass = $student?->activeEnrollment?->schoolClass;
                            @endphp
                            <tr>
                                <td data-label="Tanggal" class="text-nowrap">
                                    <strong>{{ $journal->journal_date?->format('d/m/Y') }}</strong>
                                    <span class="d-block small text-muted-warm">{{ $journal->journal_date?->locale('id')->isoFormat('dddd') }}</span>
                                </td>
                                <td data-label="Siswa">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(mb_substr($student?->name ?? '?', 0, 2)) }}</span>
                                        <div>
                                            <strong>{{ $student?->name ?? '—' }}</strong>
                                            <span class="d-block small text-muted-warm">NISN {{ $student?->nisn ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Kelas">
                                    @if($activeClass)
                                        <span class="badge badge-soft-success">{{ $activeClass->name }}</span>
                                    @else
                                        <span class="text-muted-warm">—</span>
                                    @endif
                                </td>
                                <td data-label="Agama">
                                    {{ $student?->religion?->label() ?? '—' }}
                                </td>
                                <td data-label="1. Bangun">
                                    <span class="badge bg-light text-dark border">{{ $journal->wake_up_time ? substr($journal->wake_up_time, 0, 5) : '—' }}</span>
                                </td>
                                <td data-label="2. Ibadah">
                                    @if(is_array($journal->worship_items) && count($journal->worship_items) > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" title="{{ implode(', ', $journal->worship_items) }}">
                                            {{ count($journal->worship_items) }} Dicentang
                                        </span>
                                    @else
                                        <span class="text-muted-warm">—</span>
                                    @endif
                                </td>
                                <td data-label="3. Olahraga">
                                    <span class="small d-inline-block text-truncate" style="max-width: 140px;" title="{{ $journal->exercise_activity }}">
                                        {{ $journal->exercise_activity ?: '—' }}
                                    </span>
                                </td>
                                <td data-label="4. Makan (P/S/M)">
                                    <span class="small d-block text-truncate" style="max-width: 150px;" title="Pagi: {{ $journal->meal_breakfast }} | Siang: {{ $journal->meal_lunch }} | Malam: {{ $journal->meal_dinner }}">
                                        <strong>P:</strong> {{ $journal->meal_breakfast }}<br>
                                        <strong>S:</strong> {{ $journal->meal_lunch }}<br>
                                        <strong>M:</strong> {{ $journal->meal_dinner }}
                                    </span>
                                </td>
                                <td data-label="5. Belajar">
                                    <span class="small d-inline-block text-truncate" style="max-width: 140px;" title="{{ $journal->learning_activity }}">
                                        {{ $journal->learning_activity ?: '—' }}
                                    </span>
                                </td>
                                <td data-label="6. Sosial">
                                    <span class="small d-inline-block text-truncate" style="max-width: 140px;" title="{{ $journal->social_activity }}">
                                        {{ $journal->social_activity ?: '—' }}
                                    </span>
                                </td>
                                <td data-label="7. Tidur">
                                    <span class="badge bg-light text-dark border">{{ $journal->sleep_time ? substr($journal->sleep_time, 0, 5) : '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($journals->hasPages())
                <div class="admin-card-footer">
                    {{ $journals->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection
