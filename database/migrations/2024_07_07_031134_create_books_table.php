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
            $table->uuid('id')->primary();
            $table->string('title')->unique();
            $table->text('description');
            $table->date('published_at');
            $table->string('cover_image')->nullable(); // Optional image field
            $table->string('book_file')->nullable(); // Optional File field
            $table->enum('status', ['Incomplete', 'Completed', 'Unavailable'])->default('Unavailable');
            $table->uuid('author_id')->nullable();

            // Foreign key constraint
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');

            $table->timestamps();
        });

        // Index creation for foreign key
        Schema::table('books', function (Blueprint $table) {
            $table->index('author_id');
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
