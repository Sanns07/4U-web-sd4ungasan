<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyHabitJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'journal_date',
        'wake_up_time',
        'worship_items',
        'exercise_activity',
        'meal_breakfast',
        'meal_lunch',
        'meal_dinner',
        'learning_activity',
        'social_activity',
        'sleep_time',
    ];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date:Y-m-d',
            'worship_items' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
