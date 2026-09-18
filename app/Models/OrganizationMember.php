<?php

namespace App\Models;

use App\Enums\OrganizationCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'name', 'position_name', 'category', 'photo_path', 'display_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => OrganizationCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function displayName(): string
    {
        return $this->teacher?->name ?? $this->name ?? 'Nama belum diisi';
    }
}
