<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_published')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
