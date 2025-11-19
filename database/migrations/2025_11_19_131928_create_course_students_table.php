<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_student', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->dateTime('enrolled_at')->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->boolean('approved')->default(false);

            // 🔹 Asistencia en porcentaje (0–100)
            $table->unsignedTinyInteger('attendance')->nullable();


            // Para saber si ya se emitió diploma
            $table->boolean('diploma_issued')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};

