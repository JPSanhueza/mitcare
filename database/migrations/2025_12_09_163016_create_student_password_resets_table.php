<?php

// database/migrations/2025_12_09_000000_create_student_password_resets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('type', ['invite', 'reset'])->default('invite');
            $table->timestamp('expires_at')->nullable(); // ej: +48h
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_password_resets');
    }
};

