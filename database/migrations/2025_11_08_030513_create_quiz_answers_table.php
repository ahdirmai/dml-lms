<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('quiz_answers')) {
            Schema::create('quiz_answers', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->foreignUuid('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
                $t->foreignUuid('question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $t->foreignUuid('selected_option_id')->nullable()->constrained('quiz_options')->nullOnDelete();
                $t->text('answer_text')->nullable();
                $t->boolean('is_correct')->default(false);
                $t->decimal('score_awarded', 6, 2)->default(0);
                $t->timestamps();

                $t->index('attempt_id', 'qa_attempt_idx');
                $t->index('question_id', 'qa_question_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
