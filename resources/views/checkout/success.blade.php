<x-layouts.app>
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">¡Pago realizado con éxito!</h1>
    <p class="text-gray-600 mt-2">Hemos recibido tu pago. En breve enviaremos las instrucciones a cada estudiante.</p>

    <div class="mt-6 p-5 rounded-xl border border-green-200 bg-green-50">
        <p class="text-sm text-green-800">
            Nº de orden: <span class="font-semibold">{{ $order->code ?? ('#'.$order->id) }}</span> ·
            Estado: <span class="font-semibold">{{ strtoupper($order->status) }}</span>
        </p>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-4">
            @foreach ($order->items as $item)
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">{{ $item->course_name }}</div>
                        <div class="text-sm text-gray-600">x{{ $item->qty }}</div>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-sm font-medium text-gray-700">Estudiantes</h3>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach ($item->attendees as $a)
                                <li class="flex items-center justify-between">
                                    <span>{{ $a->name }} <span class="text-gray-500">&lt;{{ $a->email }}&gt;</span></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                                 {{ $a->status === 'enrolled' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $a->status }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <aside>
            <div class="p-4 rounded-xl border border-gray-200 bg-white">
                <h2 class="text-lg font-semibold">Resumen</h2>
                <div class="mt-4 flex items-center justify-between text-sm">
                    <span>Total pagado</span>
                    <span class="font-semibold">${{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('home') }}"
                   class="mt-4 block text-center px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                    Volver al inicio
                </a>
            </div>
        </aside>
    </div>
</div>
</x-layouts.app>
