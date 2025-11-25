<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $t) {
            if (!Schema::hasColumn('quizzes', 'passing_score')) {
                $t->decimal('passing_score', 5, 2)->nullable()->after('title');
            }
            if (!Schema::hasColumn('quizzes', 'quiz_kind')) {
                $t->string('quiz_kind', 20)->default('regular')->after('passing_score'); // pretest|posttest|regular
                $t->index('quiz_kind', 'quizzes_quiz_kind_idx');
            }
            if (!Schema::hasColumn('quizzes', 'quizzable_type') && !Schema::hasColumn('quizzes', 'quizzable_id')) {
                $t->nullableMorphs('quizzable'); // quizzable_type, quizzable_id
            }
        });

        // Migrasi lesson_id -> morph Lesson
        if (Schema::hasColumn('quizzes', 'lesson_id')) {
            $lessonClass = 'App\\Models\\Lms\\Lesson';
            DB::statement("UPDATE quizzes SET quizzable_type = '{$lessonClass}', quizzable_id = lesson_id WHERE lesson_id IS NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $t) {
            if (Schema::hasColumn('quizzes', 'quizzable_type')) $t->dropColumn('quizzable_type');
            if (Schema::hasColumn('quizzes', 'quizzable_id')) $t->dropColumn('quizzable_id');
            if (Schema::hasColumn('quizzes', 'quiz_kind')) {
                try {
                    $t->dropIndex('quizzes_quiz_kind_idx');
                } catch (\Throwable $e) {
                }
                $t->dropColumn('quiz_kind');
            }
            if (Schema::hasColumn('quizzes', 'passing_score')) $t->dropColumn('passing_score');
        });
    }
};
