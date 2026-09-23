<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentEnrollmentService
{
    public function syncActivePeriod(Student $student, ?int $targetClassId, string $effectiveDate): ?StudentEnrollment
    {
        return DB::transaction(function () use ($student, $targetClassId, $effectiveDate): ?StudentEnrollment {
            $lockedStudent = Student::query()->lockForUpdate()->findOrFail($student->id);
            $targetClass = $targetClassId
                ? SchoolClass::query()->with('academicPeriod')->lockForUpdate()->findOrFail($targetClassId)
                : null;
            $periodId = $targetClass?->academic_period_id
                ?? AcademicPeriod::query()->active()->value('id');

            if (! $periodId) {
                return null;
            }

            if ($targetClass && ! $targetClass->academicPeriod->is_active) {
                throw ValidationException::withMessages([
                    'class_id' => 'Kelas harus berasal dari periode akademik aktif.',
                ]);
            }

            $current = $this->activeEnrollmentForPeriod($lockedStudent->id, (int) $periodId);

            if (! $targetClass) {
                return $current ? $this->end($current, $effectiveDate) : null;
            }

            if ($current?->class_id === $targetClass->id) {
                return $current;
            }

            $this->assertClassIsActive($targetClass);

            if ($current) {
                return $this->transfer($current, $targetClass, $effectiveDate);
            }

            return $this->placeMany($targetClass, [$lockedStudent->id], $effectiveDate)->first();
        }, 3);
    }

    public function completeAllActive(Student $student, ?string $effectiveDate = null): void
    {
        DB::transaction(function () use ($student, $effectiveDate): void {
            Student::query()->lockForUpdate()->findOrFail($student->id);
            $activeEnrollments = StudentEnrollment::query()
                ->with('schoolClass.academicPeriod')
                ->where('student_id', $student->id)
                ->where('status', EnrollmentStatus::Active)
                ->lockForUpdate()
                ->get();

            foreach ($activeEnrollments as $enrollment) {
                $candidate = CarbonImmutable::parse($effectiveDate ?: now())->startOfDay();
                $minimum = CarbonImmutable::instance($enrollment->enrolled_at)->startOfDay();
                $maximum = CarbonImmutable::instance($enrollment->schoolClass->academicPeriod->end_date)->startOfDay();
                $safeDate = $candidate->lt($minimum)
                    ? $minimum
                    : ($candidate->gt($maximum) ? $maximum : $candidate);

                $enrollment->update([
                    'status' => EnrollmentStatus::Completed,
                    'ended_at' => $safeDate,
                ]);
            }
        }, 3);
    }

    public function placeMany(SchoolClass $schoolClass, array $studentIds, string $enrolledAt): Collection
    {
        return DB::transaction(function () use ($schoolClass, $studentIds, $enrolledAt): Collection {
            $class = SchoolClass::query()
                ->with('academicPeriod')
                ->lockForUpdate()
                ->findOrFail($schoolClass->id);

            $this->assertClassIsActive($class);
            $date = $this->placementDate($enrolledAt, $class);
            $ids = collect($studentIds)->map(fn ($id): int => (int) $id)->unique()->values();
            $students = Student::query()
                ->whereIn('id', $ids)
                ->where('status', StudentStatus::Active)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($students->count() !== $ids->count()) {
                throw ValidationException::withMessages([
                    'student_ids' => 'Seluruh siswa yang dipilih harus tersedia dan berstatus aktif.',
                ]);
            }

            $created = collect();

            foreach ($ids as $studentId) {
                $current = $this->activeEnrollmentForPeriod($studentId, $class->academic_period_id);

                if ($current) {
                    $message = $current->class_id === $class->id
                        ? "{$students[$studentId]->name} sudah berada di kelas {$class->name}."
                        : "{$students[$studentId]->name} sudah memiliki kelas aktif pada periode ini. Gunakan aksi Pindah.";

                    throw ValidationException::withMessages(['student_ids' => $message]);
                }

                $created->push(StudentEnrollment::query()->create([
                    'student_id' => $studentId,
                    'class_id' => $class->id,
                    'enrolled_at' => $date,
                    'ended_at' => null,
                    'status' => EnrollmentStatus::Active,
                ]));
            }

            return $created;
        }, 3);
    }

    public function transfer(StudentEnrollment $enrollment, SchoolClass $targetClass, string $enrolledAt): StudentEnrollment
    {
        return DB::transaction(function () use ($enrollment, $targetClass, $enrolledAt): StudentEnrollment {
            $current = StudentEnrollment::query()
                ->with(['student', 'schoolClass.academicPeriod'])
                ->lockForUpdate()
                ->findOrFail($enrollment->id);
            Student::query()->lockForUpdate()->findOrFail($current->student_id);

            if ($current->status !== EnrollmentStatus::Active) {
                throw ValidationException::withMessages(['target_class_id' => 'Hanya penempatan aktif yang dapat dipindahkan.']);
            }

            $target = SchoolClass::query()
                ->with('academicPeriod')
                ->lockForUpdate()
                ->findOrFail($targetClass->id);

            $this->assertClassIsActive($target);

            if ($target->id === $current->class_id) {
                throw ValidationException::withMessages(['target_class_id' => 'Kelas tujuan harus berbeda dari kelas saat ini.']);
            }

            if ($target->academic_period_id !== $current->schoolClass->academic_period_id) {
                throw ValidationException::withMessages(['target_class_id' => 'Pemindahan hanya dapat dilakukan antar kelas dalam periode yang sama.']);
            }

            $date = $this->placementDate($enrolledAt, $target);

            if ($date->lt($current->enrolled_at->startOfDay())) {
                throw ValidationException::withMessages(['enrolled_at' => 'Tanggal pindah tidak boleh sebelum tanggal penempatan awal.']);
            }

            $current->update([
                'status' => EnrollmentStatus::Transferred,
                'ended_at' => $date,
            ]);

            return StudentEnrollment::query()->create([
                'student_id' => $current->student_id,
                'class_id' => $target->id,
                'enrolled_at' => $date,
                'ended_at' => null,
                'status' => EnrollmentStatus::Active,
            ]);
        }, 3);
    }

    public function end(StudentEnrollment $enrollment, string $endedAt): StudentEnrollment
    {
        return DB::transaction(function () use ($enrollment, $endedAt): StudentEnrollment {
            $current = StudentEnrollment::query()
                ->with('schoolClass.academicPeriod')
                ->lockForUpdate()
                ->findOrFail($enrollment->id);
            Student::query()->lockForUpdate()->findOrFail($current->student_id);

            if ($current->status !== EnrollmentStatus::Active) {
                throw ValidationException::withMessages(['ended_at' => 'Penempatan ini sudah tidak aktif.']);
            }

            $date = CarbonImmutable::parse($endedAt)->startOfDay();

            if ($date->lt($current->enrolled_at->startOfDay())) {
                throw ValidationException::withMessages(['ended_at' => 'Tanggal selesai tidak boleh sebelum tanggal penempatan.']);
            }

            if ($date->gt($current->schoolClass->academicPeriod->end_date->startOfDay())) {
                throw ValidationException::withMessages(['ended_at' => 'Tanggal selesai harus berada dalam periode kelas.']);
            }

            $current->update([
                'status' => EnrollmentStatus::Completed,
                'ended_at' => $date,
            ]);

            return $current->refresh();
        }, 3);
    }

    private function activeEnrollmentForPeriod(int $studentId, int $periodId): ?StudentEnrollment
    {
        return StudentEnrollment::query()
            ->where('student_id', $studentId)
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('schoolClass', fn ($query) => $query->where('academic_period_id', $periodId))
            ->lockForUpdate()
            ->first();
    }

    private function assertClassIsActive(SchoolClass $schoolClass): void
    {
        if (! $schoolClass->is_active) {
            throw ValidationException::withMessages(['class_id' => 'Penempatan hanya dapat dilakukan pada kelas aktif.']);
        }
    }

    private function placementDate(string $value, SchoolClass $schoolClass): CarbonImmutable
    {
        $date = CarbonImmutable::parse($value)->startOfDay();
        $period = $schoolClass->academicPeriod;

        if ($date->lt($period->start_date->startOfDay()) || $date->gt($period->end_date->startOfDay())) {
            throw ValidationException::withMessages([
                'enrolled_at' => "Tanggal penempatan harus berada dalam rentang periode {$period->academic_year}.",
            ]);
        }

        return $date;
    }
}
