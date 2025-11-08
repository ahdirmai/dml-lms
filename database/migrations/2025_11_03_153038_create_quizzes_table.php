<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Polymorphic relation (bisa milik course atau lesson)
            $t->nullableUuidMorphs('quizzable'); // BENAR untuk UUID

            // Jenis quiz: pretest | posttest | regular
            $t->string('quiz_kind', 20)->default('regular')->index();

            // Informasi dasar
            $t->string('title', 200);
            $t->integer('time_limit_seconds')->default(0); // waktu (detik)
            $t->boolean('shuffle_questions')->default(true);
            $t->boolean('shuffle_options')->default(true);

            // Passing score (bisa null, pakai default di course)
            $t->decimal('passing_score', 5, 2)->nullable();

            // Statistik (opsional untuk perkembangan LMS)
            $t->integer('total_questions')->default(0);
            $t->integer('max_score')->default(0);

            $t->timestamps();

            // Index untuk polymorph & filtering
            $t->index(['quizzable_id', 'quizzable_type']);
            $t->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
