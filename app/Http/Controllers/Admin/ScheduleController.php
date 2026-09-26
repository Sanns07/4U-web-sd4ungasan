<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\ScheduleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $schedules) {}

    public function index(Request $request): View
    {
        $periods = AcademicPeriod::query()->orderByDesc('start_date')->get();
        $periodFilterProvided = array_key_exists('period_id', $request->query());
        $selectedPeriodId = $periodFilterProvided
            ? ($request->filled('period_id') ? $request->integer('period_id') : null)
            : $periods->firstWhere('is_active', true)?->id;
        $classes = SchoolClass::query()
            ->when($selectedPeriodId, fn (Builder $query) => $query->where('academic_period_id', $selectedPeriodId))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
        $hasExplicitFilters = collect(['period_id', 'class_id', 'teacher_id'])
            ->contains(fn (string $key): bool => array_key_exists($key, $request->query()));
        $selectedClass = $request->filled('class_id')
            ? $classes->firstWhere('id', $request->integer('class_id'))
            : ($hasExplicitFilters ? null : $classes->first());
        $teachers = Teacher::query()->orderBy('name')->get();
        $selectedTeacher = $request->filled('teacher_id')
            ? $teachers->firstWhere('id', $request->integer('teacher_id'))
            : null;
        $schedules = Schedule::query()
            ->with([
                'teachingAssignment.teacher',
                'teachingAssignment.subject',
                'teachingAssignment.schoolClass.academicPeriod',
            ])
            ->when($selectedPeriodId, fn (Builder $query) => $query->whereHas(
                'teachingAssignment.schoolClass',
                fn (Builder $classQuery) => $classQuery->where('academic_period_id', $selectedPeriodId),
            ))
            ->when($selectedClass, fn (Builder $query) => $query->whereHas(
                'teachingAssignment',
                fn (Builder $assignmentQuery) => $assignmentQuery->where('class_id', $selectedClass->id),
            ))
            ->when($selectedTeacher, fn (Builder $query) => $query->whereHas(
                'teachingAssignment',
                fn (Builder $assignmentQuery) => $assignmentQuery->where('teacher_id', $selectedTeacher->id),
            ))
            ->orderBy('start_time')
            ->get();

        $schedulesByDay = collect(DayOfWeek::cases())->mapWithKeys(fn (DayOfWeek $day): array => [
            $day->value => $schedules->where('day_of_week', $day)->values(),
        ]);

        return view('admin.schedules.index', compact(
            'periods', 'selectedPeriodId', 'classes', 'selectedClass', 'teachers', 'selectedTeacher',
            'schedules', 'schedulesByDay',
        ) + ['days' => DayOfWeek::cases()]);
    }

    public function create(Request $request): View
    {
        $schedule = new Schedule;

        if ($request->integer('assignment_id')) {
            $schedule->teaching_assignment_id = $request->integer('assignment_id');
        }

        return $this->formView($schedule, $request->integer('class_id'));
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        $schedule = $this->schedules->create($request->validated());
        Log::info('Slot jadwal dibuat.', ['admin_id' => $request->user()->id, 'schedule_id' => $schedule->id]);

        return to_route('admin.schedules.index', [
            'period_id' => $schedule->teachingAssignment->schoolClass->academic_period_id,
            'class_id' => $schedule->teachingAssignment->class_id,
        ])->with('success', 'Slot jadwal berhasil dibuat.');
    }

    public function edit(Schedule $schedule): View
    {
        $schedule->load('teachingAssignment');

        return $this->formView($schedule, $schedule->teachingAssignment->class_id);
    }

    public function update(ScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule = $this->schedules->update($schedule, $request->validated());
        Log::info('Slot jadwal diperbarui.', ['admin_id' => $request->user()->id, 'schedule_id' => $schedule->id]);

        return to_route('admin.schedules.index', [
            'period_id' => $schedule->teachingAssignment->schoolClass->academic_period_id,
            'class_id' => $schedule->teachingAssignment->class_id,
        ])->with('success', 'Slot jadwal berhasil diperbarui.');
    }

    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->load('teachingAssignment.schoolClass');
        $periodId = $schedule->teachingAssignment->schoolClass->academic_period_id;
        $classId = $schedule->teachingAssignment->class_id;
        $scheduleId = $schedule->id;
        $schedule->delete();
        Log::info('Slot jadwal dihapus.', ['admin_id' => $request->user()->id, 'schedule_id' => $scheduleId]);

        return to_route('admin.schedules.index', ['period_id' => $periodId, 'class_id' => $classId])
            ->with('success', 'Slot jadwal berhasil dihapus.');
    }

    private function formView(Schedule $schedule, ?int $classId = null): View
    {
        return view('admin.schedules.form', [
            'schedule' => $schedule,
            'days' => DayOfWeek::cases(),
            'assignments' => TeachingAssignment::query()
                ->with(['teacher', 'subject', 'schoolClass.academicPeriod'])
                ->where(function (Builder $query) use ($schedule): void {
                    $query->where(function (Builder $activeQuery): void {
                        $activeQuery->where('is_active', true)
                            ->whereHas('teacher', fn (Builder $teacherQuery) => $teacherQuery->where('is_active', true))
                            ->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('is_active', true))
                            ->whereHas('schoolClass', fn (Builder $classQuery) => $classQuery->where('is_active', true));
                    });

                    if ($schedule->teaching_assignment_id) {
                        $query->orWhereKey($schedule->teaching_assignment_id);
                    }
                })
                ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
                ->orderBy('class_id')
                ->orderBy('subject_id')
                ->get(),
        ]);
    }
}
