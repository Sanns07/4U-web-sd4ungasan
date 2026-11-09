<?php

namespace Tests\Feature\Admin;

use App\Enums\DayOfWeek;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingAssignmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_unused_assignment(): void
    {
        [$admin, $class, $teacher, $subject] = $this->context();

        $this->actingAs($admin)->post('/admin/penugasan', $this->data($class, $teacher, $subject))
            ->assertRedirect('/admin/penugasan')
            ->assertSessionHas('success');
        $assignment = TeachingAssignment::query()->firstOrFail();

        $this->actingAs($admin)->put("/admin/penugasan/{$assignment->id}", $this->data($class, $teacher, $subject, ['is_active' => '0']))
            ->assertSessionDoesntHaveErrors();
        $this->assertFalse($assignment->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/penugasan/{$assignment->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('teaching_assignments', ['id' => $assignment->id]);
    }

    public function test_duplicate_assignment_is_rejected(): void
    {
        [$admin, $class, $teacher, $subject] = $this->context();
        TeachingAssignment::query()->create($this->data($class, $teacher, $subject, ['is_active' => true]));

        $this->actingAs($admin)->post('/admin/penugasan', $this->data($class, $teacher, $subject))
            ->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseCount('teaching_assignments', 1);
    }

    public function test_scheduled_assignment_cannot_change_identity_or_be_deleted(): void
    {
        [$admin, $class, $teacher, $subject] = $this->context();
        $otherTeacher = Teacher::factory()->create();
        $assignment = TeachingAssignment::query()->create($this->data($class, $teacher, $subject, ['is_active' => true]));
        Schedule::query()->create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Ruang 1',
        ]);

        $this->actingAs($admin)->put("/admin/penugasan/{$assignment->id}", $this->data($class, $otherTeacher, $subject))
            ->assertSessionHasErrors('teacher_id');
        $this->assertSame($teacher->id, $assignment->fresh()->teacher_id);

        $this->actingAs($admin)->delete("/admin/penugasan/{$assignment->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('teaching_assignments', ['id' => $assignment->id]);
    }

    public function test_explicit_empty_period_filter_lists_all_periods_and_teacher_filter_limits_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $periodA = AcademicPeriod::factory()->create();
        $periodB = AcademicPeriod::factory()->create([
            'academic_year' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2027-12-20',
            'is_active' => false,
        ]);
        $teacherA = Teacher::factory()->create(['name' => 'Guru Filter A']);
        $teacherB = Teacher::factory()->create(['name' => 'Guru Filter B']);
        $subjectA = Subject::factory()->create(['name' => 'Mapel Filter A']);
        $subjectB = Subject::factory()->create(['name' => 'Mapel Filter B']);
        $classA = SchoolClass::factory()->create(['academic_period_id' => $periodA->id]);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $periodB->id]);
        TeachingAssignment::query()->create($this->data($classA, $teacherA, $subjectA, ['is_active' => true]));
        TeachingAssignment::query()->create($this->data($classB, $teacherB, $subjectB, ['is_active' => true]));

        $this->actingAs($admin)->get('/admin/penugasan?period_id=')
            ->assertOk()
            ->assertSee('Mapel Filter A')
            ->assertSee('Mapel Filter B');

        $this->actingAs($admin)->get("/admin/penugasan?period_id=&teacher_id={$teacherA->id}")
            ->assertOk()
            ->assertSee('<strong>Mapel Filter A</strong>', false)
            ->assertDontSee('<strong>Mapel Filter B</strong>', false);
    }

    public function test_edit_form_keeps_inactive_current_relations_and_explains_identity_lock(): void
    {
        [$admin, $class, $teacher, $subject] = $this->context();
        $assignment = TeachingAssignment::query()->create($this->data($class, $teacher, $subject, ['is_active' => true]));
        Schedule::query()->create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Ruang 1',
        ]);
        $teacher->update(['is_active' => false]);
        $subject->update(['is_active' => false]);
        $class->update(['is_active' => false]);

        $this->actingAs($admin)->get("/admin/penugasan/{$assignment->id}/edit")
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee($subject->name)
            ->assertSee($class->name)
            ->assertSee('dikunci untuk menjaga histori');

        $this->actingAs($admin)->put("/admin/penugasan/{$assignment->id}", $this->data(
            $class,
            $teacher,
            $subject,
            ['is_active' => '0'],
        ))->assertSessionDoesntHaveErrors();
        $this->assertFalse($assignment->fresh()->is_active);
    }

    private function context(): array
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();

        return [
            $admin,
            SchoolClass::factory()->create(['academic_period_id' => $period->id]),
            Teacher::factory()->create(),
            Subject::factory()->create(),
        ];
    }

    private function data(SchoolClass $class, Teacher $teacher, Subject $subject, array $overrides = []): array
    {
        return array_merge([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'is_active' => '1',
        ], $overrides);
    }
}
