<?php

namespace Tests\Unit;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentAccountService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_creation_rolls_back_account_when_profile_insert_fails(): void
    {
        Student::factory()->create(['nisn' => '0011223344']);
        $service = app(StudentAccountService::class);

        try {
            $service->create([
                'name' => 'Siswa Rollback',
                'nisn' => '0011223344',
                'nis' => '999001',
                'gender' => Gender::Male->value,
                'birth_place' => null,
                'birth_date' => null,
                'address' => null,
                'parent_name' => null,
                'parent_phone' => null,
                'status' => StudentStatus::Active->value,
                'graduation_year' => null,
                'username' => 'akun.rollback',
                'password' => 'Rahasia!2026',
                'is_active' => true,
            ]);

            $this->fail('Duplikasi NISN seharusnya memicu kegagalan database.');
        } catch (QueryException) {
            $this->assertDatabaseMissing('users', ['username' => 'akun.rollback']);
            $this->assertSame(1, User::query()->whereHas('student')->count());
        }
    }
}
