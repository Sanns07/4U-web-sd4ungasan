<?php

namespace App\Http\Requests\Admin;

class DailyHabitJournalFilterRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
