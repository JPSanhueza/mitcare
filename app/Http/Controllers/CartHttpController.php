<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartHttpController extends Controller
{
    public function destroy(string $key, CartService $cart)
    {
        $cart->remove($key);

        // Si quieres soportar XHR:
        if (request()->wantsJson()) {
            return response()->noContent();
        }

        return back()->with('cart_notice', 'Ítem eliminado');
    }

    public function clear(CartService $cart)
    {
        $cart->clear();

        if (request()->wantsJson()) {
            return response()->noContent();
        }

        return back()->with('cart_notice', 'Carrito vaciado');
    }
}
