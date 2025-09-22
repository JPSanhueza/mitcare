<?php

namespace App\Livewire;

use App\Services\Cart\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartFab extends Component
{
    public bool $open = false;

    public int $count = 0;

    public array $items = [];

    public int|float $subtotal = 0.0;

    protected CartService $cart;

    public function boot(CartService $cart): void
    {
        $this->cart = $cart;
    }

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('cart:refresh')]
    public function refreshCart(): void
    {
        $this->items = $this->cart->items();
        $this->count = $this->cart->count();
        $this->subtotal = $this->cart->subtotal();
    }

    #[On('cart:open')]
    public function openPopover(): void
    {
        $this->open = true;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function removeItem(string $key): void
    {
        $this->cart->remove($key);
        $this->refreshCart();
    }

    public function clear(): void
    {
        $this->cart->clear();
        $this->refreshCart();
    }

    public function goToCart()
    {
        return $this->redirectRoute('cart.index', navigate: true);
    }

    public function checkout()   { return redirect()->route('checkout.index'); }

    public function render()
    {
        return view('livewire.cart-fab');
    }
}
