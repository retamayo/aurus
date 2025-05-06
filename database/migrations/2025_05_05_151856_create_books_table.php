<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('book_title');
            $table->text('book_synopsis')->nullable();
            $table->string('book_genre')->nullable();
            $table->string('book_cover_image')->nullable();
            $table->string('book_slug')->unique();
        
            $table->enum('book_visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->enum('book_status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->string('book_language')->default('en');
            $table->boolean('book_is_premium')->default(false);
            $table->unsignedInteger('book_token_cost')->default(0);
        
            $table->unsignedInteger('book_word_count')->default(0);
            $table->unsignedInteger('book_read_count')->default(0);
            $table->unsignedInteger('book_favorite_count')->default(0);
            $table->unsignedInteger('book_comment_count')->default(0);
            $table->decimal('book_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('book_rating_count')->default(0);
        
            $table->json('book_tags')->nullable();
            $table->longText('book_notes')->nullable();
        
            $table->json('book_ai_context')->nullable();
            $table->timestamp('book_ai_last_generated_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
