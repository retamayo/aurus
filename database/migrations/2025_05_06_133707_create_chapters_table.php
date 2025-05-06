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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
        
            $table->unsignedInteger('chapter_number');
            $table->string('chapter_title');
            $table->longText('chapter_body');
        
            $table->boolean('chapter_is_published')->default(false);
            $table->boolean('chapter_is_premium')->default(false);
            $table->unsignedInteger('chapter_token_cost')->default(0);
        
            $table->unsignedInteger('chapter_word_count')->default(0);
            $table->unsignedInteger('chapter_read_count')->default(0);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
