@extends('layouts.admin')

@php
    $editing = $student->exists;
@endphp
@section('title', ($editing ? 'Edit' : 'Tambah').' Siswa — Panel Admin')
@section('admin_page', 'students')

@section('content')
    <header class="admin-page-head"><div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span><a href="{{ route('admin.students.index') }}">Siswa &amp; Akun</a></span><span>{{ $editing ? 'Edit' : 'Tambah' }}</span></div><span class="eyebrow">Data siswa</span><h1>{{ $editing ? 'Edit siswa & akun.' : 'Tambah siswa & akun.' }}</h1><p class="text-muted-warm mb-0">Biodata, akun, dan penempatan kelas aktif disimpan dalam satu transaksi.</p></header>
    @include('admin.partials.validation-errors')
    <form action="{{ $editing ? route('admin.students.update', $student) : route('admin.students.store') }}" method="post">@csrf @if($editing) @method('PUT') @endif
        <section class="admin-card"><div class="admin-card-body">
            <div class="admin-form-section"><h2 class="admin-form-section-title">Data pribadi</h2><div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="name">Nama lengkap <span class="required-mark">*</span></label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $student->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label" for="nisn">NISN <span class="required-mark">*</span></label><input class="form-control @error('nisn') is-invalid @enderror" id="nisn" name="nisn" inputmode="numeric" maxlength="10" value="{{ old('nisn', $student->nisn) }}" required>@error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label" for="nis">NIS</label><input class="form-control @error('nis') is-invalid @enderror" id="nis" name="nis" value="{{ old('nis', $student->nis) }}">@error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label" for="gender">Jenis kelamin <span class="required-mark">*</span></label><select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required><option value="">Pilih</option>@foreach($genders as $gender)<option value="{{ $gender->value }}" @selected(old('gender', $student->gender?->value) === $gender->value)>{{ $gender->label() }}</option>@endforeach</select>@error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label" for="religion">Agama</label><select class="form-select @error('religion') is-invalid @enderror" id="religion" name="religion"><option value="">Pilih Agama</option>@foreach($religions as $religion)<option value="{{ $religion->value }}" @selected(old('religion', $student->religion?->value) === $religion->value)>{{ $religion->label() }}</option>@endforeach</select>@error('religion')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label" for="birth_place">Tempat lahir</label><input class="form-control" id="birth_place" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}"></div>
                <div class="col-md-3"><label class="form-label" for="birth_date">Tanggal lahir</label><input class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">@error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="address">Alamat</label><textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $student->address) }}</textarea></div>
                <div class="col-md-6"><label class="form-label" for="parent_name">Nama orang tua/wali</label><input class="form-control" id="parent_name" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}"></div>
                <div class="col-md-6"><label class="form-label" for="parent_phone">Kontak orang tua/wali</label><input class="form-control" id="parent_phone" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}"><div class="form-text">Data internal dan tidak ditampilkan ke publik.</div></div>
            </div></div>
            <div class="admin-form-section"><h2 class="admin-form-section-title">Akun siswa</h2><div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="username">Nama pengguna <span class="required-mark">*</span></label><input class="form-control @error('username') is-invalid @enderror" id="username" name="username" autocomplete="username" value="{{ old('username', $student->user?->username ?: $student->nisn) }}" required>@error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Huruf kecil, angka, titik, atau garis bawah. NISN dapat digunakan sebagai username awal.</div></div>
                <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $student->exists ? $student->user->is_active : true))><label class="form-check-label" for="is_active">Akun dapat login</label></div></div>
                <div class="col-md-6"><label class="form-label" for="password">{{ $editing ? 'Kata sandi baru' : 'Kata sandi awal' }} @unless($editing)<span class="required-mark">*</span>@endunless</label><div class="input-group"><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="new-password" @required(!$editing)><button class="btn btn-outline-secondary" type="button" data-password-toggle="password">Tampilkan</button></div>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @if($editing)<div class="form-text">Kosongkan agar kata sandi lama tidak berubah.</div>@endif</div>
                <div class="col-md-6"><label class="form-label" for="password_confirmation">Konfirmasi kata sandi @unless($editing)<span class="required-mark">*</span>@endunless</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(!$editing)></div>
            </div></div>
            <div class="admin-form-section"><h2 class="admin-form-section-title">Status siswa</h2><div class="row g-3"><div class="col-md-6"><label class="form-label" for="status">Status <span class="required-mark">*</span></label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $student->status?->value ?? 'aktif') === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Alumni dan siswa arsip otomatis tidak dapat login dan seluruh penempatan aktifnya diselesaikan.</div></div><div class="col-md-6"><label class="form-label" for="graduation_year">Tahun kelulusan</label><input class="form-control @error('graduation_year') is-invalid @enderror" id="graduation_year" name="graduation_year" type="number" min="2000" max="2100" value="{{ old('graduation_year', $student->graduation_year) }}">@error('graduation_year')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>
            @php
                $currentEnrollment = $student->activeEnrollment;
                $today = now();
                $periodStart = $activePeriod?->start_date;
                $periodEnd = $activePeriod?->end_date;
                $effectiveMinimum = $currentEnrollment && $currentEnrollment->enrolled_at->gt($periodStart)
                    ? $currentEnrollment->enrolled_at
                    : $periodStart;
                $defaultEnrollmentDate = $effectiveMinimum && $today->lt($effectiveMinimum)
                    ? $effectiveMinimum
                    : ($periodEnd && $today->gt($periodEnd) ? $periodEnd : $today);
            @endphp
            <div class="admin-form-section mb-0"><h2 class="admin-form-section-title">Penempatan kelas aktif</h2>
                @if($activePeriod)
                    <p class="small text-muted-warm">{{ $activePeriod->academic_year }} · {{ $activePeriod->semester->label() }}. Mengganti kelas akan menutup penempatan lama sebagai riwayat pindah.</p>
                    <div class="row g-3">
                        <div class="col-md-7"><label class="form-label" for="class_id">Kelas</label><select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id"><option value="">Belum ditempatkan</option>@foreach($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((int) old('class_id', $currentEnrollment?->class_id) === $schoolClass->id)>{{ $schoolClass->name }} · Tingkat {{ $schoolClass->grade_level }}</option>@endforeach</select>@error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @if($classes->isEmpty())<div class="form-text text-warning">Belum ada kelas aktif pada periode ini.</div>@endif</div>
                        <div class="col-md-5"><label class="form-label" for="enrolled_at">Tanggal efektif <span class="required-mark">*</span></label><input class="form-control @error('enrolled_at') is-invalid @enderror" id="enrolled_at" name="enrolled_at" type="date" min="{{ $effectiveMinimum->format('Y-m-d') }}" max="{{ $periodEnd->format('Y-m-d') }}" value="{{ old('enrolled_at', $defaultEnrollmentDate->format('Y-m-d')) }}" required>@error('enrolled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Dipakai sebagai tanggal masuk, pindah, atau selesai bila kelas dikosongkan.</div></div>
                    </div>
                @else
                    <input type="hidden" name="enrolled_at" value="{{ old('enrolled_at', now()->toDateString()) }}">
                    <div class="alert alert-warning mb-0" role="status">Belum ada periode akademik aktif. Siswa tetap dapat disimpan tanpa penempatan kelas.</div>
                @endif
            </div>
        </div><div class="admin-card-footer d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('admin.students.index') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Siswa</button></div></section>
    </form>
@endsection
