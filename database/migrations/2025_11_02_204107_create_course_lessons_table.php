<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('module_id')->constrained('course_modules')->cascadeOnDelete();

            $table->string('title');
            $table->enum('content_type', ['video', 'text', 'embed', 'file'])->default('text');

            // konten fleksibel
            $table->longText('body')->nullable();           // untuk text/markdown/HTML
            $table->string('video_url')->nullable();        // untuk video
            $table->json('meta')->nullable();               // extras (embed code, file path, etc)

            $table->unsignedInteger('duration_minutes')->default(0);
            $table->unsignedInteger('position')->default(1);

            $table->boolean('is_preview')->default(false);
            $table->boolean('is_published')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['module_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
