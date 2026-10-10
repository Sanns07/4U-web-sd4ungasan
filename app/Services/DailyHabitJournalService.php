<?php

namespace App\Services;

use App\Models\DailyHabitJournal;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DailyHabitJournalService
{
    /**
     * @return array<int, array{id: string, label: string}>
     */
    public function getWorshipItems(Student $student, CarbonInterface|string $date): array
    {
        $religionKey = $student->religion?->value ?? 'lainnya';
        $allItems = config("worship_checklist.items.{$religionKey}", config('worship_checklist.items.lainnya', []));

        $carbonDate = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $dayOfWeek = (int) $carbonDate->dayOfWeek; // 0 = Sunday, 6 = Saturday

        $filtered = [];
        foreach ($allItems as $item) {
            if (isset($item['day_of_week']) && ! in_array($dayOfWeek, $item['day_of_week'], true)) {
                continue;
            }
            $filtered[] = [
                'id' => $item['id'],
                'label' => $item['label'],
            ];
        }

        return $filtered;
    }

    /**
     * @return array<int, string>
     */
    public function getFilledDates(Student $student, string $month): array
    {
        $start = Carbon::parse($month)->startOfMonth()->toDateString();
        $end = Carbon::parse($month)->endOfMonth()->toDateString();

        return DailyHabitJournal::query()
            ->where('student_id', $student->id)
            ->whereBetween('journal_date', [$start, $end])
            ->pluck('journal_date')
            ->map(fn ($date) => $date instanceof CarbonInterface ? $date->toDateString() : (string) $date)
            ->all();
    }

    public function getJournalForDate(Student $student, string $date): ?DailyHabitJournal
    {
        return DailyHabitJournal::query()
            ->where('student_id', $student->id)
            ->where('journal_date', $date)
            ->first();
    }

    public function upsert(Student $student, array $data): DailyHabitJournal
    {
        $journalDate = $data['journal_date'];
        if (Carbon::parse($journalDate)->startOfDay()->isAfter(now()->startOfDay())) {
            throw new InvalidArgumentException('Tanggal jurnal tidak boleh melebihi hari ini.');
        }

        return DB::transaction(function () use ($student, $data, $journalDate): DailyHabitJournal {
            $attributes = Arr::only($data, [
                'wake_up_time',
                'worship_items',
                'exercise_activity',
                'meal_breakfast',
                'meal_lunch',
                'meal_dinner',
                'learning_activity',
                'social_activity',
                'sleep_time',
            ]);

            return DailyHabitJournal::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'journal_date' => $journalDate,
                ],
                $attributes,
            );
        });
    }
}
