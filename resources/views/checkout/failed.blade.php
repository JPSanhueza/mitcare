<x-layouts.app>
    <div class="max-w-3xl mx-auto px-4 py-10">
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">No pudimos completar el pago</h1>
        <p class="text-gray-600 mt-2">Tu transacción fue rechazada o cancelada. Puedes intentarlo nuevamente.</p>

        @if($order)
        <div class="mt-6 p-5 rounded-xl border border-yellow-200 bg-yellow-50">
            <p class="text-sm text-yellow-800">
                Nº de orden: <span class="font-semibold">{{ $order->code ?? ('#'.$order->id) }}</span> ·
                Estado: <span class="font-semibold">{{ strtoupper($order->status) }}</span>
            </p>
        </div>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('webpay.start', ['order' => $order->id]) }}"
                class="px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                Reintentar pago
            </a>
            <a href="{{ route('checkout.index') }}" class="px-4 py-2 rounded-lg border font-medium hover:bg-gray-50">
                Volver al checkout
            </a>
        </div>
        @else
        <div class="mt-6">
            <a href="{{ route('checkout.index') }}"
                class="px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                Ir al checkout
            </a>
        </div>
        @endif
    </div>
</x-layouts.app>
