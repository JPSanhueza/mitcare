<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();                 // p.ej. ORD-20250921-ULID
            $t->string('buyer_name', 120);
            $t->string('buyer_email', 160);
            $t->string('payment_method', 30)->default('webpayplus');
            $t->string('status', 20)->default('pending');     // pending|processing|paid|failed|canceled
            $t->unsignedInteger('subtotal');                  // CLP sin decimales
            $t->unsignedInteger('total');                     // por si agregas cargos/descuentos
            $t->char('currency', 3)->default('CLP');
            $t->json('meta')->nullable();                     // token webpay, etc.
            $t->timestamps();

            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
