<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\SchoolProfile;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeacherDirectoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q')), 0, 100);
        $field = mb_substr(trim((string) $request->query('bidang')), 0, 100);
        $visibleTeachers = Teacher::query()
            ->where('is_active', true)
            ->where('is_public', true);

        $fields = (clone $visibleTeachers)
            ->whereNotNull('public_title')
            ->where('public_title', '!=', '')
            ->distinct()
            ->orderBy('public_title')
            ->pluck('public_title');

        $teachers = (clone $visibleTeachers)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('public_title', 'like', "%{$search}%");
                });
            })
            ->when($field !== '', fn ($query) => $query->where('public_title', $field))
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        return view('public.teachers.index', [
            'schoolProfile' => SchoolProfile::query()->first(),
            'teachers' => $teachers,
            'visibleTeacherCount' => $visibleTeachers->count(),
            'fields' => $fields,
            'search' => $search,
            'field' => $field,
            'hasFilters' => $search !== '' || $field !== '',
        ]);
    }
}
