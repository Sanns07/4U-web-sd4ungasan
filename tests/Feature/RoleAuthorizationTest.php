<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_forbidden_from_admin_area(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_admin_is_forbidden_from_student_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/siswa')->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_from_private_pages(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/siswa')->assertRedirect('/login');
    }
}
