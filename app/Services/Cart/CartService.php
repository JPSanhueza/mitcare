<?php

namespace App\Services\Cart;

interface CartService
{
    /** @return array<int, array{key:string,id:int,name:string,qty:int,price:float,image:?string,subtotal:float}> */
    public function items(): array;

    public function add(int $id, string $name, float $price, int $qty = 1, ?string $image = null, array $options = []): void;

    /** $key es índice (sesión) o rowId (shoppingcart) */
    public function remove(string $key): void;

    public function updateQty(string $key, int $qty): void;

    public function clear(): void;

    public function count(): int;

    public function subtotal(): float;
}
