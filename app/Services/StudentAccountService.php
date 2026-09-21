<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentAccountService
{
    public function __construct(private readonly StudentEnrollmentService $enrollments) {}

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $accountActive = $this->accountShouldBeActive($data);
            $user = User::query()->create([
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => UserRole::Student,
                'is_active' => $accountActive,
            ]);

            $student = $user->student()->create($this->studentData($data));

            if (! empty($data['class_id'])) {
                $this->enrollments->syncActivePeriod(
                    $student,
                    (int) $data['class_id'],
                    $data['enrolled_at'],
                );
            }

            return $student;
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data): Student {
            $accountData = [
                'username' => $data['username'],
                'is_active' => $this->accountShouldBeActive($data),
            ];

            if (! empty($data['password'])) {
                $accountData['password'] = $data['password'];
            }

            $lockedStudent = Student::query()->with('user')->lockForUpdate()->findOrFail($student->id);
            $lockedStudent->user->update($accountData);
            $lockedStudent->update($this->studentData($data));

            if (($data['status'] ?? null) !== StudentStatus::Active->value) {
                $this->enrollments->completeAllActive($lockedStudent, $data['enrolled_at'] ?? null);
            } else {
                $this->enrollments->syncActivePeriod(
                    $lockedStudent,
                    ! empty($data['class_id']) ? (int) $data['class_id'] : null,
                    $data['enrolled_at'],
                );
            }

            return $lockedStudent->refresh();
        });
    }

    public function setAccountActive(Student $student, bool $active): void
    {
        DB::transaction(function () use ($student, $active): void {
            $student->user->update(['is_active' => $active]);
        });
    }

    private function accountShouldBeActive(array $data): bool
    {
        return ($data['status'] ?? StudentStatus::Active->value) === StudentStatus::Active->value
            && (bool) ($data['is_active'] ?? false);
    }

    private function studentData(array $data): array
    {
        return Arr::only($data, [
            'nisn', 'nis', 'name', 'gender', 'religion', 'birth_place', 'birth_date', 'address',
            'parent_name', 'parent_phone', 'status', 'graduation_year',
        ]);
    }
}
