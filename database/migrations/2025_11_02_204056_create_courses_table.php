<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('subtitle')->nullable();
            $table->longText('description')->nullable();

            $table->string('thumbnail_url')->nullable();
            $table->string('language', 10)->default('en');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');

            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // author (instructor/admin)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->unsignedInteger('duration_minutes')->default(0); // total duration (cached)
            $table->unsignedInteger('lessons_count')->default(0);    // cached

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
