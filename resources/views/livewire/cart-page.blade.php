<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Tu carrito</h1>
    <p class="text-gray-600 mt-1">Revisa los cursos añadidos antes de pagar.</p>

    @if ($count === 0)
    <div class="mt-8 p-6 rounded-xl border border-gray-200 bg-white text-center">
        <p class="text-gray-600">Tu carrito está vacío.</p>
        <a href="{{ route('home') }}"
            class="inline-block mt-4 px-4 py-2 rounded-lg bg-[#19355C] text-white hover:bg-[#47A8DF] transition">
            Seguir comprando
        </a>
    </div>
    @else
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Lista de ítems --}}
        <div class="lg:col-span-2 space-y-3">
            <div class="hidden md:block rounded-xl overflow-hidden border border-gray-200 bg-white">
                <table class="min-w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-3 px-4 text-left">Curso</th>
                            <th class="py-3 px-4 text-center w-24">Cant.</th>
                            <th class="py-3 px-4 text-right w-32">Precio</th>
                            <th class="py-3 px-4 text-right w-32">Subtotal</th>
                            <th class="py-3 px-4 w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach ($items as $item)
                        <tr wire:key="row-{{ $item['key'] }}">
                            <td class="py-3 px-4">
                                <div class="flex gap-3 items-center">
                                    @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" class="w-12 h-12 rounded object-cover border"
                                        alt="">
                                    @else
                                    <div
                                        class="w-12 h-12 rounded bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
                                        IMG</div>
                                    @endif
                                    <div>
                                        <div class="font-medium line-clamp-1">{{ $item['name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button class="px-2 py-1 border rounded disabled:opacity-50"
                                        wire:click="decrement('{{ $item['key'] }}')" @disabled($item['qty'] <=1)
                                        wire:loading.attr="disabled"
                                        wire:target="increment,decrement,updateQty,removeItem,clear"
                                        aria-label="Disminuir cantidad">−</button>

                                    <input type="number" min="1" value="{{ $item['qty'] }}"
                                        class="w-16 text-center border rounded py-1"
                                        wire:change="updateQty('{{ $item['key'] }}', $event.target.value)"
                                        wire:key="qty-input-{{ $item['key'] }}">

                                    <button class="px-2 py-1 border rounded"
                                        wire:click="increment('{{ $item['key'] }}')" wire:loading.attr="disabled"
                                        wire:target="increment,decrement,updateQty,removeItem,clear"
                                        aria-label="Aumentar cantidad">+</button>
                                </div>
                            </td>

                            <td class="py-3 px-4 text-right">${{ number_format($item['price'],0,',','.') }}</td>
                            <td class="py-3 px-4 text-right font-semibold">${{
                                number_format($item['subtotal'],0,',','.') }}</td>
                            <td class="py-3 px-2 text-right">
                                <button wire:click="removeItem('{{ $item['key'] }}')"
                                    class="p-1 rounded hover:bg-gray-100" title="Quitar">
                                    <x-heroicon-o-trash class="w-5 h-5 text-gray-500" />
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Versión móvil (cards) --}}
            <div class="md:hidden space-y-3">
                @foreach ($items as $item)
                <div class="p-3 rounded-xl border border-gray-200 bg-white gap-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 items-center"
                    wire:key="card-{{ $item['key'] }}">
                    @if(!empty($item['image']))
                    <img src="{{ $item['image'] }}" class="w-14 h-14 rounded object-cover border" alt="">
                    @else
                    <div
                        class="w-14 h-14 rounded bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
                        IMG</div>
                    @endif
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $item['name'] }}</div>
                        <div class="text-xs text-gray-500">
                            <div class="inline-flex items-center gap-1 mt-1">
                                <button class="px-2 py-1 border rounded disabled:opacity-50"
                                    wire:click="decrement('{{ $item['key'] }}')" @disabled($item['qty'] <=1)"
                                    wire:loading.attr="disabled"
                                    wire:target="increment,decrement,updateQty,removeItem,clear">−</button>

                                <input type="number" min="1" value="{{ $item['qty'] }}"
                                    class="w-16 text-center border rounded py-1"
                                    wire:change="updateQty('{{ $item['key'] }}', $event.target.value)"
                                    wire:key="qty-input-mobile-{{ $item['key'] }}">

                                <button class="px-2 py-1 border rounded" wire:click="increment('{{ $item['key'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="increment,decrement,updateQty,removeItem,clear">+</button>
                            </div>
                        </div>

                        <div class="text-sm font-semibold mt-1">${{ number_format($item['subtotal'],0,',','.') }}</div>
                    </div>
                    <button wire:click="removeItem('{{ $item['key'] }}')"
                        class="self-start p-1 rounded hover:bg-gray-100" title="Quitar">
                        <x-heroicon-o-trash class="w-5 h-5 text-gray-500" />
                    </button>
                </div>
                @endforeach
            </div>

            <div>
                <button wire:click="clear" class="text-sm text-red-600 hover:text-red-700 underline">
                    Vaciar carrito
                </button>
            </div>
        </div>

        {{-- Resumen --}}
        <aside class="lg:col-span-1">
            <div class="p-4 rounded-xl border border-gray-200 bg-white space-y-3">
                <h2 class="font-semibold">Resumen</h2>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Artículos</span>
                    <span class="font-medium">{{ $count }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold">${{ number_format($subtotal,0,',','.') }}</span>
                </div>
                <button wire:click="checkout"
                    class="w-full mt-2 px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                    Ir a pagar
                </button>
                <a href="{{ route('home') }}" class="block text-center text-sm text-[#19355C] hover:underline">
                    Seguir comprando
                </a>
            </div>
        </aside>
    </div>
    @endif
</div>
