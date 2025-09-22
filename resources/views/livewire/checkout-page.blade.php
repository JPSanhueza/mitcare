<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Checkout</h1>
    <p class="text-gray-600 mt-1">Ingresa tus datos y confirma tu compra.</p>

    @if ($count === 0)
        <div class="mt-8 p-6 rounded-xl border bg-white text-center">
            <p class="text-gray-600">Tu carrito está vacío.</p>
            <a href="{{ route('home') }}"
               class="mt-4 inline-block px-4 py-2 bg-[#19355C] text-white rounded-lg hover:bg-[#47A8DF]">
                Seguir comprando
            </a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Formulario --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Datos del cliente --}}
                <div class="p-4 border rounded-xl bg-white space-y-4">
                    <h2 class="font-semibold text-lg">Datos del comprador</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" wire:model.live="name"
                               class="mt-1 block w-full p-1.5 border-gray-300 rounded-sm shadow-sm"
                               placeholder="Tu nombre completo">
                        @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                        <input type="email" wire:model.live="email"
                               class="mt-1 block w-full p-1.5 border-gray-300 rounded-sm shadow-sm"
                               placeholder="tu@correo.com">
                        @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button wire:click="checkout"
                        wire:loading.attr="disabled"
                        class="w-full px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                    Confirmar y pagar
                </button>
            </div>

            {{-- Resumen --}}
            <aside class="lg:col-span-1">
                <div class="p-4 border rounded-xl bg-white">
                    <h2 class="font-semibold text-lg">Resumen</h2>
                    <div class="divide-y divide-gray-100 mt-3">
                        @foreach ($items as $item)
                            <div class=" justify-between py-2 text-sm grid grid-cols-1 md:grid-cols-2"
                                 wire:key="summary-item-{{ $item['key'] }}">
                                <span>{{ $item['qty'] }}x {{ $item['name'] }}</span>
                                <span>${{ number_format($item['subtotal'],0,',','.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between mt-4 font-semibold text-gray-900">
                        <span>Total</span>
                        <span>${{ number_format($subtotal,0,',','.') }}</span>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
