@extends('layouts.student')

@section('content')
    <header class="portal-page-head">
        <div class="container">
            <span class="eyebrow">Portal akademik · read-only</span>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                <div><h1 class="mb-2">{{ isset($student) ? 'Jadwal belajar '.$student->name.'.' : 'Jadwal belajar siswa.' }}</h1><p class="text-muted-warm mb-0">Informasi kelas dan jadwal mingguan dari periode akademik aktif.</p></div>
                <span class="portal-period">Data akun siswa</span>
            </div>
        </div>
    </header>
    <section class="section-space-sm">
        <div class="container">
            @if ($state === 'no-profile')
                <section class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><span class="eyebrow">Profil siswa</span><h2>Profil siswa belum terhubung.</h2><p class="text-muted-warm mb-0">Akun berhasil masuk, tetapi profil siswa belum tersedia. Silakan hubungi admin sekolah.</p></section>
            @elseif ($state === 'no-period')
                <section class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><span class="eyebrow">Periode akademik</span><h2>Periode aktif belum tersedia.</h2><p class="text-muted-warm mb-0">Sekolah belum mengaktifkan tahun ajaran dan semester yang akan digunakan.</p></section>
            @elseif ($state === 'no-class')
                <section class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><span class="eyebrow">Penempatan siswa</span><h2>Kelas aktif belum tersedia.</h2><p class="text-muted-warm mb-0">Akunmu sudah aktif, tetapi admin belum menempatkanmu ke kelas untuk periode ini.</p></section>
            @else
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl"><div class="academic-chip h-100"><span>Nama siswa</span><strong>{{ $student->name }}</strong></div></div>
                    <div class="col-sm-6 col-xl"><div class="academic-chip h-100"><span>NISN</span><strong>{{ $student->nisn }}</strong></div></div>
                    <div class="col-sm-6 col-xl"><div class="academic-chip h-100"><span>Alamat</span><strong>{{ $student->address ?: 'Belum tersedia' }}</strong></div></div>
                    <div class="col-sm-6 col-xl"><div class="academic-chip h-100"><span>Kelas aktif</span><strong>{{ $schoolClass->name }}</strong></div></div>
                    <div class="col-sm-6 col-xl"><div class="academic-chip h-100"><span>Periode</span><strong>{{ $period->academic_year }} · {{ $period->semester->label() }}</strong></div></div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-lg-4">
                        <section class="card h-100"><div class="card-body"><span class="eyebrow">Pendamping kelas</span><h2 class="h3">Wali kelas.</h2>
                            @if($schoolClass->homeroomTeacher)
                                <p class="mb-1 fw-semibold">{{ $schoolClass->homeroomTeacher->name }}</p><p class="small text-muted-warm mb-0">{{ $schoolClass->homeroomTeacher->public_title ?: 'Tenaga pendidik' }}</p>
                            @else
                                <p class="text-muted-warm mb-0">Wali kelas belum tersedia untuk kelas ini.</p>
                            @endif
                        </div></section>
                    </div>
                    <div class="col-lg-8">
                        <section class="card h-100"><div class="card-body"><span class="eyebrow">Pengampu aktif</span><h2 class="h3">Guru mata pelajaran.</h2>
                            @forelse($teachingAssignments as $assignment)
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-1 py-2 border-bottom"><strong>{{ $assignment->subject->name }}</strong><span class="text-muted-warm">{{ $assignment->teacher->name }}</span></div>
                            @empty
                                <p class="text-muted-warm mb-0">Guru mata pelajaran belum tersedia untuk kelas ini.</p>
                            @endforelse
                        </div></section>
                    </div>
                </div>

                @if ($state === 'no-schedule')
                    <section class="empty-state"><span class="empty-state-mark" aria-hidden="true">—</span><span class="eyebrow">Jadwal kelas</span><h2>Jadwal belum tersedia untuk periode ini.</h2><p class="text-muted-warm mb-0">Admin sekolah masih menyusun jadwal {{ $schoolClass->name }}. Silakan periksa kembali setelah informasi diperbarui.</p></section>
                @else
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4"><div><span class="eyebrow">Senin—Sabtu</span><h2 class="h1 mb-1">Jadwal pelajaran mingguan.</h2><p class="text-muted-warm mb-0">Setiap slot memuat guru pengampu dan ruang belajar.</p></div><span class="small text-muted-warm">Periode {{ $period->academic_year }}</span></div>
                    <div class="row row-cols-1 row-cols-xl-2 g-4">
                        @foreach ($days as $day)
                            @php($daySchedules = $schedulesByDay->get($day->value, collect()))
                            <div class="col">
                                <section class="card day-schedule-card h-100" aria-labelledby="day-{{ $day->value }}">
                                    <div class="card-header d-flex justify-content-between align-items-center"><h3 class="h3 mb-0" id="day-{{ $day->value }}">{{ $day->label() }}</h3><span class="small">{{ $daySchedules->count() }} mapel</span></div>
                                    <div class="card-body p-0">
                                        @forelse ($daySchedules as $schedule)
                                            <div class="schedule-slot"><div class="schedule-time">{{ substr($schedule->start_time, 0, 5) }}<small>{{ substr($schedule->end_time, 0, 5) }} WITA</small></div><div><div class="schedule-subject">{{ $schedule->teachingAssignment->subject->name }}</div><div class="schedule-meta">{{ $schedule->teachingAssignment->teacher->name }} · {{ $schedule->room ?? $schoolClass->room }}</div></div></div>
                                        @empty
                                            <div class="p-4 text-muted-warm small">Tidak ada pelajaran terjadwal.</div>
                                        @endforelse
                                    </div>
                                </section>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
