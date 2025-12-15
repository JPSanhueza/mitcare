<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('total_hours')->nullable()
                ->after('price');

            $table->string('hours_description')->nullable()
                ->after('total_hours');

            $table->string('nombre_diploma')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['total_hours', 'hours_description, nombre_diploma']);
        });
    }
};
