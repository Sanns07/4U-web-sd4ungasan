<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Post;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'activePeriod' => AcademicPeriod::query()->active()->latest('start_date')->first(),
            'stats' => [
                'students' => Student::query()->where('status', StudentStatus::Active)->count(),
                'teachers' => Teacher::query()->where('is_active', true)->count(),
                'classes' => SchoolClass::query()->where('is_active', true)->count(),
                'posts' => Post::query()->where('status', PostStatus::Published)->count(),
            ],
        ]);
    }
}
