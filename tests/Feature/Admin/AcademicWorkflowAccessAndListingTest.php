<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicWorkflowAccessAndListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_all_academic_pages_and_student_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create(['academic_period_id' => $period->id]);
        $assignment = TeachingAssignment::query()->create([
            'teacher_id' => Teacher::factory()->create()->id,
            'subject_id' => Subject::factory()->create()->id,
            'class_id' => $class->id,
            'is_active' => true,
        ]);
        $paths = [
            '/admin/kelas', '/admin/kelas/create', "/admin/kelas/{$class->id}", "/admin/kelas/{$class->id}/edit",
            '/admin/penugasan', '/admin/penugasan/create', "/admin/penugasan/{$assignment->id}/edit",
            '/admin/jadwal', "/admin/jadwal/create?class_id={$class->id}",
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
            $this->actingAs($student)->get($path)->assertForbidden();
        }
    }

    public function test_class_and_assignment_lists_support_filter_and_empty_state(): void
    {
        $admin = User::factory()->admin()->create();
        $period = AcademicPeriod::factory()->create();
        $class = SchoolClass::factory()->create([
            'academic_period_id' => $period->id,
            'name' => 'XI TARGET',
        ]);

        $this->actingAs($admin)->get("/admin/kelas?period_id={$period->id}&q=TARGET")
            ->assertOk()->assertSee('XI TARGET');
        $this->actingAs($admin)->get("/admin/kelas?period_id={$period->id}&q=tidak-ada")
            ->assertOk()->assertSee('Kelas tidak ditemukan.');

        $this->actingAs($admin)->get("/admin/penugasan?period_id={$period->id}&class_id={$class->id}")
            ->assertOk()->assertSee('Penugasan tidak ditemukan.');
        $this->actingAs($admin)->get("/admin/jadwal?period_id={$period->id}&class_id={$class->id}")
            ->assertOk()->assertSee('Jadwal kelas belum tersedia.');
    }
}
