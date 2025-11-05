<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150)->unique();
            $table->string('slug', 191)->unique();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
