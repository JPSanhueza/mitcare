<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAttendee;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CheckoutPage extends Component
{
    /** Paso del wizard (1: comprador, 2: estudiantes, 3: pago) */
    public int $step = 1;

    /** Comprador */
    public string $buyer_name = '';

    public string $buyer_email = '';

    /** Solo WebPay (preseleccionado) */
    public string $payment_method = 'webpayplus';

    public bool $terms_accepted = false;

    /**
     * Roster de estudiantes por ítem:
     * attendees[itemKey][index]['name'|'email']
     */
    public array $attendees = [];

    public int $attVersion = 0;

    /** Snapshot del carrito */
    public array $items = [];

    public int $count = 0;

    public int|float $subtotal = 0.0;

    protected CartService $cart;

    public function boot(CartService $cart): void
    {
        $this->cart = $cart;
    }

    public function mount(): void
    {
        $this->refreshCart();
        $this->syncAttendeesStructure();
    }

    /** Si el carrito cambia desde otro componente, re-sincroniza */
    #[On('cart:refresh')]
    public function refreshCart(): void
    {
        $this->items = $this->cart->items();
        $this->count = $this->cart->count();
        $this->subtotal = $this->cart->subtotal();

        $this->syncAttendeesStructure();
    }

    /** Mantiene attendees alineado con los ítems (keys + qty) */
    private function syncAttendeesStructure(): void
    {
        $new = [];
        foreach ($this->items as $item) {
            $key = (string) $item['key'];
            $qty = (int) $item['qty'];
            $rows = $this->attendees[$key] ?? [];

            for ($i = 0; $i < $qty; $i++) {
                $new[$key][$i] = [
                    'name' => $rows[$i]['name'] ?? '',
                    'email' => $rows[$i]['email'] ?? '',
                ];
            }
        }
        $this->attendees = $new;
    }

    /** Rellena una fila con los datos del comprador */
    public function useBuyerFor(string $key, int $index): void
    {
        Log::info("Usando datos del comprador para item $key, fila $index");

        // Reasignación por copia para que Livewire detecte el cambio
        $att = $this->attendees;
        $att[$key] ??= [];
        $att[$key][$index] ??= ['name' => '', 'email' => ''];

        $att[$key][$index]['name'] = trim($this->buyer_name);
        $att[$key][$index]['email'] = trim($this->buyer_email);

        $this->attendees = $att;

        // 🔁 fuerza un remount del bloque con inputs
        $this->attVersion++;
    }

    /** Ir al siguiente paso con validación del paso actual */
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate($this->rulesStep1());

            if ($this->count <= 0) {
                $this->addError('cart', 'Tu carrito está vacío.');

                return;
            }

            $this->step = 2;

            return;
        }

        if ($this->step === 2) {
            $this->validate($this->rulesStep2());
            $this->step = 3;

            return;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    /** Validaciones por paso */
    protected function rulesStep1(): array
    {
        return [
            'buyer_name' => ['required', 'string', 'max:120'],
            'buyer_email' => ['required', 'email', 'max:160'],
        ];
    }

    protected function rulesStep2(): array
    {
        $rules = [];

        // Requiere nombre y correo por cada cupo de cada ítem
        foreach ($this->items as $item) {
            $key = (string) $item['key'];
            $qty = (int) $item['qty'];

            for ($i = 0; $i < $qty; $i++) {
                $rules["attendees.$key.$i.name"] = ['required', 'string', 'max:120'];
                $rules["attendees.$key.$i.email"] = ['required', 'email', 'max:160'];
            }
        }

        return $rules;
    }

    protected function rulesStep3(): array
    {
        return [
            'payment_method' => ['required', 'in:webpayplus'],
            'terms_accepted' => ['accepted'],
        ];
    }

    /**  Validación en vivo por campo según el paso */
    public function updated($name): void
    {
        if ($this->step === 1 && in_array($name, ['buyer_name', 'buyer_email'], true)) {
            $this->validateOnly($name, $this->rulesStep1());
        }

        if ($this->step === 2 && str_starts_with($name, 'attendees.')) {
            // Usa el set completo de reglas del paso 2; validateOnly aplicará a $name
            $this->validateOnly($name, $this->rulesStep2());
        }
    }

    /** Helpers de validez por paso (no lanzan excepción) */
    private function isStep1Valid(): bool
    {
        $data = [
            'buyer_name' => $this->buyer_name,
            'buyer_email' => $this->buyer_email,
        ];

        return Validator::make($data, $this->rulesStep1())->passes() && $this->count > 0;
    }

    // private function isStep2Valid(): bool
    // {
    //     // Arma data y reglas sólo si hay ítems
    //     if ($this->count <= 0) {
    //         return false;
    //     }

    //     $data = [];
    //     $rules = [];
    //     foreach ($this->items as $item) {
    //         $key = (string) $item['key'];
    //         $qty = (int) $item['qty'];
    //         for ($i = 0; $i < $qty; $i++) {
    //             $data["attendees.$key.$i.name"] = $this->attendees[$key][$i]['name'] ?? '';
    //             $data["attendees.$key.$i.email"] = $this->attendees[$key][$i]['email'] ?? '';
    //             $rules["attendees.$key.$i.name"] = ['required', 'string', 'max:120'];
    //             $rules["attendees.$key.$i.email"] = ['required', 'email', 'max:160'];
    //         }
    //     }

    //     return Validator::make($data, $rules)->passes();
    // }

    private function hasStep2MinimumFilled(): bool
    {
        if ($this->count <= 0) {
            return false;
        }

        foreach ($this->items as $item) {
            $key = (string) $item['key'];
            $qty = (int) $item['qty'];
            for ($i = 0; $i < $qty; $i++) {
                $name = trim($this->attendees[$key][$i]['name'] ?? '');
                $email = trim($this->attendees[$key][$i]['email'] ?? '');
                if ($name === '' || $email === '') {
                    return false;
                }
            }
        }

        return true;
    }

    private function isStep3Valid(): bool
    {
        $data = [
            'payment_method' => $this->payment_method,
            'terms_accepted' => $this->terms_accepted,
        ];

        return Validator::make($data, $this->rulesStep3())->passes();
    }

    /** ✅ Computados para la UI (usar en Blade) */
    public function getCanContinueProperty(): bool
    {
        return match ($this->step) {
            1 => $this->isStep1Valid(),
            // 2 => $this->isStep2Valid(),
            2 => $this->hasStep2MinimumFilled(),
            default => false,
        };
    }

    public function getCanPayProperty(): bool
    {
        // Por seguridad, exige también que los pasos previos estén OK
        return $this->isStep3Valid();
    }

    /** Confirmar y redirigir a WebPay (snapshot en sesión) */
    public function confirmAndPay(): void
    {
        // 1) Validar todo
        $this->validate($this->rulesStep1() + $this->rulesStep2() + $this->rulesStep3());
        if ($this->count <= 0) {
            $this->addError('cart', 'Tu carrito está vacío.');

            return;
        }

        // 2) Normalizar montos (CLP enteros)
        $items = $this->items;
        $calcSubtotal = 0;
        foreach ($items as &$it) {
            $it['price'] = (int) round($it['price']);                 // unidad
            $it['qty'] = (int) $it['qty'];
            $it['subtotal'] = (int) ($it['price'] * $it['qty']);
            $calcSubtotal += $it['subtotal'];
        }
        unset($it);
        $total = $calcSubtotal; // aquí podrías sumar cargos/descuentos

        // 3) Persistir en transacción
        $order = DB::transaction(function () use ($items, $calcSubtotal, $total) {
            $code = 'ORD-'.now()->format('Ymd').'-'.Str::ulid();

            $order = Order::create([
                'code' => $code,
                'buyer_name' => $this->buyer_name,
                'buyer_email' => $this->buyer_email,
                'payment_method' => 'webpayplus',
                'status' => 'pending',
                'subtotal' => $calcSubtotal,
                'total' => $total,
                'currency' => 'CLP',
                'meta' => [],
            ]);

            foreach ($items as $it) {
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => (int) $it['id'],
                    'course_name' => (string) $it['name'],
                    'unit_price' => (int) $it['price'],
                    'qty' => (int) $it['qty'],
                    'subtotal' => (int) $it['subtotal'],
                    'meta' => [],
                ]);

                // attendees por key del carrito
                $key = (string) $it['key'];
                $rows = $this->attendees[$key] ?? [];
                for ($i = 0; $i < $it['qty']; $i++) {
                    $row = $rows[$i] ?? ['name' => '', 'email' => ''];
                    OrderItemAttendee::create([
                        'order_item_id' => $item->id,
                        'course_id' => (int) $it['id'],
                        'name' => (string) ($row['name'] ?? ''),
                        'email' => (string) ($row['email'] ?? ''),
                        'status' => 'pending',
                    ]);
                }
            }

            return $order;
        });

        // 4) Guarda snapshot por si lo necesitas luego (opcional)
        session(['checkout_order_id' => $order->id]);

        // 5) Redirige a iniciar WebPay con esta orden
        $this->redirectRoute('webpay.start', ['order' => $order->id]);
    }

    public function render()
    {
        return view('livewire.checkout-page')->title('Checkout');
    }
}
