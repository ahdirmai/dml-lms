<?php

// database/migrations/xxxx_xx_xx_create_integration_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users');
            $table->string('source')->default('internal_system');
            $table->string('action')->nullable();
            $table->string('external_id')->nullable()->index();
            $table->string('status', 20)->default('success'); // success, failed, skipped, etc.
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
