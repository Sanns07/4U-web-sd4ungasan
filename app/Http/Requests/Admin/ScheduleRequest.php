<?php

namespace App\Http\Requests\Admin;

use App\Enums\DayOfWeek;
use Illuminate\Validation\Rule;

class ScheduleRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'room' => $this->filled('room') ? trim((string) $this->input('room')) : null,
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        $schedule = $this->route('schedule');

        return [
            'teaching_assignment_id' => [
                'required', 'integer',
                Rule::exists('teaching_assignments', 'id')->where(function ($query) use ($schedule): void {
                    $query->where('is_active', true);

                    if ($schedule?->teaching_assignment_id) {
                        $query->orWhere('id', $schedule->teaching_assignment_id);
                    }
                }),
            ],
            'day_of_week' => ['required', Rule::enum(DayOfWeek::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'teaching_assignment_id.exists' => 'Pilih penugasan aktif yang masih valid.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'teaching_assignment_id' => 'penugasan mengajar',
            'day_of_week' => 'hari',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'room' => 'ruang',
            'notes' => 'catatan',
        ];
    }
}
