<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function create(array $data): Schedule
    {
        return $this->save(new Schedule, $data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        return $this->save($schedule, $data);
    }

    private function save(Schedule $schedule, array $data): Schedule
    {
        return DB::transaction(function () use ($schedule, $data): Schedule {
            $assignment = TeachingAssignment::query()
                ->with(['teacher', 'subject', 'schoolClass'])
                ->lockForUpdate()
                ->findOrFail($data['teaching_assignment_id']);

            $usesCurrentAssignment = $schedule->exists
                && $schedule->teaching_assignment_id === $assignment->id;

            if (! $usesCurrentAssignment
                && (! $assignment->is_active || ! $assignment->teacher->is_active || ! $assignment->subject->is_active || ! $assignment->schoolClass->is_active)) {
                throw ValidationException::withMessages([
                    'teaching_assignment_id' => 'Penugasan harus aktif dan terhubung ke guru, mata pelajaran, serta kelas aktif.',
                ]);
            }

            Teacher::query()->lockForUpdate()->findOrFail($assignment->teacher_id);
            SchoolClass::query()->lockForUpdate()->findOrFail($assignment->class_id);

            $start = $this->normalizeTime($data['start_time']);
            $end = $this->normalizeTime($data['end_time']);
            $room = filled($data['room'] ?? null) ? trim($data['room']) : $assignment->schoolClass->room;
            $base = Schedule::query()
                ->with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass'])
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->whereHas('teachingAssignment.schoolClass', fn (Builder $query) => $query
                    ->where('academic_period_id', $assignment->schoolClass->academic_period_id))
                ->when($schedule->exists, fn (Builder $query) => $query->whereKeyNot($schedule->id));

            $classConflict = (clone $base)
                ->whereHas('teachingAssignment', fn (Builder $query) => $query->where('class_id', $assignment->class_id))
                ->lockForUpdate()
                ->first();

            if ($classConflict) {
                throw ValidationException::withMessages([
                    'start_time' => "Kelas {$assignment->schoolClass->name} sudah memiliki {$classConflict->teachingAssignment->subject->name} pada {$classConflict->startTimeLabel()}–{$classConflict->endTimeLabel()}.",
                ]);
            }

            $teacherConflict = (clone $base)
                ->whereHas('teachingAssignment', fn (Builder $query) => $query->where('teacher_id', $assignment->teacher_id))
                ->lockForUpdate()
                ->first();

            if ($teacherConflict) {
                throw ValidationException::withMessages([
                    'start_time' => "{$assignment->teacher->name} sudah mengajar {$teacherConflict->teachingAssignment->schoolClass->name} pada {$teacherConflict->startTimeLabel()}–{$teacherConflict->endTimeLabel()}.",
                ]);
            }

            if ($room !== null) {
                $roomConflict = (clone $base)
                    ->whereRaw('LOWER(room) = ?', [mb_strtolower($room)])
                    ->lockForUpdate()
                    ->first();

                if ($roomConflict) {
                    throw ValidationException::withMessages([
                        'room' => "Ruang {$room} sudah digunakan pada {$roomConflict->startTimeLabel()}–{$roomConflict->endTimeLabel()}.",
                    ]);
                }
            }

            $schedule->fill([
                'teaching_assignment_id' => $assignment->id,
                'day_of_week' => $data['day_of_week'],
                'start_time' => $start,
                'end_time' => $end,
                'room' => $room,
                'notes' => $data['notes'] ?? null,
            ])->save();

            return $schedule->refresh()->load(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass']);
        }, 3);
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
