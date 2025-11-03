<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_course', function (Blueprint $table) {
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->primary(['category_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_course');
    }
};
