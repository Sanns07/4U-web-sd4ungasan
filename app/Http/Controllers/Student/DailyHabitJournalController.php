<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\DailyHabitJournalRequest;
use App\Models\Student;
use App\Services\DailyHabitJournalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyHabitJournalController extends Controller
{
    public function __construct(private readonly DailyHabitJournalService $journalService) {}

    public function index(Request $request, ?string $date = null): View|RedirectResponse
    {
        /** @var Student|null $student */
        $student = $request->user()->student;

        if (! $student) {
            return view('student.journal.no-profile');
        }

        $targetDate = $date ?? $request->query('date', now()->toDateString());

        // Validate date format and ensure not future date
        try {
            $parsedDate = Carbon::parse($targetDate);
        } catch (\Exception) {
            $parsedDate = now();
        }

        if ($parsedDate->startOfDay()->isAfter(now()->startOfDay())) {
            return redirect()->route('student.journal.index', ['date' => now()->toDateString()])
                ->with('error', 'Tanggal jurnal tidak boleh melebihi hari ini.');
        }

        $selectedDateString = $parsedDate->toDateString();
        $journal = $this->journalService->getJournalForDate($student, $selectedDateString);
        $worshipItems = $this->journalService->getWorshipItems($student, $parsedDate);
        $filledDates = $this->journalService->getFilledDates($student, $parsedDate->format('Y-m'));

        return view('student.journal.index', [
            'student' => $student,
            'selectedDate' => $parsedDate,
            'selectedDateString' => $selectedDateString,
            'journal' => $journal,
            'worshipItems' => $worshipItems,
            'filledDates' => $filledDates,
        ]);
    }

    public function store(DailyHabitJournalRequest $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->user()->student;

        $journal = $this->journalService->upsert($student, $request->validated());

        return redirect()->route('student.journal.index', ['date' => $journal->journal_date->toDateString()])
            ->with('success', 'Jurnal 7 Kebiasaan Anak Hebat tanggal '.$journal->journal_date->format('d/m/Y').' berhasil disimpan.');
    }
}
