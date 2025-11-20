<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('organization')->nullable()->after('especialidad');
            $table->string('apellido')->nullable()->after('nombre');
            $table->string('signature')->nullable()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('organization');
            $table->dropColumn('apellido');
        });
    }
};

