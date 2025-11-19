<?php

// database/migrations/xxxx_xx_xx_create_user_import_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_import_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users');
            $table->string('session_token')->unique();
            $table->json('filters')->nullable();
            $table->json('payload'); // hasil preview dari internal system
            $table->unsignedInteger('total_records')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_import_sessions');
    }
};
