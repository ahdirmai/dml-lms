<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('department')->nullable();
            $table->string('job_title')->nullable();

            // Masih pakai external_id manager dari sistem internal
            // $table->string('manager_external_id')->nullable();
            // $table->index('manager_external_id', 'user_profiles_manager_external_id_index');

            $table->boolean('is_employee')->default(true);
            $table->boolean('is_hr')->default(false);

            // Opsional tapi sangat berguna
            $table->json('raw_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
