<?php

namespace App\Services\Cart\Drivers;

use App\Services\Cart\CartService;
use Gloudemans\Shoppingcart\Facades\Cart as GCart;

class ShoppingcartCartService implements CartService
{
    public function items(): array
    {
        return GCart::content()->map(fn ($row) => [
            'key' => (string) $row->rowId,
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'qty' => (int) $row->qty,
            'price' => (float) $row->price,
            'image' => $row->options->get('image'),
            'subtotal' => (float) $row->price * (int) $row->qty,
        ])->values()->all();
    }

    public function add(int $id, string $name, float $price, int $qty = 1, ?string $image = null, array $options = []): void
    {
        GCart::add($id, $name, max(1, $qty), $price, 0, array_merge(['image' => $image], $options));
    }

    public function remove(string $key): void
    {
        GCart::remove($key);
    }

    public function clear(): void
    {
        GCart::destroy();
    }

    public function count(): int
    {
        return (int) GCart::count();
    }

    public function subtotal(): float
    {
        return (float) GCart::subtotal(0, '.', '');
    }

    public function updateQty(string $key, int $qty): void
    {
        $qty = (int) $qty;

        if ($qty <= 0) {
            GCart::remove($key);

            return;
        }

        // En gloudemans/shoppingcart, update(rowId, qty) setea cantidad absoluta
        GCart::update($key, $qty);
    }
}
