// 2025_11_08_000006_optimize_pivots.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('category_courses')) {
            Schema::table('category_courses', function (Blueprint $t) {
                $t->unique(['category_id', 'course_id'], 'uniq_category_course');
            });
        }
        if (Schema::hasTable('course_tags')) {
            Schema::table('course_tags', function (Blueprint $t) {
                $t->unique(['course_id', 'tag_id'], 'uniq_course_tag');
            });
        }
        if (Schema::hasTable('quiz_questions')) {
            Schema::table('quiz_questions', function (Blueprint $t) {
                if (!Schema::hasColumn('quiz_questions', 'score')) {
                    $t->decimal('score', 6, 2)->default(1)->after('question_text');
                }
                $t->index('quiz_id', 'qq_quiz_idx');
            });
        }
        if (Schema::hasTable('quiz_options')) {
            Schema::table('quiz_options', function (Blueprint $t) {
                $t->index(['question_id', 'is_correct'], 'qo_question_correct_idx');
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('category_courses', fn(Blueprint $t) => $t->dropUnique('uniq_category_course'));
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('course_tags', fn(Blueprint $t) => $t->dropUnique('uniq_course_tag'));
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('quiz_questions', fn(Blueprint $t) => $t->dropIndex('qq_quiz_idx'));
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('quiz_options', fn(Blueprint $t) => $t->dropIndex('qo_question_correct_idx'));
        } catch (\Throwable $e) {
        }
        if (Schema::hasTable('quiz_questions') && Schema::hasColumn('quiz_questions', 'score')) {
            Schema::table('quiz_questions', fn(Blueprint $t) => $t->dropColumn('score'));
        }
    }
};
