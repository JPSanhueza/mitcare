<?php

namespace App\Http\Controllers;

use App\Models\Order;

class CheckoutResultController extends Controller
{
    public function success(Order $order)
    {
        $order->load(['items.attendees']);
        return view('checkout.success', compact('order'));
    }

    public function failed(?Order $order = null)
    {
        if ($order) {
            $order->loadMissing(['items.attendees']);
        }
        return view('checkout.failed', compact('order'));
    }
}
