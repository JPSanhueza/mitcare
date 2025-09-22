<?php

namespace App\Livewire;

use App\Services\Cart\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPage extends Component
{
    public array $items = [];
    public int $count = 0;
    public int|float $subtotal = 0.0;

    protected CartService $cart;

    public function boot(CartService $cart): void { $this->cart = $cart; }

    public function mount(): void { $this->refresh(); }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->items    = $this->cart->items();
        $this->count    = $this->cart->count();
        $this->subtotal = $this->cart->subtotal();
    }

    public function updateQty(string $key, $qty): void
    {
        $qty = (int) $qty;

        if ($qty < 1) {
            $this->cart->remove($key);
        } else {
            $this->cart->updateQty($key, $qty);
        }

        $this->refresh();
        $this->dispatch('cart-updated');
    }

    public function increment(string $key): void
    {
        $item = $this->findItem($key);
        if ($item) {
            $this->cart->updateQty($key, (int)$item['qty'] + 1);
            $this->refresh();
            $this->dispatch('cart-updated');
        }
    }

    public function decrement(string $key): void
    {
        $item = $this->findItem($key);
        if ($item) {
            $new = (int)$item['qty'] - 1;
            if ($new < 1) {
                $this->cart->remove($key);
            } else {
                $this->cart->updateQty($key, $new);
            }
            $this->refresh();
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem(string $key): void
    {
        $this->cart->remove($key);
        $this->refresh();
        $this->dispatch('cart-updated');
    }

    public function clear(): void
    {
        $this->cart->clear();
        $this->refresh();
        $this->dispatch('cart-updated');
    }

    public function checkout()   { return redirect()->route('checkout.index'); }

    public function render()
    {
        return view('livewire.cart-page')->title('Tu carrito');
    }

    private function findItem(string $key): ?array
    {
        foreach ($this->items as $i) {
            if ($i['key'] === $key) return $i;
        }
        return null;
    }
}
