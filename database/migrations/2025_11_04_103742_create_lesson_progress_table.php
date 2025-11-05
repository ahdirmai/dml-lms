<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->bigIncrements('id');

            // FK ke enrollments (siapa yang mengambil course)
            $table->unsignedBigInteger('enrollment_id');

            // FK ke lessons (pakai UUID jika lesson kamu UUID; ganti ke unsignedBigInteger jika integer)
            $table->uuid('lesson_id');

            // Status progres per lesson
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');

            // Optional tracking time
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);

            $table->timestamps();

            // 1 enrollment hanya punya 1 row per lesson
            $table->unique(['enrollment_id', 'lesson_id']);

            $table->foreign('enrollment_id')->references('id')->on('enrollments')->cascadeOnDelete();
            $table->foreign('lesson_id')->references('id')->on('lessons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
