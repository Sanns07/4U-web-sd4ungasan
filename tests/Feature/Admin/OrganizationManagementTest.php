<?php

namespace Tests\Feature\Admin;

use App\Enums\OrganizationCategory;
use App\Models\OrganizationMember;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_organization_member(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/organisasi', [
            'teacher_id' => '',
            'name' => 'Made Wijaya',
            'position_name' => 'Ketua Komite',
            'category' => OrganizationCategory::Committee->value,
            'display_order' => 2,
            'is_active' => '1',
        ])->assertRedirect('/admin/organisasi')->assertSessionHas('success');

        $member = OrganizationMember::query()->firstOrFail();
        $this->actingAs($admin)->put("/admin/organisasi/{$member->id}", [
            'teacher_id' => '',
            'name' => 'Made Wijaya',
            'position_name' => 'Ketua Komite Sekolah',
            'category' => OrganizationCategory::Committee->value,
            'display_order' => 1,
            'is_active' => '0',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('organization_members', ['position_name' => 'Ketua Komite Sekolah', 'display_order' => 1, 'is_active' => false]);
        $this->actingAs($admin)->delete("/admin/organisasi/{$member->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('organization_members', ['id' => $member->id]);
    }

    public function test_organization_member_can_reference_teacher_and_requires_manual_name_otherwise(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($admin)->post('/admin/organisasi', [
            'teacher_id' => $teacher->id,
            'name' => '',
            'position_name' => 'Kepala Sekolah',
            'category' => OrganizationCategory::Principal->value,
            'display_order' => 1,
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('organization_members', ['teacher_id' => $teacher->id, 'name' => null]);

        $this->actingAs($admin)->post('/admin/organisasi', [
            'teacher_id' => '',
            'name' => '',
            'position_name' => 'Staf',
            'category' => OrganizationCategory::Administration->value,
            'display_order' => 2,
            'is_active' => '1',
        ])->assertSessionHasErrors('name');
    }
}
