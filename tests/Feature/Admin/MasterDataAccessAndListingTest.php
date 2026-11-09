<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataAccessAndListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_master_data_pages_and_student_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $paths = [
            '/admin/profil-sekolah',
            '/admin/organisasi', '/admin/organisasi/create',
            '/admin/guru', '/admin/guru/create',
            '/admin/siswa', '/admin/siswa/create',
            '/admin/periode', '/admin/periode/create',
            '/admin/mapel', '/admin/mapel/create',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
            $this->actingAs($student)->get($path)->assertForbidden();
        }

        $this->actingAs($student)->post('/admin/mapel', [
            'code' => 'TOLAK',
            'name' => 'Tidak Boleh Dibuat',
            'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing('subjects', ['code' => 'TOLAK']);
    }

    public function test_teacher_search_filters_and_pagination_work(): void
    {
        $admin = User::factory()->admin()->create();
        Teacher::factory()->create(['name' => 'Guru Target', 'is_active' => false, 'is_public' => false]);
        Teacher::factory()->count(21)->create();

        $this->actingAs($admin)->get('/admin/guru?q=Guru+Target&status=inactive&public=hidden')
            ->assertOk()->assertSee('Guru Target')->assertSee('1 guru ditemukan');

        $this->actingAs($admin)->get('/admin/guru?page=2')
            ->assertOk()->assertSee('pagination');
    }

    public function test_subject_search_filter_and_empty_state_work(): void
    {
        $admin = User::factory()->admin()->create();
        Subject::factory()->create(['code' => 'TARGET', 'name' => 'Mapel Target', 'is_active' => false]);

        $this->actingAs($admin)->get('/admin/mapel?q=TARGET&status=inactive')
            ->assertOk()->assertSee('Mapel Target')->assertSee('1 mata pelajaran');

        $this->actingAs($admin)->get('/admin/mapel?q=tidak-ada-sama-sekali')
            ->assertOk()->assertSee('Mata pelajaran tidak ditemukan.');
    }
}
