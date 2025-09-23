<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_item_attendees', function (Blueprint $table) {
            $table->boolean('moodle_has_account')->nullable()->after('status');
            $table->string('moodle_username')->nullable()->after('moodle_has_account');
            $table->timestamp('moodle_checked_at')->nullable()->after('moodle_username');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_attendees', function (Blueprint $table) {
            $table->dropColumn(['moodle_has_account', 'moodle_username', 'moodle_checked_at']);
        });
    }
};
