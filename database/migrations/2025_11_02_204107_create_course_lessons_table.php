<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $t->foreignUuid('module_id')->constrained('modules')->cascadeOnDelete();
            $t->string('title', 200);
            $t->string('kind', 20)->default('youtube'); // youtube|gdrive|quiz
            $t->string('content_url', 2048)->nullable();
            $t->string('youtube_video_id', 32)->nullable();
            $t->string('gdrive_file_id', 128)->nullable();
            $t->integer('order')->default(1);
            $t->timestamps();
            $t->index(['module_id', 'order']);
            $t->index(['kind']);
            $t->index(['youtube_video_id']);
            $t->index(['gdrive_file_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
