<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\Religion;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'nisn', 'nis', 'name', 'gender', 'religion', 'birth_place', 'birth_date', 'address',
        'parent_name', 'parent_phone', 'status', 'graduation_year',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'religion' => Religion::class,
            'birth_date' => 'date',
            'status' => StudentStatus::class,
            'graduation_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyHabitJournals(): HasMany
    {
        return $this->hasMany(DailyHabitJournal::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'student_enrollments', 'student_id', 'class_id')
            ->withPivot(['enrolled_at', 'ended_at', 'status'])
            ->withTimestamps();
    }

    public function activeEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('schoolClass.academicPeriod', fn ($query) => $query->where('is_active', true))
            ->latestOfMany();
    }
}
