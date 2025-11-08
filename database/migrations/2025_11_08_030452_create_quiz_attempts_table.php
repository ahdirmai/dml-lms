<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $t) {
                $t->uuid('id')->primary();

                // quizzes.id = UUID → tetap foreignUuid
                $t->foreignUuid('quiz_id')->constrained('quizzes')->cascadeOnDelete();

                // users.id kemungkinan unsigned BIGINT → gunakan foreignIdFor(User::class)
                $t->foreignIdFor(\App\Models\User::class, 'user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $t->integer('attempt_no')->default(1);
                $t->timestamp('started_at')->nullable();
                $t->timestamp('finished_at')->nullable();
                $t->decimal('score', 6, 2)->default(0);
                $t->boolean('passed')->default(false);
                $t->integer('duration_seconds')->nullable();
                $t->timestamps();

                $t->index(['quiz_id', 'user_id'], 'qa_quiz_user_idx');
                $t->index(['user_id', 'quiz_id', 'attempt_no'], 'qa_user_quiz_attempt_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
