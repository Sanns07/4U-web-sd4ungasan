<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_assignment_id', 'day_of_week', 'start_time', 'end_time', 'room', 'notes',
    ];

    protected function casts(): array
    {
        return ['day_of_week' => DayOfWeek::class];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function startTimeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function endTimeLabel(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }
}
