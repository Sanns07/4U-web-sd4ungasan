@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran — Panel Admin')
@section('admin_page', 'schedules')

@section('content')
    <header class="admin-page-head"><div class="admin-breadcrumb"><span><a href="{{ route('admin.dashboard') }}">Admin</a></span><span>Akademik</span><span>Jadwal</span></div><div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3"><div><span class="eyebrow">Senin—Sabtu</span><h1>Jadwal pelajaran.</h1><p class="text-muted-warm mb-0">Susun slot mingguan sekaligus mencegah bentrok kelas, guru, dan ruang dalam periode yang sama.</p></div><a class="btn btn-primary" href="{{ route('admin.schedules.create', $selectedClass ? ['class_id' => $selectedClass->id] : []) }}">+ Tambah Slot</a></div></header>

    <form action="{{ route('admin.schedules.index') }}" method="get" class="admin-filter row g-2 align-items-end mb-4"><div class="col-md-6 col-xl-3"><label class="form-label" for="schedule_period">Periode</label><select class="form-select" id="schedule_period" name="period_id"><option value="">Semua periode</option>@foreach($periods as $period)<option value="{{ $period->id }}" @selected((int) $selectedPeriodId === $period->id)>{{ $period->academic_year }} · {{ $period->semester->label() }}{{ $period->is_active ? ' (aktif)' : '' }}</option>@endforeach</select></div><div class="col-md-6 col-xl-3"><label class="form-label" for="schedule_class">Kelas</label><select class="form-select" id="schedule_class" name="class_id"><option value="">Semua kelas</option>@foreach($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected($selectedClass?->id === $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select></div><div class="col-md-9 col-xl-4"><label class="form-label" for="schedule_teacher">Guru pengampu</label><select class="form-select" id="schedule_teacher" name="teacher_id"><option value="">Semua guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($selectedTeacher?->id === $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div><div class="col-md-3 col-xl-2 d-grid"><button class="btn btn-outline-primary" type="submit">Tampilkan</button></div></form>

    @if($classes->isEmpty() && $schedules->isEmpty())
        <section class="empty-state"><span class="empty-state-mark">—</span><span class="eyebrow">Belum ada kelas</span><h2>Jadwal belum dapat disusun.</h2><p class="text-muted-warm mb-0">Buat kelas dan penugasan mengajar terlebih dahulu.</p></section>
    @elseif($schedules->isEmpty())
        <section class="empty-state"><span class="empty-state-mark">—</span><span class="eyebrow">{{ $selectedTeacher?->name ?? $selectedClass?->name ?? 'Filter terpilih' }}</span><h2>{{ $selectedClass ? 'Jadwal kelas belum tersedia.' : 'Jadwal belum tersedia.' }}</h2><p class="text-muted-warm">Tambahkan slot dari penugasan mengajar aktif atau ubah filter.</p><a class="btn btn-primary" href="{{ route('admin.schedules.create', $selectedClass ? ['class_id' => $selectedClass->id] : []) }}">+ Tambah Slot</a></section>
    @else
        <div class="admin-schedule-board">
            @foreach($days as $day)
                @php($daySchedules = $schedulesByDay->get($day->value, collect()))
                <section class="schedule-day"><div class="d-flex justify-content-between align-items-center"><h2 class="h3 mb-0">{{ $day->label() }}</h2><span class="small text-muted-warm">{{ $daySchedules->count() }} slot</span></div>
                    @forelse($daySchedules as $schedule)
                        <article class="schedule-slot"><span>{{ $schedule->startTimeLabel() }}–{{ $schedule->endTimeLabel() }}</span><strong>{{ $schedule->teachingAssignment->subject->name }}</strong><small>{{ $schedule->teachingAssignment->teacher->name }} · {{ $schedule->teachingAssignment->schoolClass->name }} · {{ $schedule->room ?: $schedule->teachingAssignment->schoolClass->room ?: 'Ruang belum diatur' }}</small>@if(!$selectedPeriodId)<small>{{ $schedule->teachingAssignment->schoolClass->academicPeriod->academic_year }} · {{ $schedule->teachingAssignment->schoolClass->academicPeriod->semester->label() }}</small>@endif<div class="d-flex gap-2 mt-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.schedules.edit', $schedule) }}">Edit</a><form action="{{ route('admin.schedules.destroy', $schedule) }}" method="post" data-confirm="Hapus slot {{ $schedule->teachingAssignment->subject->name }} pada {{ $day->label() }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form></div></article>
                    @empty
                        <p class="small text-muted-warm mb-0 mt-3">Belum ada pelajaran terjadwal.</p>
                    @endforelse
                </section>
            @endforeach
        </div>
    @endif
@endsection
