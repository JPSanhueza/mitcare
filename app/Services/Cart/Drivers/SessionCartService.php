<?php

namespace App\Services\Cart\Drivers;

use App\Services\Cart\CartService;

class SessionCartService implements CartService
{
    private const KEY = 'cart.items';

    public function items(): array
    {
        $items = session(self::KEY, []);

        return collect($items)->values()->map(function ($i, $index) {
            $qty = (int) ($i['qty'] ?? 1);
            $price = (float) ($i['price'] ?? 0);

            return [
                'key' => (string) $index, // índice
                'id' => (int) ($i['id'] ?? 0),
                'name' => (string) ($i['name'] ?? 'Curso'),
                'qty' => $qty,
                'price' => $price,
                'image' => $i['image'] ?? null,
                'subtotal' => $qty * $price,
            ];
        })->all();
    }

    public function add(int $id, string $name, float $price, int $qty = 1, ?string $image = null, array $options = []): void
    {
        $items = session(self::KEY, []);
        $idx = collect($items)->search(fn ($i) => (int) ($i['id'] ?? 0) === $id);

        if ($idx !== false) {
            $items[$idx]['qty'] = ((int) ($items[$idx]['qty'] ?? 0)) + max(1, $qty);
        } else {
            $items[] = [
                'id' => $id,
                'name' => $name,
                'qty' => max(1, $qty),
                'price' => $price,
                'image' => $image,
            ];
        }
        session([self::KEY => array_values($items)]);
    }

    public function remove(string $key): void
    {
        $items = session(self::KEY, []);
        $index = (int) $key;
        if (isset($items[$index])) {
            unset($items[$index]);
            session([self::KEY => array_values($items)]);
        }
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return (int) collect(session(self::KEY, []))->sum(fn ($i) => (int) ($i['qty'] ?? 0));
    }

    public function subtotal(): float
    {
        return (float) collect(session(self::KEY, []))->sum(
            fn ($i) => (int) ($i['qty'] ?? 0) * (float) ($i['price'] ?? 0)
        );
    }

    public function updateQty(string $key, int $qty): void
    {
        $items = session(self::KEY, []);
        $index = (int) $key;

        if (! isset($items[$index])) {
            return;
        }

        $qty = (int) $qty;

        if ($qty <= 0) {
            unset($items[$index]);
            session([self::KEY => array_values($items)]);

            return;
        }

        $items[$index]['qty'] = $qty;
        session([self::KEY => array_values($items)]);
    }
}
