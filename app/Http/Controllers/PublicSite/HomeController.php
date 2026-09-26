<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\PostStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Post;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestPosts = Post::query()
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.home', [
            'schoolProfile' => SchoolProfile::query()->first(),
            'activePeriod' => AcademicPeriod::query()->where('is_active', true)->first(),
            'latestPosts' => $latestPosts,
            'featuredTeachers' => Teacher::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('name')
                ->limit(2)
                ->get(),
            'statistics' => [
                ['value' => Student::query()->where('status', StudentStatus::Active)->count(), 'label' => 'Siswa aktif'],
                ['value' => Teacher::query()->where('is_active', true)->count(), 'label' => 'Guru & tenaga didik'],
                ['value' => SchoolClass::query()->where('is_active', true)->count(), 'label' => 'Kelas aktif'],
                ['value' => max(0, now()->year - 1987), 'label' => 'Tahun bertumbuh'],
            ],
        ]);
    }
}
