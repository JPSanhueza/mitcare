<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diplomas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diploma_batch_id')->constrained()->cascadeOnDelete();

            // Fecha de emisión
            $table->date('issued_at');

            // Nota final (se copia desde course_student al generar diploma)
            $table->decimal('final_grade', 5, 2)->nullable();

            // Archivo PDF generado
            $table->string('file_path')->nullable();

            // Código único para verificación (hash)
            $table->string('verification_code')->unique();

            // QR almacenado (opcional) o se genera al vuelo
            $table->string('qr_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diplomas');
    }
};

