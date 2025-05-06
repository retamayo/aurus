<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'book_title',
        'book_synopsis',
        'book_genre',
        'book_cover_image',
        'book_slug',
        'book_visibility',
        'book_status',
        'book_language',
        'book_is_premium',
        'book_token_cost',
        'book_word_count',
        'book_read_count',
        'book_favorite_count',
        'book_comment_count',
        'book_rating',
        'book_rating_count',
        'book_tags',
        'book_notes',
        'book_ai_context',
        'book_ai_last_generated_at',
    ];

    protected $casts = [
        'book_tags' => 'array',
        'book_ai_context' => 'array',
        'book_ai_last_generated_at' => 'datetime',
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
}
