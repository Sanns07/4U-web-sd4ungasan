<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeachingAssignmentRequest;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $periods = AcademicPeriod::query()->orderByDesc('start_date')->get();
        $periodFilterProvided = array_key_exists('period_id', $request->query());
        $selectedPeriodId = $periodFilterProvided
            ? ($request->filled('period_id') ? $request->integer('period_id') : null)
            : $periods->firstWhere('is_active', true)?->id;
        $assignments = TeachingAssignment::query()
            ->with(['teacher', 'subject', 'schoolClass.academicPeriod'])
            ->withCount('schedules')
            ->when($selectedPeriodId, fn (Builder $query) => $query->whereHas('schoolClass', fn (Builder $classQuery) => $classQuery->where('academic_period_id', $selectedPeriodId)))
            ->when($request->integer('class_id'), fn (Builder $query, int $id) => $query->where('class_id', $id))
            ->when($request->integer('teacher_id'), fn (Builder $query, int $id) => $query->where('teacher_id', $id))
            ->when($request->integer('subject_id'), fn (Builder $query, int $id) => $query->where('subject_id', $id))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assignments.index', [
            'assignments' => $assignments,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'classes' => SchoolClass::query()->when($selectedPeriodId, fn (Builder $query) => $query->where('academic_period_id', $selectedPeriodId))->orderBy('name')->get(),
            'teachers' => Teacher::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new TeachingAssignment);
    }

    public function store(TeachingAssignmentRequest $request): RedirectResponse
    {
        $assignment = TeachingAssignment::query()->create($request->validated());
        Log::info('Penugasan mengajar dibuat.', ['admin_id' => $request->user()->id, 'assignment_id' => $assignment->id]);

        return to_route('admin.assignments.index')->with('success', 'Penugasan mengajar berhasil dibuat.');
    }

    public function edit(TeachingAssignment $teachingAssignment): View
    {
        return $this->formView($teachingAssignment);
    }

    public function update(TeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $teachingAssignment->update($request->validated());
        Log::info('Penugasan mengajar diperbarui.', ['admin_id' => $request->user()->id, 'assignment_id' => $teachingAssignment->id]);

        return to_route('admin.assignments.index')->with('success', 'Penugasan mengajar berhasil diperbarui.');
    }

    public function destroy(Request $request, TeachingAssignment $teachingAssignment): RedirectResponse
    {
        if ($teachingAssignment->schedules()->exists()) {
            return back()->with('error', 'Penugasan tidak dapat dihapus karena sudah dipakai pada jadwal. Nonaktifkan penugasan sebagai gantinya.');
        }

        $teachingAssignment->delete();
        Log::info('Penugasan mengajar dihapus.', ['admin_id' => $request->user()->id, 'assignment_id' => $teachingAssignment->id]);

        return to_route('admin.assignments.index')->with('success', 'Penugasan mengajar berhasil dihapus.');
    }

    private function formView(TeachingAssignment $teachingAssignment): View
    {
        return view('admin.assignments.form', [
            'teachingAssignment' => $teachingAssignment,
            'identityLocked' => $teachingAssignment->exists && $teachingAssignment->schedules()->exists(),
            'classes' => SchoolClass::query()->with('academicPeriod')->where('is_active', true)
                ->when($teachingAssignment->class_id, fn (Builder $query) => $query->orWhereKey($teachingAssignment->class_id))
                ->orderBy('name')->get(),
            'teachers' => Teacher::query()->where('is_active', true)
                ->when($teachingAssignment->teacher_id, fn (Builder $query) => $query->orWhereKey($teachingAssignment->teacher_id))
                ->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)
                ->when($teachingAssignment->subject_id, fn (Builder $query) => $query->orWhereKey($teachingAssignment->subject_id))
                ->orderBy('name')->get(),
        ]);
    }
}
