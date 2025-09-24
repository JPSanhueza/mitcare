<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAttendee;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;

class CheckoutPage extends Component
{
    private const DRAFT_NS = 'checkout.draft.v1';

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

    public bool $isPaying = false;

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
        $this->refreshCart();      // carrito actual
        $this->hydrateFromDraft(); // trae borrador (buyer, attendees, step, etc.)
        $this->syncAttendeesStructure(); // reacomoda attendees según qty actual
        $this->persistDraft();     // guarda estado inicial ya coherente
    }

    /** Si el carrito cambia desde otro componente, re-sincroniza */
    #[On('cart:refresh')]
    public function refreshCart(): void
    {
        $this->items = $this->cart->items();
        $this->count = $this->cart->count();
        $this->subtotal = $this->cart->subtotal();

        $this->syncAttendeesStructure();
        $this->persistDraft();
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

    private function draftKey(): string
    {
        // Si hay usuario autenticado, guarda por usuario. Si no, una clave fija por navegador.
        $base = 'checkout.draft.v1';
        if (auth()->check()) {
            return "{$base}.user.".auth()->id();
        }

        // Para invitados, una sola clave estable por cookie de sesión.
        return "{$base}.guest";
    }

    private function persistDraft(): void
    {
        session()->put($this->draftKey(), [
            'step' => $this->step,
            'buyer_name' => $this->buyer_name,
            'buyer_email' => $this->buyer_email,
            'payment_method' => $this->payment_method,
            'terms_accepted' => $this->terms_accepted,
            'attendees' => $this->attendees,    // se recorta luego según qty
            'items' => $this->items,        // snapshot para reconstruir estructura
            'subtotal' => $this->subtotal,
            'count' => $this->count,
            'ts' => now()->timestamp,
        ]);
    }

    private function hydrateFromDraft(): void
    {
        $data = session()->get($this->draftKey());
        if (! $data || ! is_array($data)) {
            return;
        }

        // Solo tomamos lo seguro; el carrito real manda
        $this->step = (int) ($data['step'] ?? $this->step);
        $this->buyer_name = (string) ($data['buyer_name'] ?? $this->buyer_name);
        $this->buyer_email = (string) ($data['buyer_email'] ?? $this->buyer_email);
        $this->payment_method = (string) ($data['payment_method'] ?? $this->payment_method);
        $this->terms_accepted = (bool) ($data['terms_accepted'] ?? $this->terms_accepted);
        $this->attendees = (array) ($data['attendees'] ?? $this->attendees);

        // Re-sincroniza estructura con el carrito vigente (qty/keys actuales)
        $this->syncAttendeesStructure();
    }

    private function clearDraft(): void
    {
        session()->forget($this->draftKey());
    }

    public function dehydrate(): void
    {
        $this->persistDraft();
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
        $this->persistDraft();
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
            $this->persistDraft();

            return;
        }

        if ($this->step === 2) {
            $this->validate($this->rulesStep2());
            $this->step = 3;
            $this->persistDraft();

            return;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
        $this->persistDraft();
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
            // 'terms_accepted' => ['accepted'],
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
        $this->persistDraft();
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
        // Evita re-entradas locales
        if ($this->isPaying) {
            return;
        }
        $this->isPaying = true;

        Log::info('[checkout] confirmAndPay CLICK', [
            'step' => $this->step,
            'count' => $this->count,
            'pm' => $this->payment_method,
            'terms' => $this->terms_accepted,
        ]);

        // 1) Validación de los 3 pasos
        try {
            $this->validate($this->rulesStep1() + $this->rulesStep2() + $this->rulesStep3());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errs = $e->validator->errors()->toArray();
            Log::warning('[checkout] validation failed', ['errors' => $errs]);

            // regresar al paso con errores
            $keys = array_keys($errs);
            $this->step = collect($keys)->contains(fn ($k) => str_starts_with($k, 'attendees.')) ? 2 : 1;

            $this->dispatch('toast', body: 'Revisa los campos pendientes.');
            $this->dispatch('unlockPayBtn'); // re-habilita el botón en la UI
            $this->isPaying = false;

            return;
        }

        // 2) Normaliza ítems y totales
        $items = $this->items;
        $calcSubtotal = 0;
        foreach ($items as &$it) {
            $it['price'] = (int) round($it['price']);
            $it['qty'] = (int) $it['qty'];
            $it['subtotal'] = (int) ($it['price'] * $it['qty']);
            $calcSubtotal += $it['subtotal'];
        }
        unset($it);
        $total = $calcSubtotal;

        // 3) Candado para evitar dobles inicios (idempotencia)
        // Puedes usar también el ID de una pre-orden si lo tuvieras.
        $lockKey = 'checkout:'.session()->getId();

        try {
            $result = Cache::lock($lockKey, 15)->block(0, function () use ($items, $calcSubtotal, $total) {

                // 3.1) Crea la orden bajo el candado
                try {
                    DB::beginTransaction();

                    do {
                        $code = 'ORD-'.now()->format('Ymd').'-'.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    } while (Order::where('code', $code)->exists());

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

                    DB::commit();
                    Log::info('[checkout] order created', ['order_id' => $order->id, 'total' => $order->total]);

                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('[checkout] order create failed', ['msg' => $e->getMessage()]);
                    $this->dispatch('toast', body: 'No se pudo preparar tu orden. Intenta de nuevo.');
                    $this->addError('payment', 'No se pudo procesar el pago, intenta nuevamente.');
                    $this->dispatch('unlockPayBtn');
                    $this->isPaying = false;

                    return null;
                }

                // 3.2) Redirección a iniciar pago (tu ruta webpay.start)
                try {
                    Log::info('[checkout] redirecting to webpay.start', ['order_id' => $order->id]);

                    // Livewire v3: redirección de navegador (no SPA)
                    $this->redirectRoute('webpay.start', ['order' => $order->id], navigate: false);

                    // Importante: terminar aquí para no ejecutar más código.
                    return 'redirected';
                } catch (\Throwable $e) {
                    Log::error('[checkout] redirect failed', ['msg' => $e->getMessage()]);
                    $this->dispatch('toast', body: 'No se pudo redirigir a Webpay.');
                    $this->dispatch('unlockPayBtn');
                    $this->isPaying = false;

                    return null;
                }
            });

            // Si no se pudo obtener el lock (tap muy rápido o doble envío)
            if ($result === null) {
                Log::warning('[checkout] lock not acquired');
                $this->addError('payment', 'Estamos procesando tu pago. Si no avanza, actualiza la página.');
                $this->dispatch('unlockPayBtn');
                $this->isPaying = false;
            }

            // Si fue 'redirected', el navegador ya navegó.

        } catch (\Throwable $e) {
            // Falla del mecanismo de lock o error general
            Log::error('[checkout] lock/flow error', ['msg' => $e->getMessage()]);
            $this->addError('payment', 'No se pudo iniciar el pago. Intenta nuevamente.');
            $this->dispatch('unlockPayBtn');
            $this->isPaying = false;
        }
    }

    // app/Livewire/CheckoutPage.php

    protected array $messages = [
        // Paso 1
        'buyer_name.required' => 'El nombre es obligatorio.',
        'buyer_name.string' => 'El nombre no es válido.',
        'buyer_name.max' => 'El nombre no debe superar :max caracteres.',
        'buyer_email.required' => 'El correo es obligatorio.',
        'buyer_email.email' => 'Ingresa un correo válido.',
        'buyer_email.max' => 'El correo no debe superar :max caracteres.',

        // Paso 2 (repite para todos los ítems/índices)
        'attendees.*.*.name.required' => 'El nombre del estudiante es obligatorio.',
        'attendees.*.*.name.string' => 'El nombre del estudiante no es válido.',
        'attendees.*.*.name.max' => 'El nombre del estudiante no debe superar :max caracteres.',
        'attendees.*.*.email.required' => 'El correo del estudiante es obligatorio.',
        'attendees.*.*.email.email' => 'El correo del estudiante no es válido.',
        'attendees.*.*.email.max' => 'El correo del estudiante no debe superar :max caracteres.',

        // Paso 3
        'payment_method.required' => 'Debes seleccionar un método de pago.',
        'payment_method.in' => 'El método de pago seleccionado no es válido.',
        'terms_accepted.accepted' => 'Debes aceptar los Términos y Condiciones.',
    ];

    protected array $validationAttributes = [
        'buyer_name' => 'nombre',
        'buyer_email' => 'correo',
        'attendees.*.*.name' => 'nombre del estudiante',
        'attendees.*.*.email' => 'correo del estudiante',
        'payment_method' => 'método de pago',
        'terms_accepted' => 'términos y condiciones',
    ];

    public function render()
    {
        return view('livewire.checkout-page')->title('Checkout');
    }
}
