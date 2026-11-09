<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_empty_state_and_save_single_school_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/profil-sekolah')
            ->assertOk()
            ->assertSee('Profil belum tersedia.');

        $this->actingAs($admin)->put('/admin/profil-sekolah', $this->validData())
            ->assertRedirect('/admin/profil-sekolah')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('school_profiles', 1);
        $this->assertDatabaseHas('school_profiles', [
            'school_name' => 'SMA Uji Cendekia',
            'updated_by' => $admin->id,
        ]);

        $profile = SchoolProfile::query()->firstOrFail();
        $this->actingAs($admin)->put('/admin/profil-sekolah', $this->validData(['phone' => '0361-999']))->assertSessionHas('success');
        $this->assertDatabaseCount('school_profiles', 1);
        $this->assertSame('0361-999', $profile->fresh()->phone);
    }

    public function test_school_profile_validation_is_in_indonesian_and_rejects_invalid_data(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/admin/profil-sekolah')->put('/admin/profil-sekolah', [
            'school_name' => '',
            'address' => '',
            'phone' => '',
            'email' => 'bukan-email',
            'vision' => '',
            'mission' => '',
        ])->assertRedirect('/admin/profil-sekolah')
            ->assertSessionHasErrors(['school_name', 'address', 'phone', 'email', 'vision', 'mission']);

        $this->assertDatabaseCount('school_profiles', 0);
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'school_name' => 'SMA Uji Cendekia',
            'address' => 'Jl. Pengujian No. 1',
            'phone' => '0361-123',
            'email' => 'sekolah@example.test',
            'vision' => 'Menjadi sekolah yang unggul.',
            'mission' => 'Mendidik dengan integritas.',
        ], $overrides);
    }
}
