<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Slug unik (boleh null agar gampang seeding)
            $t->string('slug', 220)->nullable()->unique();

            // FK: Laravel otomatis menambahkan index untuk foreign*()
            $t->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $t->foreignUuid('module_id')->constrained('modules')->cascadeOnDelete();

            // Info utama
            $t->string('title', 200);
            $t->text('description')->nullable();

            // Jenis konten
            // pakai string + index agar fleksibel (youtube|gdrive|quiz|pdf|text|external)
            $t->string('kind', 20)->default('youtube');

            // Media/tautan
            $t->string('content_url', 2048)->nullable();
            $t->string('youtube_video_id', 32)->nullable();
            $t->string('gdrive_file_id', 128)->nullable();

            // Orde dalam modul
            $t->unsignedSmallInteger('order_no')->default(1);

            // Metadata tambahan (opsional, umum dipakai)
            $t->unsignedSmallInteger('duration_minutes')->default(0); // estimasi durasi
            $t->boolean('is_preview')->default(false);                 // bisa dilihat meski belum enroll
            $t->timestamp('published_at')->nullable();                 // jadwal publish

            $t->timestamps();

            // Index & constraint untuk performa & konsistensi urutan
            $t->index(['course_id']);                    // frequent filter
            $t->index(['module_id', 'order_no']);       // daftar lesson per modul dgn orde
            $t->index(['kind']);                         // filter jenis konten
            $t->index(['youtube_video_id']);             // lookup cepat
            $t->index(['gdrive_file_id']);               // lookup cepat

            // Unik per modul untuk menjaga urutan tidak dobel
            $t->unique(['module_id', 'order_no'], 'uniq_module_order');

            // (Opsional, sering dipakai): cari lesson berdasarkan slug dalam satu course
            // $t->unique(['course_id', 'slug'], 'uniq_course_slug');

            // (Opsional, MySQL 8+): fulltext untuk pencarian
            // $t->fullText(['title', 'description'], 'ft_lesson_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
