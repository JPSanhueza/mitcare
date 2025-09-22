<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('courses', function (Blueprint $table) {
        $table->id();

        // Catálogo básico
        $table->string('nombre');
        $table->text('subtitulo')->nullable();
        $table->string('slug')->unique();
        $table->text('descripcion')->nullable();

        // Venta simple
        $table->decimal('price', 10, 2);

        // Publicación
        $table->boolean('is_active')->default(true);
        $table->timestamp('published_at')->nullable();

        // Cupos (opcional). NULL = sin límite
        $table->unsignedInteger('capacity')->nullable();

        // Ejecución / ficha
        $table->enum('modality', ['online', 'presencial', 'mixto'])->default('online');
        $table->dateTime('start_at')->nullable();
        $table->dateTime('end_at')->nullable();
        $table->string('location')->nullable();

        // Medios simples
        $table->string('image')->nullable();

        // Opcionales por si conectas algo externo
        $table->string('external_url')->nullable();
        $table->string('moodle_course_id')->nullable();

        $table->timestamps();

        // Índices básicos para listar rápido
        $table->index(['is_active', 'published_at']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
