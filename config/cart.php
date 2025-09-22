<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver del carrito
    |--------------------------------------------------------------------------
    | Puedes elegir entre:
    | - "session" => usa la sesión de Laravel (SessionCartService)
    | - "shoppingcart" => usa el paquete gloudemans/shoppingcart (ShoppingcartCartService)
    |
    | Se puede configurar desde el .env con la variable CART_DRIVER.
    */

    'driver' => env('CART_DRIVER', 'session'),

];
