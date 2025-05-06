<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'book_id',
        'chapter_number',
        'chapter_title',
        'chapter_body',
        'chapter_is_published',
        'chapter_is_premium',
        'chapter_token_cost',
        'chapter_word_count',
        'chapter_read_count',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
