<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $t->string('title', 200);
            $t->string('slug', 220)->nullable()->unique();
            $t->integer('order')->default(1);
            $t->timestamps();
            $t->index(['course_id', 'order']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
