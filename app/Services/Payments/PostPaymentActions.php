<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\EnrollmentService;

class PostPaymentActions
{
    public function __construct(
        private CartService $cart,
        private EnrollmentService $enrollment,
    ) {}

    public function onApproved(Order $order): void
    {
        // marca pagada (si aún no)
        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);
        }

        // matricula + correos + limpia carrito
        $this->enrollment->enrollFromOrder($order);
        $this->cart->clear();
    }
}
