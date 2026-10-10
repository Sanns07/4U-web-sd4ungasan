<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DailyHabitJournalFilterRequest;
use App\Models\DailyHabitJournal;
use App\Models\SchoolClass;
use App\Services\DailyHabitJournalExportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyHabitJournalController extends Controller
{
    public function __construct(private readonly DailyHabitJournalExportService $exportService) {}

    public function index(DailyHabitJournalFilterRequest $request): View
    {
        $classes = SchoolClass::query()->where('is_active', true)->orderBy('name')->get();
        $query = $this->buildQuery($request);

        $journals = $query->paginate(25)->withQueryString();

        return view('admin.journal.index', [
            'journals' => $journals,
            'classes' => $classes,
            'filters' => [
                'month' => $request->input('month'),
                'class_id' => $request->input('class_id'),
                'q' => $request->input('q'),
            ],
        ]);
    }

    public function export(DailyHabitJournalFilterRequest $request): StreamedResponse
    {
        $query = $this->buildQuery($request);
        $month = $request->input('month') ?: now()->format('Y-m');
        $filename = "rekap-jurnal-7-kebiasaan-{$month}.csv";

        return $this->exportService->streamCsv($query, $filename);
    }

    private function buildQuery(DailyHabitJournalFilterRequest $request): Builder
    {
        $query = DailyHabitJournal::query()
            ->with(['student.activeEnrollment.schoolClass', 'student.user'])
            ->orderBy('journal_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('month')) {
            $month = (string) $request->input('month');
            $start = Carbon::parse($month)->startOfMonth()->toDateString();
            $end = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->whereBetween('journal_date', [$start, $end]);
        }

        if ($request->filled('class_id')) {
            $classId = (int) $request->input('class_id');
            $query->whereHas('student.enrollments', function (Builder $q) use ($classId): void {
                $q->where('class_id', $classId);
            });
        }

        if ($request->filled('q')) {
            $search = (string) $request->input('q');
            $query->whereHas('student', function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
