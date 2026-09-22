<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->withCount('teachingAssignments')
            ->when($request->string('q')->trim()->isNotEmpty(), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn (Builder $nested) => $nested->where('code', 'like', $term)->orWhere('name', 'like', $term));
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.form', ['subject' => new Subject]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::query()->create($request->validated());
        Log::info('Mata pelajaran dibuat.', ['admin_id' => $request->user()->id, 'subject_id' => $subject->id]);

        return to_route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.form', compact('subject'));
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());
        Log::info('Mata pelajaran diperbarui.', ['admin_id' => $request->user()->id, 'subject_id' => $subject->id]);

        return to_route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update(['is_active' => ! $subject->is_active]);
        Log::info('Status mata pelajaran diubah.', ['admin_id' => $request->user()->id, 'subject_id' => $subject->id, 'is_active' => $subject->is_active]);

        return back()->with('success', $subject->is_active ? 'Mata pelajaran berhasil diaktifkan.' : 'Mata pelajaran berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse
    {
        if ($subject->teachingAssignments()->exists()) {
            return back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan dalam penugasan atau jadwal. Nonaktifkan mata pelajaran sebagai gantinya.');
        }

        $id = $subject->id;
        $subject->delete();
        Log::info('Mata pelajaran dihapus.', ['admin_id' => $request->user()->id, 'subject_id' => $id]);

        return to_route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
