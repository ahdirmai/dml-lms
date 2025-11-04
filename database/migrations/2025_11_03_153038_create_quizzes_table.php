<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $t->string('title', 200);
            $t->integer('time_limit_seconds')->nullable();
            $t->boolean('shuffle_questions')->default(true);
            $t->timestamps();
            $t->index(['lesson_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
