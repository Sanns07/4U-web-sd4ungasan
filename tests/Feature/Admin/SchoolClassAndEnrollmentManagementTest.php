<?php

namespace Tests\Feature\Admin;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolClassAndEnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_unused_class(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($admin)->post('/admin/kelas', $this->classData($period, $teacher))
            ->assertRedirect()
            ->assertSessionHas('success');

        $class = SchoolClass::query()->where('name', 'X IPA 1')->firstOrFail();
        $this->actingAs($admin)->put("/admin/kelas/{$class->id}", $this->classData($period, $teacher, ['name' => 'X IPA 2']))
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('X IPA 2', $class->fresh()->name);

        $this->actingAs($admin)->delete("/admin/kelas/{$class->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_duplicate_class_and_period_change_for_referenced_class_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $otherPeriod = AcademicPeriod::factory()->create([
            'academic_year' => '2027/2028',
            'is_active' => false,
        ]);
        $teacher = Teacher::factory()->create();
        $class = SchoolClass::factory()->create([
            'academic_period_id' => $period->id,
            'name' => 'X IPA 1',
        ]);
        $student = Student::factory()->create();
        StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => $period->start_date,
            'status' => EnrollmentStatus::Active,
        ]);

        $this->actingAs($admin)->post('/admin/kelas', $this->classData($period, $teacher))
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->put("/admin/kelas/{$class->id}", $this->classData($otherPeriod, $teacher))
            ->assertSessionHasErrors('academic_period_id');

        $this->actingAs($admin)->delete("/admin/kelas/{$class->id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }

    public function test_bulk_placement_is_atomic_and_student_can_only_have_one_active_class_per_period(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $classA = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI A']);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI B']);
        $students = Student::factory()->count(3)->create();

        $this->actingAs($admin)->post("/admin/kelas/{$classA->id}/siswa", [
            'student_ids' => [$students[0]->id, $students[1]->id],
            'enrolled_at' => '2026-07-15',
        ])->assertSessionHas('success');
        $this->assertDatabaseCount('student_enrollments', 2);

        $this->actingAs($admin)->post("/admin/kelas/{$classB->id}/siswa", [
            'student_ids' => [$students[2]->id, $students[0]->id],
            'enrolled_at' => '2026-07-16',
        ])->assertSessionHasErrors('student_ids');

        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $students[2]->id,
            'class_id' => $classB->id,
        ]);
        $this->assertDatabaseCount('student_enrollments', 2);
    }

    public function test_admin_can_transfer_end_and_reenroll_student_without_losing_history(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $classA = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI A']);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $period->id, 'name' => 'XI B']);
        $student = Student::factory()->create();
        $first = StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $classA->id,
            'enrolled_at' => '2026-07-15',
            'status' => EnrollmentStatus::Active,
        ]);

        $this->actingAs($admin)->patch("/admin/kelas/{$classA->id}/siswa/{$first->id}/pindah", [
            'target_class_id' => $classB->id,
            'enrolled_at' => '2026-08-01',
        ])->assertSessionHas('success');

        $second = StudentEnrollment::query()->where('class_id', $classB->id)->firstOrFail();
        $this->assertSame(EnrollmentStatus::Transferred, $first->fresh()->status);

        $this->actingAs($admin)->patch("/admin/kelas/{$classB->id}/siswa/{$second->id}/pindah", [
            'target_class_id' => $classA->id,
            'enrolled_at' => '2026-08-15',
        ])->assertSessionHas('success');

        $active = StudentEnrollment::query()->where('student_id', $student->id)->where('status', EnrollmentStatus::Active)->firstOrFail();
        $this->assertSame($classA->id, $active->class_id);
        $this->assertDatabaseCount('student_enrollments', 3);

        $this->actingAs($admin)->delete("/admin/kelas/{$classA->id}/siswa/{$active->id}", [
            'ended_at' => '2026-09-01',
        ])->assertSessionHas('success');
        $this->assertSame(EnrollmentStatus::Completed, $active->fresh()->status);
    }

    public function test_placement_and_end_dates_must_remain_inside_period(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $student = Student::factory()->create();

        $this->actingAs($admin)->post("/admin/kelas/{$class->id}/siswa", [
            'student_ids' => [$student->id],
            'enrolled_at' => '2027-01-01',
        ])->assertSessionHasErrors('enrolled_at');

        $enrollment = StudentEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-15',
            'status' => EnrollmentStatus::Active,
        ]);
        $this->actingAs($admin)->delete("/admin/kelas/{$class->id}/siswa/{$enrollment->id}", [
            'ended_at' => '2027-01-01',
        ])->assertSessionHasErrors('ended_at');
        $this->assertSame(EnrollmentStatus::Active, $enrollment->fresh()->status);
    }

    public function test_class_detail_renders_bounded_date_inputs_and_preserved_enrollment_history(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $activeStudent = Student::factory()->create();
        $historyStudent = Student::factory()->create();
        StudentEnrollment::query()->create([
            'student_id' => $activeStudent->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-20',
            'status' => EnrollmentStatus::Active,
        ]);
        StudentEnrollment::query()->create([
            'student_id' => $historyStudent->id,
            'class_id' => $class->id,
            'enrolled_at' => '2026-07-15',
            'ended_at' => '2026-08-01',
            'status' => EnrollmentStatus::Transferred,
        ]);

        $this->actingAs($admin)->get("/admin/kelas/{$class->id}")
            ->assertOk()
            ->assertSee('Riwayat kelas')
            ->assertSee($historyStudent->name)
            ->assertSee('name="enrolled_at"', false)
            ->assertSee('name="ended_at"', false)
            ->assertSee('min="2026-07-20"', false)
            ->assertSee('max="2026-12-19"', false);
    }

    private function classData(AcademicPeriod $period, Teacher $teacher, array $overrides = []): array
    {
        return array_merge([
            'academic_period_id' => $period->id,
            'name' => 'X IPA 1',
            'grade_level' => 10,
            'room' => 'Ruang X-1',
            'homeroom_teacher_id' => $teacher->id,
            'is_active' => '1',
        ], $overrides);
    }
}
