<?php

namespace App\Livewire;

use App\Services\Cart\CartService;
use Livewire\Component;

class CheckoutPage extends Component
{
    public array $items = [];

    public int|float $subtotal = 0.0;

    public int $count = 0;

    public string $name = '';

    public string $email = '';

    public string $paymentMethod = '';

    protected CartService $cart;

    public function boot(CartService $cart): void
    {
        $this->cart = $cart;
    }

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function refreshCart(): void
    {
        $this->items = $this->cart->items();
        $this->count = $this->cart->count();
        $this->subtotal = $this->cart->subtotal();
    }

    public function updated($property)
    {
        $this->validateOnly($property, $this->rules());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'paymentMethod' => ['required', 'in:webpay'],
        ];
    }

    public function checkout(): void
    {
        $this->validate();

        if ($this->count === 0) {
            $this->addError('cart', 'Tu carrito está vacío.');

            return;
        }

        // Aquí crearías la transacción en tu base de datos
        // y guardarías info de carrito, cliente, método de pago.

        // Simulación de ID de transacción:
        $transactionId = uniqid('tx_');

        $this->redirectRoute('webpay.start', ['tx' => $transactionId]);
    }

    public function render()
    {
        return view('livewire.checkout-page')->title('Checkout');
    }
}
