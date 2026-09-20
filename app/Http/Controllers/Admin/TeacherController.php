<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::query()
            ->withCount(['homeroomClasses', 'teachingAssignments', 'organizationMemberships'])
            ->when($request->string('q')->trim()->isNotEmpty(), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn (Builder $nested) => $nested->where('name', 'like', $term)->orWhere('nip', 'like', $term));
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->input('status') === 'active'))
            ->when($request->filled('public'), fn (Builder $query) => $query->where('is_public', $request->input('public') === 'shown'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('admin.teachers.form', ['teacher' => new Teacher, 'genders' => Gender::cases()]);
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        return $this->persist($request, new Teacher, 'ditambahkan');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->loadCount(['homeroomClasses', 'teachingAssignments', 'organizationMemberships']);

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        return view('admin.teachers.form', ['teacher' => $teacher, 'genders' => Gender::cases()]);
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        return $this->persist($request, $teacher, 'diperbarui');
    }

    public function toggleStatus(Request $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update(['is_active' => ! $teacher->is_active]);
        Log::info('Status guru diubah.', ['admin_id' => $request->user()->id, 'teacher_id' => $teacher->id, 'is_active' => $teacher->is_active]);

        return back()->with('success', $teacher->is_active ? 'Guru berhasil diaktifkan.' : 'Guru berhasil dinonaktifkan. Riwayat akademik tetap tersimpan.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        if ($teacher->homeroomClasses()->exists() || $teacher->teachingAssignments()->exists() || $teacher->organizationMemberships()->exists()) {
            return back()->with('error', 'Guru tidak dapat dihapus karena masih digunakan sebagai wali kelas, anggota organisasi, atau penugasan mengajar. Nonaktifkan guru sebagai gantinya.');
        }

        $photo = $teacher->photo_path;
        $id = $teacher->id;
        $teacher->delete();

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        Log::info('Guru dihapus.', ['admin_id' => $request->user()->id, 'teacher_id' => $id]);

        return to_route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }

    private function persist(TeacherRequest $request, Teacher $teacher, string $action): RedirectResponse
    {
        $oldPhoto = $teacher->photo_path;
        $newPhoto = $request->file('photo')?->store('teachers', 'public');

        try {
            $data = Arr::except($request->validated(), ['photo']);

            if ($newPhoto) {
                $data['photo_path'] = $newPhoto;
            }

            $teacher->fill($data)->save();

            if ($newPhoto && $oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            Log::info("Guru {$action}.", ['admin_id' => $request->user()->id, 'teacher_id' => $teacher->id]);

            return to_route('admin.teachers.index')->with('success', "Data guru berhasil {$action}.");
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            report($exception);

            return back()->withInput()->with('error', 'Data guru belum dapat disimpan. Silakan coba lagi.');
        }
    }
}
