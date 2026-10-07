<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Services\PostHtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_user_id', 'type', 'title', 'slug', 'excerpt', 'body_html', 'cover_image_path',
        'cover_alt_text', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('id');
    }

    public function renderedBodyHtml(): string
    {
        return app(PostHtmlSanitizer::class)->renderImages(
            $this->body_html,
            $this->relationLoaded('images') ? $this->images : $this->images()->get(),
        );
    }

    public function editorBodyHtml(): string
    {
        return app(PostHtmlSanitizer::class)->renderImages(
            $this->body_html,
            $this->relationLoaded('images') ? $this->images : $this->images()->get(),
            true,
        );
    }
}
