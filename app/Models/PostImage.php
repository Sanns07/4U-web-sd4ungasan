<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'file_path', 'alt_text', 'caption'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
