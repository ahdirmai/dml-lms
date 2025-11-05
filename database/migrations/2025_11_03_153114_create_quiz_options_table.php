<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_options', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $t->text('option_text');
            $t->boolean('is_correct')->default(false);
            $t->timestamps();
            $t->index(['question_id']);
            $t->index(['is_correct']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
    }
};
