<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolClassRequest;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $periods = AcademicPeriod::query()->orderByDesc('start_date')->get();
        $selectedPeriodId = $request->integer('period_id') ?: $periods->firstWhere('is_active', true)?->id;
        $classes = SchoolClass::query()
            ->with(['academicPeriod', 'homeroomTeacher'])
            ->withCount(['activeEnrollments'])
            ->when($selectedPeriodId, fn (Builder $query) => $query->where('academic_period_id', $selectedPeriodId))
            ->when($request->string('q')->trim()->isNotEmpty(), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('name', 'like', $term)
                        ->orWhere('room', 'like', $term)
                        ->orWhereHas('homeroomTeacher', fn (Builder $teacherQuery) => $teacherQuery->where('name', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'periods', 'selectedPeriodId'));
    }

    public function create(): View
    {
        return $this->formView(new SchoolClass);
    }

    public function store(SchoolClassRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::query()->create($request->validated());
        Log::info('Kelas dibuat.', ['admin_id' => $request->user()->id, 'class_id' => $schoolClass->id]);

        return to_route('admin.classes.show', $schoolClass)->with('success', 'Kelas berhasil dibuat.');
    }

    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load(['academicPeriod', 'homeroomTeacher']);
        $enrollments = $schoolClass->enrollments()
            ->with(['student.user'])
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('student', fn (Builder $query) => $query->whereNull('deleted_at'))
            ->latest('enrolled_at')
            ->paginate(20, ['*'], 'students')
            ->withQueryString();
        $enrollmentHistory = $schoolClass->enrollments()
            ->with(['student.user'])
            ->where('status', '!=', EnrollmentStatus::Active)
            ->whereHas('student', fn (Builder $query) => $query->whereNull('deleted_at'))
            ->latest('ended_at')
            ->latest('enrolled_at')
            ->paginate(10, ['*'], 'history')
            ->withQueryString();
        $availableStudents = Student::query()
            ->where('status', StudentStatus::Active)
            ->whereDoesntHave('enrollments', fn (Builder $query) => $query
                ->where('status', EnrollmentStatus::Active)
                ->whereHas('schoolClass', fn (Builder $classQuery) => $classQuery
                    ->where('academic_period_id', $schoolClass->academic_period_id)))
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
        $targetClasses = SchoolClass::query()
            ->where('academic_period_id', $schoolClass->academic_period_id)
            ->where('is_active', true)
            ->whereKeyNot($schoolClass->id)
            ->orderBy('name')
            ->get();

        return view('admin.classes.show', compact(
            'schoolClass', 'enrollments', 'enrollmentHistory', 'availableStudents', 'targetClasses',
        ));
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return $this->formView($schoolClass);
    }

    public function update(SchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());
        Log::info('Kelas diperbarui.', ['admin_id' => $request->user()->id, 'class_id' => $schoolClass->id]);

        return to_route('admin.classes.show', $schoolClass)->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->enrollments()->exists() || $schoolClass->teachingAssignments()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena memiliki riwayat penempatan atau penugasan. Nonaktifkan kelas sebagai gantinya.');
        }

        $schoolClass->delete();
        Log::info('Kelas dihapus.', ['admin_id' => $request->user()->id, 'class_id' => $schoolClass->id]);

        return to_route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }

    private function formView(SchoolClass $schoolClass): View
    {
        return view('admin.classes.form', [
            'schoolClass' => $schoolClass,
            'periods' => AcademicPeriod::query()->orderByDesc('start_date')->get(),
            'teachers' => Teacher::query()
                ->where('is_active', true)
                ->when($schoolClass->homeroom_teacher_id, fn (Builder $query) => $query->orWhereKey($schoolClass->homeroom_teacher_id))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
