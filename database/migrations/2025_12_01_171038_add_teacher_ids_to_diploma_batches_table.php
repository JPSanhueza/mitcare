<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('diploma_batches', function (Blueprint $table) {
            // JSON si tu DB lo soporta bien, si no, usa text()
            $table->json('teacher_ids')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('diploma_batches', function (Blueprint $table) {
            $table->dropColumn('teacher_ids');
        });
    }
};
