<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_without_default_error(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Tidak dapat masuk');
    }

    public function test_active_admin_can_login_and_is_redirected_to_admin(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        $this->post('/login', ['username' => $admin->username, 'password' => 'secret-password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_active_student_can_login_and_is_redirected_to_schedule(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student, 'password' => 'secret-password']);
        Student::factory()->create(['user_id' => $user->id]);

        $this->post('/login', ['username' => $user->username, 'password' => 'secret-password'])
            ->assertRedirect('/siswa');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected_with_generic_error(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        $this->from('/login')->post('/login', ['username' => $user->username, 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'secret-password']);

        $this->post('/login', ['username' => $user->username, 'password' => 'secret-password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'username' => $user->username,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('username');
        }

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk.',
            session('errors')->first('username'),
        );

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_public_registration_routes_do_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_student_portal_has_no_separate_schedule_route(): void
    {
        $this->get('/siswa/jadwal')->assertNotFound();
    }
}
