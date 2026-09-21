<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Enums\Religion;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentAccountService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentAccountService $studentAccounts) {}

    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['user', 'activeEnrollment.schoolClass.academicPeriod'])
            ->when($request->string('q')->trim()->isNotEmpty(), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('name', 'like', $term)
                        ->orWhere('nisn', 'like', $term)
                        ->orWhere('nis', 'like', $term)
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('username', 'like', $term));
                });
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                match ($request->input('status')) {
                    'inactive_account' => $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('is_active', false)),
                    'active' => $query->where('status', StudentStatus::Active)->whereHas('user', fn (Builder $userQuery) => $userQuery->where('is_active', true)),
                    'alumni' => $query->where('status', StudentStatus::Alumni),
                    'archived' => $query->where('status', StudentStatus::Archived),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create(): View
    {
        return $this->formView(new Student);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = $this->studentAccounts->create($request->validated());
        Log::info('Siswa dan akun dibuat.', ['admin_id' => $request->user()->id, 'student_id' => $student->id, 'user_id' => $student->user_id]);

        return to_route('admin.students.index')->with('success', 'Data siswa dan akun berhasil dibuat.');
    }

    public function show(Student $student): View
    {
        $student->load(['user', 'enrollments.schoolClass.academicPeriod']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $student->load(['user', 'activeEnrollment.schoolClass.academicPeriod']);

        return $this->formView($student);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->studentAccounts->update($student, $request->validated());
        Log::info('Siswa dan akun diperbarui.', ['admin_id' => $request->user()->id, 'student_id' => $student->id]);

        return to_route('admin.students.index')->with('success', 'Data siswa dan akun berhasil diperbarui.');
    }

    public function toggleAccount(Request $request, Student $student): RedirectResponse
    {
        if (! $student->user->is_active && $student->status !== StudentStatus::Active) {
            return back()->with('error', 'Akun hanya dapat diaktifkan untuk siswa berstatus aktif. Ubah status siswa terlebih dahulu melalui form edit.');
        }

        $this->studentAccounts->setAccountActive($student, ! $student->user->is_active);
        $student->refresh()->load('user');
        Log::info('Status akun siswa diubah.', ['admin_id' => $request->user()->id, 'student_id' => $student->id, 'is_active' => $student->user->is_active]);

        return back()->with('success', $student->user->is_active ? 'Akun siswa berhasil diaktifkan.' : 'Akun siswa berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        if ($student->enrollments()->exists()) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena memiliki riwayat penempatan kelas. Arsipkan siswa dan nonaktifkan akunnya sebagai gantinya.');
        }

        DB::transaction(function () use ($student): void {
            $student->user->update(['is_active' => false]);
            $student->delete();
        });

        Log::info('Siswa dihapus secara aman dan akun dinonaktifkan.', ['admin_id' => $request->user()->id, 'student_id' => $student->id]);

        return to_route('admin.students.index')->with('success', 'Data siswa berhasil dihapus dan akunnya telah dinonaktifkan.');
    }

    private function formView(Student $student): View
    {
        $activePeriod = AcademicPeriod::query()->active()->latest('start_date')->first();
        $currentClassId = $student->activeEnrollment?->class_id;
        $classes = SchoolClass::query()
            ->with('academicPeriod')
            ->where(function (Builder $query) use ($currentClassId): void {
                $query->where('is_active', true);

                if ($currentClassId) {
                    $query->orWhereKey($currentClassId);
                }
            })
            ->when($activePeriod, fn (Builder $query) => $query->where('academic_period_id', $activePeriod->id))
            ->when(! $activePeriod, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('admin.students.form', [
            'student' => $student,
            'genders' => Gender::cases(),
            'religions' => Religion::cases(),
            'statuses' => StudentStatus::cases(),
            'activePeriod' => $activePeriod,
            'classes' => $classes,
        ]);
    }
}
