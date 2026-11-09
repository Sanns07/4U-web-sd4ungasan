<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_toggle_and_delete_unused_subject(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/mapel', $this->validData())
            ->assertRedirect('/admin/mapel')->assertSessionHas('success');

        $subject = Subject::query()->where('code', 'EKO')->firstOrFail();
        $this->actingAs($admin)->put("/admin/mapel/{$subject->id}", $this->validData(['name' => 'Ekonomi Lanjutan']))->assertSessionHas('success');
        $this->assertSame('Ekonomi Lanjutan', $subject->fresh()->name);

        $this->actingAs($admin)->patch("/admin/mapel/{$subject->id}/status")->assertSessionHas('success');
        $this->assertFalse($subject->fresh()->is_active);
        $this->actingAs($admin)->delete("/admin/mapel/{$subject->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_duplicate_subject_code_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Subject::factory()->create(['code' => 'EKO']);

        $this->actingAs($admin)->post('/admin/mapel', $this->validData(['code' => 'eko']))
            ->assertSessionHasErrors('code');
    }

    public function test_referenced_subject_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $subject = Subject::factory()->create();
        TeachingAssignment::query()->create([
            'teacher_id' => Teacher::factory()->create()->id,
            'subject_id' => $subject->id,
            'class_id' => SchoolClass::factory()->create()->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->delete("/admin/mapel/{$subject->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'EKO',
            'name' => 'Ekonomi',
            'description' => 'Dasar ekonomi.',
            'is_active' => '1',
        ], $overrides);
    }
}
