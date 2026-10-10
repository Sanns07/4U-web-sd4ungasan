<?php

namespace App\Services;

use App\Models\DailyHabitJournal;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyHabitJournalExportService
{
    public function streamCsv(Builder $query, string $filename = 'rekap-jurnal.csv'): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header row
            fputcsv($handle, [
                'No',
                'Tanggal',
                'Nama Siswa',
                'Kelas',
                'Agama',
                '1. Bangun Pagi',
                '2. Rekap Ibadah',
                '3. Olahraga',
                '4a. Makan Pagi',
                '4b. Makan Siang',
                '4c. Makan Malam',
                '5. Gemar Belajar',
                '6. Bermasyarakat',
                '7. Tidur Cepat',
            ]);

            $rowNumber = 1;

            $query->chunk(200, function ($journals) use ($handle, &$rowNumber): void {
                /** @var DailyHabitJournal $journal */
                foreach ($journals as $journal) {
                    $student = $journal->student;
                    $className = $student?->activeEnrollment?->schoolClass?->name ?? '-';
                    $religionLabel = $student?->religion?->label() ?? '-';

                    $worshipText = is_array($journal->worship_items) && count($journal->worship_items) > 0
                        ? implode(', ', $journal->worship_items)
                        : '-';

                    fputcsv($handle, [
                        $rowNumber++,
                        $journal->journal_date?->format('d/m/Y') ?? (string) $journal->journal_date,
                        $student?->name ?? '-',
                        $className,
                        $religionLabel,
                        $journal->wake_up_time ? substr($journal->wake_up_time, 0, 5) : '-',
                        $worshipText,
                        $journal->exercise_activity ?: '-',
                        $journal->meal_breakfast ?: '-',
                        $journal->meal_lunch ?: '-',
                        $journal->meal_dinner ?: '-',
                        $journal->learning_activity ?: '-',
                        $journal->social_activity ?: '-',
                        $journal->sleep_time ? substr($journal->sleep_time, 0, 5) : '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
