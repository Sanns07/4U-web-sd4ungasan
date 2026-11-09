<?php

namespace Tests\Feature\Admin;

use App\Enums\Gender;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_toggle_and_safely_delete_teacher(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/guru', $this->validData())
            ->assertRedirect('/admin/guru')
            ->assertSessionHas('success');

        $teacher = Teacher::query()->where('nip', '198811112020121001')->firstOrFail();
        $this->actingAs($admin)->put("/admin/guru/{$teacher->id}", $this->validData(['name' => 'Ni Luh Sari, M.Pd.']))->assertSessionHas('success');
        $this->assertSame('Ni Luh Sari, M.Pd.', $teacher->fresh()->name);

        $this->actingAs($admin)->patch("/admin/guru/{$teacher->id}/status")->assertSessionHas('success');
        $this->assertFalse($teacher->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/guru/{$teacher->id}")->assertSessionHas('success');
        $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
    }

    public function test_referenced_teacher_cannot_be_deleted_and_is_not_a_login_account(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create();
        SchoolClass::factory()->create(['homeroom_teacher_id' => $teacher->id]);

        $this->actingAs($admin)->delete("/admin/guru/{$teacher->id}")
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'tidak dapat dihapus'));

        $this->assertNotSoftDeleted('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['username' => $teacher->nip]);
    }

    public function test_teacher_photo_rejects_non_image_file(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/admin/guru/create')->post('/admin/guru', $this->validData([
            'photo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]))->assertRedirect('/admin/guru/create')
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('teachers', ['nip' => '198811112020121001']);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'nip' => '198811112020121001',
            'name' => 'Ni Luh Sari, S.Pd.',
            'gender' => Gender::Female->value,
            'birth_place' => 'Denpasar',
            'birth_date' => '1988-11-11',
            'email' => 'sari@example.test',
            'phone' => '081234567890',
            'address' => 'Denpasar',
            'public_title' => 'Guru Ekonomi',
            'is_active' => '1',
            'is_public' => '1',
        ], $overrides);
    }
}
