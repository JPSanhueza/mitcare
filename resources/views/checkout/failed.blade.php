{{-- resources/views/checkout/failed.blade.php --}}
<x-layouts.app>
@php
    $commit    = data_get($order?->meta, 'webpay_commit', []);
    $txDateRaw = data_get($commit, 'transaction_date');
    $txDate    = $txDateRaw ? \Illuminate\Support\Carbon::parse($txDateRaw)->timezone(config('app.timezone')) : null;

    // Estado “humano” en español para la orden
    $statusMap = [
        'failed'    => 'Rechazado',
        'canceled'  => 'Cancelado por el usuario',
        'reversed'  => 'Reversado',
        'pending'   => 'Pendiente',
        'processing'=> 'En proceso',
        'paid'      => 'Pagado',
    ];
    $niceStatus = $statusMap[strtolower($order->status ?? '')] ?? ucfirst($order->status ?? 'Fallido');

    // Email de soporte (ajusta a tu dominio o sácale de config)
    $supportEmail = config('mail.from.address', 'soporte@mitcare.cl');
@endphp

<div class="max-w-4xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="flex items-start gap-3">
        <div class="shrink-0 mt-1">
            <svg class="w-8 h-8 text-rose-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 1 0 9.75 9.75A9.76 9.76 0 0 0 12 2.25Zm0 5.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V8.25A.75.75 0 0 1 12 7.5Zm0 8.25a1.125 1.125 0 1 1 0 2.25 1.125 1.125 0 0 1 0-2.25Z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">No pudimos completar el pago</h1>
            <p class="text-gray-600 mt-1">
                Tu transacción fue {{ strtolower($niceStatus) }}. Puedes intentarlo nuevamente.
            </p>
        </div>
    </div>

    {{-- Banda informativa --}}
    @if($order)
    <div class="mt-6 p-5 rounded-xl border border-amber-200 bg-amber-50 text-sm text-amber-900">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
            <div>
                <span class="font-medium">Nº de orden:</span>
                <span class="font-semibold">{{ $order->code ?? '#'.$order->id }}</span>
            </div>
            <div>
                <span class="font-medium">Estado:</span>
                <span>{{ $niceStatus }}</span>
            </div>
            @if($txDate)
            <div>
                <span class="font-medium">Fecha:</span>
                <span>{{ $txDate->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            {{-- Sugerencias/ayuda --}}
            <div class="rounded-xl border border-gray-200 bg-white">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-lg font-semibold">¿Qué puedes hacer?</h2>
                </div>
                <ul class="p-4 space-y-2 text-sm text-gray-700 list-disc ml-5">
                    <li>Reintenta el pago. A veces el banco rechaza temporalmente.</li>
                    <li>Verifica saldo y topes de tu tarjeta.</li>
                    <li>Si volviste atrás en el banco, el pago quedó cancelado. Puedes intentarlo otra vez.</li>
                    <li>Si el problema persiste, contáctanos en
                        <a href="mailto:{{ $supportEmail }}" class="text-[#19355C] underline">{{ $supportEmail }}</a>.
                    </li>
                </ul>
            </div>

            {{-- (Opcional) Resumen de lo que estabas comprando --}}
            @if($order && $order->items->count())
            <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-lg font-semibold">Resumen de tu carrito</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 text-left">Curso</th>
                                <th class="px-4 py-2 text-center">Cant.</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order->items as $it)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 break-words">{{ $it->course_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">x{{ $it->qty }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        ${{ number_format($it->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right font-medium text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-extrabold text-gray-900">
                                    ${{ number_format($order->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Acciones --}}
        <aside class="space-y-3">
            @if($order)
                <a href="{{ route('webpay.start', ['order' => $order->id]) }}"
                   class="block w-full text-center px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                    Reintentar pago
                </a>
                <a href="{{ route('checkout.index') }}"
                   class="block w-full text-center px-4 py-2 rounded-lg border font-medium hover:bg-gray-50">
                    Volver al checkout
                </a>
            @endif
            <a href="{{ route('home') }}"
               class="block w-full text-center px-4 py-2 rounded-lg border font-medium hover:bg-gray-50">
                Volver al inicio
            </a>
        </aside>
    </div>
</div>
</x-layouts.app>
