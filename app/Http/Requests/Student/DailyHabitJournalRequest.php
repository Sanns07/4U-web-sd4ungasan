<?php

namespace App\Http\Requests\Student;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class DailyHabitJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Student)
            && $this->user()?->student !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'journal_date' => $this->filled('journal_date')
                ? $this->input('journal_date')
                : now()->toDateString(),
            'worship_items' => is_array($this->input('worship_items'))
                ? array_values(array_filter($this->input('worship_items')))
                : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'journal_date' => ['required', 'date', 'before_or_equal:today'],
            'wake_up_time' => ['required', 'date_format:H:i'],
            'worship_items' => ['nullable', 'array'],
            'worship_items.*' => ['string', 'max:255'],
            'exercise_activity' => ['required', 'string', 'max:1000'],
            'meal_breakfast' => ['required', 'string', 'max:1000'],
            'meal_lunch' => ['required', 'string', 'max:1000'],
            'meal_dinner' => ['required', 'string', 'max:1000'],
            'learning_activity' => ['required', 'string', 'max:1000'],
            'social_activity' => ['required', 'string', 'max:1000'],
            'sleep_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'journal_date.before_or_equal' => 'Tanggal jurnal tidak boleh melebihi hari ini.',
            'wake_up_time.date_format' => 'Format jam bangun pagi harus berupa HH:MM.',
            'sleep_time.date_format' => 'Format jam tidur malam harus berupa HH:MM.',
        ];
    }

    public function attributes(): array
    {
        return [
            'journal_date' => 'tanggal jurnal',
            'wake_up_time' => 'jam bangun pagi',
            'worship_items' => 'checklist ibadah',
            'exercise_activity' => 'kegiatan berolahraga',
            'meal_breakfast' => 'menu makan pagi / sarapan',
            'meal_lunch' => 'menu makan siang',
            'meal_dinner' => 'menu makan malam',
            'learning_activity' => 'kegiatan gemar belajar',
            'social_activity' => 'kegiatan bermasyarakat',
            'sleep_time' => 'jam tidur malam',
        ];
    }
}
