<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_migrations_and_seed_data_are_repeatable(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseCount('academic_periods', 1);
        $this->assertDatabaseCount('classes', 1);
        $this->assertDatabaseCount('teachers', 6);
        $this->assertDatabaseCount('subjects', 6);
        $this->assertDatabaseCount('teaching_assignments', 6);
        $this->assertDatabaseCount('schedules', 6);
        $this->assertSame(6, Schedule::query()->distinct()->count('day_of_week'));
    }

    public function test_user_roles_are_limited_and_teachers_have_no_login_relation(): void
    {
        $this->assertSame(['admin', 'siswa'], array_column(UserRole::cases(), 'value'));
        $this->assertFalse(Schema::hasColumn('teachers', 'user_id'));

        $teacher = Teacher::factory()->create();

        $this->assertNotInstanceOf(User::class, $teacher);
    }
}
