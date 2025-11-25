<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->string('title', 180);
            $t->string('slug', 220)->unique();
            $t->string('subtitle', 180)->nullable();

            $t->longText('description');

            // dipakai saat upload thumbnail
            $t->string('thumbnail_path', 2048)->nullable();

            // status: draft|published|archived
            $t->string('status', 20)->default('draft');
            $t->timestamp('published_at')->nullable();

            // level/kesulitan: beginner|intermediate|advanced
            $t->string('difficulty', 20)->default('beginner');

            // relasi ke users (instructor)
            $t->foreignId('instructor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $t->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            // indeks tambahan yang umum dipakai
            $t->index(['status']);
            $t->index(['difficulty']);
            $t->index(['instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
