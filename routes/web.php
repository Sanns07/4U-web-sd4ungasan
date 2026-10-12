<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\DailyHabitJournalController as AdminDailyHabitJournalController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrganizationMemberController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\StudentEnrollmentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\InformationController;
use App\Http\Controllers\PublicSite\ProfileController;
use App\Http\Controllers\PublicSite\TeacherDirectoryController;
use App\Http\Controllers\Student\DailyHabitJournalController as StudentDailyHabitJournalController;
use App\Http\Controllers\Student\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/profil', ProfileController::class)->name('profile');
Route::get('/guru', TeacherDirectoryController::class)->name('teachers.index');
Route::get('/informasi', [InformationController::class, 'index'])->name('information.index');
Route::get('/informasi/{post:slug}', [InformationController::class, 'show'])->name('information.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/admin', DashboardController::class)->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('profil-sekolah', [SchoolProfileController::class, 'edit'])->name('school-profile.edit');
        Route::put('profil-sekolah', [SchoolProfileController::class, 'update'])->name('school-profile.update');

        Route::resource('organisasi', OrganizationMemberController::class)
            ->except('show')
            ->parameters(['organisasi' => 'organization_member'])
            ->names('organization-members');

        Route::get('posts/{post}/preview', [PostController::class, 'preview'])->name('posts.preview');
        Route::patch('posts/{post}/publikasikan', [PostController::class, 'publish'])->name('posts.publish');
        Route::patch('posts/{post}/arsipkan', [PostController::class, 'archive'])->name('posts.archive');
        Route::resource('posts', PostController::class)->except('show');

        Route::patch('guru/{teacher}/status', [TeacherController::class, 'toggleStatus'])->name('teachers.toggle-status');
        Route::resource('guru', TeacherController::class)
            ->parameters(['guru' => 'teacher'])
            ->names('teachers');

        Route::patch('siswa/{student}/akun', [AdminStudentController::class, 'toggleAccount'])->name('students.toggle-account');
        Route::resource('siswa', AdminStudentController::class)
            ->parameters(['siswa' => 'student'])
            ->names('students');

        Route::patch('periode/{period}/aktifkan', [AcademicPeriodController::class, 'activate'])->name('periods.activate');
        Route::patch('periode/{period}/nonaktifkan', [AcademicPeriodController::class, 'deactivate'])->name('periods.deactivate');
        Route::resource('periode', AcademicPeriodController::class)
            ->except('show')
            ->parameters(['periode' => 'period'])
            ->names('periods');

        Route::post('kelas/{school_class}/siswa', [StudentEnrollmentController::class, 'store'])->name('class-enrollments.store');
        Route::patch('kelas/{school_class}/siswa/{enrollment}/pindah', [StudentEnrollmentController::class, 'transfer'])->name('class-enrollments.transfer');
        Route::delete('kelas/{school_class}/siswa/{enrollment}', [StudentEnrollmentController::class, 'destroy'])->name('class-enrollments.destroy');
        Route::resource('kelas', SchoolClassController::class)
            ->parameters(['kelas' => 'school_class'])
            ->names('classes');

        Route::patch('mapel/{subject}/status', [SubjectController::class, 'toggleStatus'])->name('subjects.toggle-status');
        Route::resource('mapel', SubjectController::class)
            ->except('show')
            ->parameters(['mapel' => 'subject'])
            ->names('subjects');

        Route::resource('penugasan', TeachingAssignmentController::class)
            ->except('show')
            ->parameters(['penugasan' => 'teaching_assignment'])
            ->names('assignments');

        Route::resource('jadwal', AdminScheduleController::class)
            ->except('show')
            ->parameters(['jadwal' => 'schedule'])
            ->names('schedules');

        Route::get('jurnal/export', [AdminDailyHabitJournalController::class, 'export'])->name('journal.export');
        Route::get('jurnal', [AdminDailyHabitJournalController::class, 'index'])->name('journal.index');
    });
});

Route::middleware(['auth', 'role:siswa'])->group(function (): void {
    Route::get('/siswa', ScheduleController::class)->name('student.home');
    Route::get('/siswa/jurnal/{date?}', [StudentDailyHabitJournalController::class, 'index'])->name('student.journal.index');
    Route::post('/siswa/jurnal', [StudentDailyHabitJournalController::class, 'store'])->name('student.journal.store');
});
