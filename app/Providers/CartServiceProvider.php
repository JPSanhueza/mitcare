<?php

namespace App\Providers;

use App\Services\Cart\CartService;
use App\Services\Cart\Drivers\SessionCartService;
use App\Services\Cart\Drivers\ShoppingcartCartService;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartService::class, function () {
            return match (config('cart.driver')) {
                'shoppingcart' => new ShoppingcartCartService,
                default => new SessionCartService,
            };
        });
    }
}
