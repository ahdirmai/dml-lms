<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::create('course_tag', function (Blueprint $table) {
        //     $table->uuid('course_id');
        //     $table->uuid('tag_id');
        //     $table->primary(['course_id', 'tag_id']);
        //     $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        //     $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        //     $table->index(['course_id']);
        //     $table->index(['tag_id']);
        // });
    }
    public function down(): void
    {
        // Schema::dropIfExists('course_tag');
    }
};
