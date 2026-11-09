<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_account_without_student_profile_sees_empty_state(): void
    {
        $student = User::factory()->create();
        Student::query()->where('user_id', $student->id)->delete();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Profil siswa belum terhubung.');
    }

    public function test_student_sees_schedule_for_their_active_class(): void
    {
        $this->seed();
        $student = User::query()->where('username', '0087432195')->firstOrFail();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Alya Safitri')
            ->assertSee('0087432195')
            ->assertSee('XI IPA 1')
            ->assertSee('2026/2027')
            ->assertSee('Denpasar, Bali')
            ->assertSee('Wali kelas.')
            ->assertSee('Guru mata pelajaran.')
            ->assertSee('Matematika Wajib')
            ->assertSee('Ratna Dewi, M.Pd.')
            ->assertSee('Ruang 11')
            ->assertSeeInOrder(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
    }

    public function test_student_sees_empty_state_when_no_active_period_exists(): void
    {
        $this->seed();
        AcademicPeriod::query()->update(['is_active' => false]);
        $student = User::query()->where('username', '0087432195')->firstOrFail();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Periode aktif belum tersedia.');
    }

    public function test_student_sees_empty_state_when_no_active_class_assignment_exists(): void
    {
        $this->seed();
        StudentEnrollment::query()->delete();
        $student = User::query()->where('username', '0087432195')->firstOrFail();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Kelas aktif belum tersedia.');
    }

    public function test_student_sees_empty_state_when_schedule_is_empty(): void
    {
        $this->seed();
        Schedule::query()->delete();
        $student = User::query()->where('username', '0087432195')->firstOrFail();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Jadwal belum tersedia untuk periode ini.')
            ->assertSee('XI IPA 1');
    }

    public function test_student_sees_homeroom_and_subject_teacher_empty_states(): void
    {
        $this->seed();
        $student = User::query()->where('username', '0087432195')->firstOrFail();
        $student->student->activeEnrollment->schoolClass->update(['homeroom_teacher_id' => null]);
        Schedule::query()->delete();
        TeachingAssignment::query()->delete();

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Wali kelas belum tersedia untuk kelas ini.')
            ->assertSee('Guru mata pelajaran belum tersedia untuk kelas ini.');
    }

    public function test_student_page_never_displays_another_students_identity(): void
    {
        $this->seed();
        $student = User::query()->where('username', '0087432195')->firstOrFail();
        Student::factory()->create(['name' => 'Siswa Lain Rahasia', 'nisn' => '9999999999']);

        $this->actingAs($student)->get('/siswa')
            ->assertOk()
            ->assertSee('Alya Safitri')
            ->assertDontSee('Siswa Lain Rahasia')
            ->assertDontSee('9999999999');
    }
}
