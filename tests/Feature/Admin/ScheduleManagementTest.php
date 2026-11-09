<?php

namespace Tests\Feature\Admin;

use App\Enums\DayOfWeek;
use App\Enums\Semester;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_schedule(): void
    {
        [$admin, $period, $class, $teacher, $assignment] = $this->context();

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($assignment))
            ->assertRedirect()
            ->assertSessionHas('success');
        $schedule = Schedule::query()->firstOrFail();
        $this->assertSame($class->room, $schedule->room);

        $this->actingAs($admin)->put("/admin/jadwal/{$schedule->id}", $this->data($assignment, [
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Lab. Sains',
        ]))->assertSessionDoesntHaveErrors();
        $this->assertSame('Lab. Sains', $schedule->fresh()->room);

        $this->actingAs($admin)->delete("/admin/jadwal/{$schedule->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_invalid_time_and_class_teacher_room_conflicts_are_rejected(): void
    {
        [$admin, $period, $classA, $teacherA, $assignmentA] = $this->context();
        $classB = SchoolClass::factory()->create([
            'academic_period_id' => $period->id,
            'name' => 'XI B',
            'room' => 'Ruang B',
        ]);
        $teacherB = Teacher::factory()->create();
        $subjectB = Subject::factory()->create();
        $assignmentSameClass = $this->assignment($classA, $teacherB, $subjectB);
        $assignmentSameTeacher = $this->assignment($classB, $teacherA, Subject::factory()->create());
        $assignmentSameRoom = $this->assignment($classB, Teacher::factory()->create(), Subject::factory()->create());

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($assignmentA, [
            'start_time' => '09:00',
            'end_time' => '08:00',
        ]))->assertSessionHasErrors('end_time');

        Schedule::query()->create([
            'teaching_assignment_id' => $assignmentA->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Lab Bersama',
        ]);

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($assignmentSameClass, [
            'start_time' => '08:00', 'end_time' => '09:00', 'room' => 'Ruang Lain',
        ]))->assertSessionHasErrors('start_time');

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($assignmentSameTeacher, [
            'start_time' => '08:00', 'end_time' => '09:00', 'room' => 'Ruang Lain',
        ]))->assertSessionHasErrors('start_time');

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($assignmentSameRoom, [
            'start_time' => '08:00', 'end_time' => '09:00', 'room' => 'lab bersama',
        ]))->assertSessionHasErrors('room');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_same_teacher_and_room_are_allowed_in_different_periods(): void
    {
        [$admin, $period, $class, $teacher, $assignment] = $this->context();
        Schedule::query()->create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Lab Bersama',
        ]);
        $otherPeriod = AcademicPeriod::factory()->create([
            'academic_year' => '2027/2028',
            'semester' => Semester::Odd,
            'start_date' => '2027-07-12',
            'end_date' => '2027-12-18',
            'is_active' => false,
        ]);
        $otherClass = SchoolClass::factory()->create([
            'academic_period_id' => $otherPeriod->id,
            'name' => 'XI Tahun Berikutnya',
        ]);
        $otherAssignment = $this->assignment($otherClass, $teacher, Subject::factory()->create());

        $this->actingAs($admin)->post('/admin/jadwal', $this->data($otherAssignment, ['room' => 'Lab Bersama']))
            ->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_explicit_all_periods_and_teacher_filter_render_correct_schedule_rows(): void
    {
        [$admin, $periodA, $classA, $teacherA, $assignmentA] = $this->context();
        $assignmentA->subject->update(['name' => 'Mapel Jadwal A']);
        Schedule::query()->create([
            'teaching_assignment_id' => $assignmentA->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Ruang A',
        ]);
        $periodB = AcademicPeriod::factory()->create([
            'academic_year' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2027-12-20',
            'is_active' => false,
        ]);
        $classB = SchoolClass::factory()->create(['academic_period_id' => $periodB->id]);
        $teacherB = Teacher::factory()->create();
        $subjectB = Subject::factory()->create(['name' => 'Mapel Jadwal B']);
        $assignmentB = $this->assignment($classB, $teacherB, $subjectB);
        Schedule::query()->create([
            'teaching_assignment_id' => $assignmentB->id,
            'day_of_week' => DayOfWeek::Tuesday,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'room' => 'Ruang B',
        ]);

        $this->actingAs($admin)->get('/admin/jadwal?period_id=&class_id=')
            ->assertOk()
            ->assertSee('Mapel Jadwal A')
            ->assertSee('Mapel Jadwal B');

        $this->actingAs($admin)->get("/admin/jadwal?period_id=&class_id=&teacher_id={$teacherA->id}")
            ->assertOk()
            ->assertSee('Mapel Jadwal A')
            ->assertDontSee('Mapel Jadwal B');
    }

    public function test_schedule_edit_keeps_inactive_current_assignment_valid(): void
    {
        [$admin, $period, $class, $teacher, $assignment] = $this->context();
        $schedule = Schedule::query()->create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => DayOfWeek::Monday,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
            'room' => 'Ruang A',
        ]);
        $assignment->update(['is_active' => false]);
        $teacher->update(['is_active' => false]);

        $this->actingAs($admin)->get("/admin/jadwal/{$schedule->id}/edit")
            ->assertOk()
            ->assertSee($teacher->name);

        $this->actingAs($admin)->put("/admin/jadwal/{$schedule->id}", $this->data($assignment, [
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]))->assertSessionDoesntHaveErrors();
        $this->assertSame('09:00', $schedule->fresh()->startTimeLabel());
    }

    private function context(): array
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create([
            'academic_period_id' => $period->id,
            'name' => 'XI A',
            'room' => 'Ruang A',
        ]);
        $teacher = Teacher::factory()->create();
        $assignment = $this->assignment($class, $teacher, Subject::factory()->create());

        return [$admin, $period, $class, $teacher, $assignment];
    }

    private function assignment(SchoolClass $class, Teacher $teacher, Subject $subject): TeachingAssignment
    {
        return TeachingAssignment::query()->create([
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'is_active' => true,
        ]);
    }

    private function data(TeachingAssignment $assignment, array $overrides = []): array
    {
        return array_merge([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => DayOfWeek::Monday->value,
            'start_time' => '07:30',
            'end_time' => '08:50',
            'room' => '',
            'notes' => 'Test jadwal',
        ], $overrides);
    }
}
