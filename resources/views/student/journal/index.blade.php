@extends('layouts.student')

@section('title', 'Jurnal 7 Kebiasaan Anak Hebat — Portal Siswa')

@section('content')
    <header class="portal-page-head">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                <div>
                    <span class="eyebrow">Jurnal Harian Karakter</span>
                    <h1 class="mb-2">Jurnal 7 Kebiasaan Anak Hebat</h1>
                    <p class="text-muted-warm mb-0">Catat dan pantau pembiasaan karakter positif harian siswa.</p>
                </div>
                <div>
                    <a href="{{ route('student.home') }}" class="btn btn-sm btn-outline-light">Lihat Jadwal Pelajaran</a>
                </div>
            </div>
        </div>
    </header>

    <section class="section-space-sm">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <strong class="d-block mb-1">Terdapat kesalahan pengisian form:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Date Selector Bar --}}
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="text-muted small d-block">Tanggal Terpilih:</span>
                            <h3 class="h4 mb-0 text-simak-forest">
                                {{ \Carbon\Carbon::parse($selectedDateString)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                @if($selectedDateString === now()->toDateString())
                                    <span class="badge bg-success ms-2 fs-6">Hari Ini</span>
                                @endif
                                @if($journal)
                                    <span class="badge bg-primary ms-1 fs-6">✓ Terisi</span>
                                @else
                                    <span class="badge bg-secondary ms-1 fs-6">Belum Diisi</span>
                                @endif
                            </h3>
                        </div>

                        <form action="{{ route('student.journal.index') }}" method="get" class="d-flex align-items-center gap-2">
                            <label for="date-select" class="form-label mb-0 small text-nowrap">Pilih Tanggal:</label>
                            <input
                                type="date"
                                id="date-select"
                                name="date"
                                class="form-control form-control-sm"
                                max="{{ now()->toDateString() }}"
                                value="{{ $selectedDateString }}"
                                onchange="this.form.submit()"
                            >
                            <noscript><button type="submit" class="btn btn-sm btn-secondary">Buka</button></noscript>
                        </form>
                    </div>

                    {{-- Quick date pills --}}
                    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                        <span class="small text-muted align-self-center me-1">Akses Cepat:</span>
                        @for($i = 0; $i < 7; $i++)
                            @php
                                $d = now()->subDays($i);
                                $dStr = $d->toDateString();
                                $isFilled = in_array($dStr, $filledDates, true);
                                $isActive = ($dStr === $selectedDateString);
                            @endphp
                            <a
                                href="{{ route('student.journal.index', ['date' => $dStr]) }}"
                                class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} position-relative px-3"
                            >
                                {{ $i === 0 ? 'Hari Ini' : ($i === 1 ? 'Kemarin' : $d->locale('id')->isoFormat('ddd, D MMM')) }}
                                @if($isFilled)
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" title="Sudah diisi">
                                        <span class="visually-hidden">Sudah diisi</span>
                                    </span>
                                @endif
                            </a>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Main 7 Habits Form --}}
            <form action="{{ route('student.journal.store') }}" method="post" class="card shadow-sm border-0">
                @csrf
                <input type="hidden" name="journal_date" value="{{ $selectedDateString }}">

                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h5 mb-0 text-simak-forest">Formulir 7 Kebiasaan Anak Hebat</h2>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        {{-- 1. Bangun Pagi --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">1</span>
                                    <h3 class="h6 mb-0 fw-bold">Bangun Pagi</h3>
                                </div>
                                <p class="small text-muted mb-3">Membiasakan bangun pagi agar dapat melakukan aktivitas yang menyenangkan dan bermanfaat.</p>
                                <div class="row align-items-center">
                                    <label for="wake_up_time" class="col-sm-3 col-form-label fw-semibold">Jam Bangun Pagi <span class="text-danger">*</span></label>
                                    <div class="col-sm-4">
                                        <input
                                            type="time"
                                            id="wake_up_time"
                                            name="wake_up_time"
                                            class="form-control @error('wake_up_time') is-invalid @enderror"
                                            value="{{ old('wake_up_time', $journal?->wake_up_time ? substr($journal->wake_up_time, 0, 5) : '05:00') }}"
                                            required
                                        >
                                        @error('wake_up_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Beribadah --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">2</span>
                                    <h3 class="h6 mb-0 fw-bold">Beribadah</h3>
                                </div>
                                <p class="small text-muted mb-3">
                                    Dasar penting dalam membentuk karakter yang positif. 
                                    (Checklist disesuaikan dengan agama siswa: <strong>{{ $student->religion?->label() ?? 'Belum Ditentukan' }}</strong>)
                                </p>

                                @if(!$student->religion)
                                    <div class="alert alert-warning py-2 small mb-0">
                                        <em>Data agama belum diisi di profil siswa oleh admin sekolah. Anda tetap dapat melanjutkan pengisian checklist umum di bawah.</em>
                                    </div>
                                @endif

                                <div class="row g-2 pt-2">
                                    @php
                                        $checkedItems = old('worship_items', $journal?->worship_items ?? []);
                                    @endphp
                                    @forelse($worshipItems as $item)
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-check p-2 bg-white rounded border">
                                                <input
                                                    class="form-check-input ms-0 me-2"
                                                    type="checkbox"
                                                    name="worship_items[]"
                                                    value="{{ $item['label'] }}"
                                                    id="worship_{{ $item['id'] }}"
                                                    {{ in_array($item['label'], $checkedItems, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label fw-semibold small" for="worship_{{ $item['id'] }}">
                                                    {{ $item['label'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="form-check p-2 bg-white rounded border">
                                                <input
                                                    class="form-check-input ms-0 me-2"
                                                    type="checkbox"
                                                    name="worship_items[]"
                                                    value="Ibadah Harian Selesai"
                                                    id="worship_default"
                                                    {{ in_array('Ibadah Harian Selesai', $checkedItems, true) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label fw-semibold small" for="worship_default">
                                                    Melaksanakan Ibadah Harian
                                                </label>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- 3. Berolahraga --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">3</span>
                                    <h3 class="h6 mb-0 fw-bold">Berolahraga</h3>
                                </div>
                                <p class="small text-muted mb-2">Penting untuk gaya hidup sehat, mendukung kesehatan fisik dan kesejahteraan mental.</p>
                                <div>
                                    <label for="exercise_activity" class="form-label fw-semibold small">Kegiatan Olahraga / Fisik <span class="text-danger">*</span></label>
                                    <textarea
                                        id="exercise_activity"
                                        name="exercise_activity"
                                        rows="2"
                                        class="form-control @error('exercise_activity') is-invalid @enderror"
                                        placeholder="Contoh: Senam pagi 15 menit, jalan santai, bermain bola..."
                                        required
                                    >{{ old('exercise_activity', $journal?->exercise_activity) }}</textarea>
                                    @error('exercise_activity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 4. Makan Sehat dan Bergizi --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">4</span>
                                    <h3 class="h6 mb-0 fw-bold">Makan Sehat dan Bergizi</h3>
                                </div>
                                <p class="small text-muted mb-3">Konsumsi makanan sehat dan bergizi penting untuk memenuhi kebutuhan nutrisi tubuh.</p>
                                
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="meal_breakfast" class="form-label fw-semibold small">4a. Makan Pagi / Sarapan <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="meal_breakfast"
                                            name="meal_breakfast"
                                            class="form-control @error('meal_breakfast') is-invalid @enderror"
                                            placeholder="Contoh: Nasi, telur mata sapi, susu"
                                            value="{{ old('meal_breakfast', $journal?->meal_breakfast) }}"
                                            required
                                        >
                                        @error('meal_breakfast')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="meal_lunch" class="form-label fw-semibold small">4b. Makan Siang <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="meal_lunch"
                                            name="meal_lunch"
                                            class="form-control @error('meal_lunch') is-invalid @enderror"
                                            placeholder="Contoh: Nasi, sayur sop, ayam goreng, air putih"
                                            value="{{ old('meal_lunch', $journal?->meal_lunch) }}"
                                            required
                                        >
                                        @error('meal_lunch')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="meal_dinner" class="form-label fw-semibold small">4c. Makan Malam <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="meal_dinner"
                                            name="meal_dinner"
                                            class="form-control @error('meal_dinner') is-invalid @enderror"
                                            placeholder="Contoh: Nasi merah, tumis kangkung, ikan bakar"
                                            value="{{ old('meal_dinner', $journal?->meal_dinner) }}"
                                            required
                                        >
                                        @error('meal_dinner')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5. Gemar Belajar --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">5</span>
                                    <h3 class="h6 mb-0 fw-bold">Gemar Belajar</h3>
                                </div>
                                <p class="small text-muted mb-2">Aspek penting dalam perkembangan pribadi dan akademis anak.</p>
                                <div>
                                    <label for="learning_activity" class="form-label fw-semibold small">Aktivitas Belajar / Membaca <span class="text-danger">*</span></label>
                                    <textarea
                                        id="learning_activity"
                                        name="learning_activity"
                                        rows="2"
                                        class="form-control @error('learning_activity') is-invalid @enderror"
                                        placeholder="Contoh: Membaca buku ensiklopedia 30 menit, mengulang pelajaran matematika..."
                                        required
                                    >{{ old('learning_activity', $journal?->learning_activity) }}</textarea>
                                    @error('learning_activity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 6. Bermasyarakat --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">6</span>
                                    <h3 class="h6 mb-0 fw-bold">Bermasyarakat</h3>
                                </div>
                                <p class="small text-muted mb-2">Aktivitas sosial, gotong royong, budaya, dan kepedulian lingkungan di sekitar.</p>
                                <div>
                                    <label for="social_activity" class="form-label fw-semibold small">Kegiatan Sosial / Bermasyarakat <span class="text-danger">*</span></label>
                                    <textarea
                                        id="social_activity"
                                        name="social_activity"
                                        rows="2"
                                        class="form-control @error('social_activity') is-invalid @enderror"
                                        placeholder="Contoh: Membantu orang tua membersihkan halaman, menyapa tetangga, kerja bakti..."
                                        required
                                    >{{ old('social_activity', $journal?->social_activity) }}</textarea>
                                    @error('social_activity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 7. Tidur Cepat --}}
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fs-6 rounded-pill px-3">7</span>
                                    <h3 class="h6 mb-0 fw-bold">Tidur Cepat</h3>
                                </div>
                                <p class="small text-muted mb-3">Menjaga organ tubuh agar berfungsi optimal serta memulihkan kondisi mental dan emosional.</p>
                                <div class="row align-items-center">
                                    <label for="sleep_time" class="col-sm-3 col-form-label fw-semibold">Jam Tidur Malam <span class="text-danger">*</span></label>
                                    <div class="col-sm-4">
                                        <input
                                            type="time"
                                            id="sleep_time"
                                            name="sleep_time"
                                            class="form-control @error('sleep_time') is-invalid @enderror"
                                            value="{{ old('sleep_time', $journal?->sleep_time ? substr($journal->sleep_time, 0, 5) : '21:00') }}"
                                            required
                                        >
                                        @error('sleep_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white p-4 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Semua tanda (<span class="text-danger">*</span>) wajib diisi.</span>
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        Simpan Jurnal ({{ \Carbon\Carbon::parse($selectedDateString)->locale('id')->isoFormat('D MMM Y') }})
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
