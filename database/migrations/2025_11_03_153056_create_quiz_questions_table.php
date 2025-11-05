<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $t->text('question');
            $t->string('qtype', 20)->default('mcq'); // mcq|truefalse|shortanswer
            $t->decimal('score', 6, 2)->default(1.00);
            $t->integer('order')->default(1);
            $t->timestamps();
            $t->index(['quiz_id', 'order']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
