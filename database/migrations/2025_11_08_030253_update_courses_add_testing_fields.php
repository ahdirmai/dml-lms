<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            if (!Schema::hasColumn('courses', 'has_pretest')) {
                $t->boolean('has_pretest')->default(false)->after('status');
            }
            if (!Schema::hasColumn('courses', 'has_posttest')) {
                $t->boolean('has_posttest')->default(false)->after('has_pretest');
            }
            if (!Schema::hasColumn('courses', 'default_passing_score')) {
                $t->decimal('default_passing_score', 5, 2)->nullable()->after('has_posttest');
            }
            if (!Schema::hasColumn('courses', 'pretest_passing_score')) {
                $t->decimal('pretest_passing_score', 5, 2)->nullable()->after('default_passing_score');
            }
            if (!Schema::hasColumn('courses', 'posttest_passing_score')) {
                $t->decimal('posttest_passing_score', 5, 2)->nullable()->after('pretest_passing_score');
            }
            if (!Schema::hasColumn('courses', 'require_pretest_before_content')) {
                $t->boolean('require_pretest_before_content')->default(false)->after('posttest_passing_score');
            }

            $t->index(['has_pretest', 'has_posttest'], 'courses_has_tests_idx');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            foreach (
                [
                    'has_pretest',
                    'has_posttest',
                    'default_passing_score',
                    'pretest_passing_score',
                    'posttest_passing_score',
                    'require_pretest_before_content'
                ] as $col
            ) {
                if (Schema::hasColumn('courses', $col)) $t->dropColumn($col);
            }
            try {
                $t->dropIndex('courses_has_tests_idx');
            } catch (\Throwable $e) {
            }
        });
    }
};
