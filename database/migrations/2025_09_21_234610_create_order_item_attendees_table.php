<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_attendees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('course_id');
            $t->string('name', 120);
            $t->string('email', 160);
            $t->string('status', 20)->default('pending');      // pending|enrolled
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_attendees');
    }
};
