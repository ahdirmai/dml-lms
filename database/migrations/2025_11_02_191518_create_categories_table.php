<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            // Perbaikan: Tambahkan ->nullable() di sini
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
