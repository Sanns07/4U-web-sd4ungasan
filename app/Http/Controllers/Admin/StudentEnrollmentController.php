<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EndStudentEnrollmentRequest;
use App\Http\Requests\Admin\StudentPlacementRequest;
use App\Http\Requests\Admin\StudentTransferRequest;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class StudentEnrollmentController extends Controller
{
    public function __construct(private readonly StudentEnrollmentService $enrollments) {}

    public function store(StudentPlacementRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $created = $this->enrollments->placeMany(
            $schoolClass,
            $request->validated('student_ids'),
            $request->validated('enrolled_at'),
        );
        Log::info('Siswa ditempatkan ke kelas.', [
            'admin_id' => $request->user()->id,
            'class_id' => $schoolClass->id,
            'student_ids' => $created->pluck('student_id')->all(),
        ]);

        return to_route('admin.classes.show', $schoolClass)
            ->with('success', $created->count().' siswa berhasil ditempatkan ke kelas.');
    }

    public function transfer(
        StudentTransferRequest $request,
        SchoolClass $schoolClass,
        StudentEnrollment $enrollment,
    ): RedirectResponse {
        if ($enrollment->class_id !== $schoolClass->id) {
            abort(404);
        }

        $targetClass = SchoolClass::query()->findOrFail($request->validated('target_class_id'));
        $newEnrollment = $this->enrollments->transfer($enrollment, $targetClass, $request->validated('enrolled_at'));
        Log::info('Siswa dipindahkan antar kelas.', [
            'admin_id' => $request->user()->id,
            'student_id' => $newEnrollment->student_id,
            'from_class_id' => $schoolClass->id,
            'to_class_id' => $targetClass->id,
        ]);

        return to_route('admin.classes.show', $targetClass)->with('success', 'Siswa berhasil dipindahkan ke kelas tujuan.');
    }

    public function destroy(
        EndStudentEnrollmentRequest $request,
        SchoolClass $schoolClass,
        StudentEnrollment $enrollment,
    ): RedirectResponse {
        if ($enrollment->class_id !== $schoolClass->id) {
            abort(404);
        }

        $this->enrollments->end($enrollment, $request->validated('ended_at'));
        Log::info('Penempatan siswa diselesaikan.', [
            'admin_id' => $request->user()->id,
            'class_id' => $schoolClass->id,
            'student_id' => $enrollment->student_id,
        ]);

        return to_route('admin.classes.show', $schoolClass)->with('success', 'Siswa berhasil dikeluarkan dari kelas tanpa menghapus riwayat.');
    }
}
