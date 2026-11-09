<?php

namespace Tests\Feature\Admin;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_student_and_student_account_atomically(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/siswa', $this->validData())
            ->assertRedirect('/admin/siswa')
            ->assertSessionHas('success');

        $student = Student::query()->where('nisn', '0011223344')->firstOrFail();
        $this->assertSame(UserRole::Student, $student->user->role);
        $this->assertTrue(Hash::check('Rahasia!2026', $student->user->password));
        $this->assertDatabaseHas('students', ['user_id' => $student->user_id, 'name' => 'Kadek Putri']);
    }

    public function test_admin_creates_account_student_and_active_class_enrollment_atomically(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);

        $this->actingAs($admin)->post('/admin/siswa', $this->validData([
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-20',
        ]))->assertSessionDoesntHaveErrors();

        $student = Student::query()->where('nisn', '0011223344')->firstOrFail();
        $enrollment = $student->enrollments()->firstOrFail();
        $this->assertSame($class->id, $enrollment->class_id);
        $this->assertSame('2026-07-20', $enrollment->enrolled_at->format('Y-m-d'));
        $this->assertSame('aktif', $enrollment->status->value);
    }

    public function test_failed_enrollment_rolls_back_new_account_and_student(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);

        $this->actingAs($admin)->post('/admin/siswa', $this->validData([
            'class_id' => $class->id,
            'enrolled_at' => '2027-01-01',
        ]))->assertSessionHasErrors('enrolled_at');

        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseCount('student_enrollments', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_editing_class_in_same_period_transfers_enrollment_and_preserves_history(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $classA = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI A']);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI B']);
        $student = Student::factory()->create();
        $first = StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $classA->id,
            'enrolled_at' => '2026-07-20',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'password' => '',
            'password_confirmation' => '',
            'class_id' => $classB->id,
            'enrolled_at' => '2026-08-15',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertSame('pindah', $first->fresh()->status->value);
        $this->assertSame('2026-08-15', $first->fresh()->ended_at->format('Y-m-d'));
        $activeEnrollment = $student->enrollments()->where('status', 'aktif')->firstOrFail();
        $this->assertSame($classB->id, $activeEnrollment->class_id);
        $this->assertSame('2026-08-15', $activeEnrollment->enrolled_at->format('Y-m-d'));
        $this->assertDatabaseCount('student_enrollments', 2);
    }

    public function test_failed_transfer_rolls_back_student_and_account_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $classA = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $student = Student::factory()->create(['name' => 'Nama Lama']);
        StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $classA->id,
            'enrolled_at' => '2026-07-20',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'name' => 'Nama Tidak Boleh Tersimpan',
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => 'username.baru',
            'password' => '',
            'password_confirmation' => '',
            'class_id' => $classB->id,
            'enrolled_at' => '2026-07-01',
        ]))->assertSessionHasErrors('enrolled_at');

        $this->assertSame('Nama Lama', $student->fresh()->name);
        $this->assertNotSame('username.baru', $student->user->fresh()->username);
        $this->assertDatabaseCount('student_enrollments', 1);
        $this->assertSame($classA->id, $student->enrollments()->firstOrFail()->class_id);
    }

    public function test_edit_without_password_preserves_old_hash_and_new_password_is_hashed(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $student->user->update(['password' => 'PasswordLama!']);
        $oldHash = $student->user->fresh()->password;

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'name' => 'Nama Diperbarui',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertSame($oldHash, $student->user->fresh()->password);
        $this->assertSame('Nama Diperbarui', $student->fresh()->name);

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'password' => 'PasswordBaru!2026',
            'password_confirmation' => 'PasswordBaru!2026',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertNotSame('PasswordBaru!2026', $student->user->fresh()->password);
        $this->assertTrue(Hash::check('PasswordBaru!2026', $student->user->fresh()->password));
    }

    public function test_admin_can_deactivate_student_account_and_archived_student_stays_inactive(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $enrollment = StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-20',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->patch("/admin/siswa/{$student->id}/akun")->assertSessionHas('success');
        $this->assertFalse($student->user->fresh()->is_active);

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'status' => StudentStatus::Archived->value,
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertFalse($student->user->fresh()->is_active);
        $this->assertSame(StudentStatus::Archived, $student->fresh()->status);
        $this->assertSame('selesai', $enrollment->fresh()->status->value);

        $this->actingAs($admin)->patch("/admin/siswa/{$student->id}/akun")
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'berstatus aktif'));

        $this->assertFalse($student->user->fresh()->is_active);
        $this->assertSame(StudentStatus::Archived, $student->fresh()->status);
    }

    public function test_alumni_or_archived_status_deactivates_account_and_safely_closes_active_enrollments(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $student = Student::factory()->create();
        $enrollment = StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-20',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'status' => StudentStatus::Alumni->value,
            'graduation_year' => 2026,
            'class_id' => $class->id,
            'enrolled_at' => '2030-01-01',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertFalse($student->user->fresh()->is_active);
        $this->assertSame('selesai', $enrollment->fresh()->status->value);
        $this->assertSame($period->end_date->format('Y-m-d'), $enrollment->fresh()->ended_at->format('Y-m-d'));
    }

    public function test_student_list_only_shows_active_enrollment_from_active_period(): void
    {
        $admin = User::factory()->admin()->create();
        $activePeriod = AcademicPeriod::factory()->create();
        $oldPeriod = AcademicPeriod::factory()->create([
            'academic_year' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-20',
            'is_active' => false,
        ]);
        $activeClass = SchoolClass::factory()->create(['academic_period_id' => $activePeriod->id, 'name' => 'XI AKTIF']);
        $oldClass = SchoolClass::factory()->create(['academic_period_id' => $oldPeriod->id, 'name' => 'X LAMA']);
        $student = Student::factory()->create();
        StudentEnrollment::query()->create(['student_id' => $student->id, 'class_id' => $oldClass->id, 'enrolled_at' => '2025-07-15', 'status' => 'aktif']);
        StudentEnrollment::query()->create(['student_id' => $student->id, 'class_id' => $activeClass->id, 'enrolled_at' => '2026-07-15', 'status' => 'aktif']);

        $this->actingAs($admin)->get('/admin/siswa')
            ->assertOk()
            ->assertSee('XI AKTIF')
            ->assertDontSee('X LAMA');
    }

    public function test_edit_form_preserves_current_class_that_was_deactivated(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create([
            'academic_period_id' => $period->id,
            'name' => 'XI NONAKTIF',
            'is_active' => false,
        ]);
        $student = Student::factory()->create();
        StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-20',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->get("/admin/siswa/{$student->id}/edit")
            ->assertOk()
            ->assertSee('XI NONAKTIF');

        $this->actingAs($admin)->put("/admin/siswa/{$student->id}", $this->validData([
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'username' => $student->user->username,
            'password' => '',
            'password_confirmation' => '',
            'class_id' => $class->id,
            'enrolled_at' => '2026-09-01',
        ]))->assertSessionDoesntHaveErrors();

        $this->assertSame('aktif', $student->enrollments()->firstOrFail()->status->value);
    }

    public function test_duplicate_nisn_and_username_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = Student::factory()->create(['nisn' => '0011223344']);

        $this->actingAs($admin)->post('/admin/siswa', $this->validData(['username' => $existing->user->username]))
            ->assertSessionHasErrors(['nisn', 'username']);

        $this->assertDatabaseCount('students', 1);
    }

    public function test_student_with_enrollment_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $class = SchoolClass::factory()->create();
        StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-01',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->delete("/admin/siswa/{$student->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kadek Putri',
            'nisn' => '0011223344',
            'nis' => '260099',
            'gender' => Gender::Female->value,
            'birth_place' => 'Denpasar',
            'birth_date' => '2010-01-12',
            'address' => 'Denpasar',
            'parent_name' => 'Made Ayu',
            'parent_phone' => '081212121212',
            'status' => StudentStatus::Active->value,
            'graduation_year' => null,
            'username' => '0011223344',
            'password' => 'Rahasia!2026',
            'password_confirmation' => 'Rahasia!2026',
            'is_active' => '1',
        ], $overrides);
    }
}
