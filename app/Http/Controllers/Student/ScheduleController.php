<?php

namespace App\Http\Controllers\Student;

use App\Enums\DayOfWeek;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolProfile;
use App\Models\StudentEnrollment;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;
        $schoolProfile = SchoolProfile::query()->first();

        if (! $student) {
            return $this->viewFor('no-profile', compact('schoolProfile'));
        }

        $period = AcademicPeriod::query()->active()->latest('start_date')->first();

        if (! $period) {
            return $this->viewFor('no-period', compact('student', 'schoolProfile'));
        }

        $enrollment = StudentEnrollment::query()
            ->with('schoolClass.homeroomTeacher')
            ->where('student_id', $student->id)
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('schoolClass', fn ($query) => $query
                ->where('academic_period_id', $period->id)
                ->where('is_active', true))
            ->first();

        if (! $enrollment) {
            return $this->viewFor('no-class', compact('student', 'period', 'schoolProfile'));
        }

        $schoolClass = $enrollment->schoolClass;
        $teachingAssignments = TeachingAssignment::query()
            ->with(['subject', 'teacher'])
            ->where('class_id', $schoolClass->id)
            ->where('is_active', true)
            ->whereHas('subject', fn ($query) => $query->where('is_active', true))
            ->whereHas('teacher', fn ($query) => $query->where('is_active', true))
            ->orderBy('subject_id')
            ->get()
            ->sortBy(fn (TeachingAssignment $assignment): string => $assignment->subject->name)
            ->values();

        $schedules = Schedule::query()
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher'])
            ->whereHas('teachingAssignment', fn ($query) => $query
                ->where('class_id', $schoolClass->id)
                ->where('is_active', true)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('is_active', true))
                ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('is_active', true)))
            ->get();

        $schedulesByDay = collect(DayOfWeek::cases())->mapWithKeys(
            fn (DayOfWeek $day): array => [
                $day->value => $schedules
                    ->filter(fn (Schedule $schedule): bool => $schedule->day_of_week === $day)
                    ->sortBy('start_time')
                    ->values(),
            ],
        );

        return $this->viewFor($schedules->isEmpty() ? 'no-schedule' : 'ready', compact(
            'student', 'period', 'schoolClass', 'teachingAssignments', 'schedulesByDay', 'schoolProfile',
        ));
    }

    private function viewFor(string $state, array $data = []): View
    {
        return view('student.schedule', array_merge([
            'state' => $state,
            'days' => DayOfWeek::cases(),
            'schedulesByDay' => new Collection,
            'teachingAssignments' => new Collection,
            'schoolProfile' => null,
        ], $data));
    }
}
