<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('email')->nullable()->unique();
            // En Chile el RUT es clave: ideal que sea único
            $table->string('rut')->unique();


            // Datos opcionales
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
