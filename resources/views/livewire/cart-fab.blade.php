<div class="fixed z-[70]
     right-2 sm:right-3 md:right-4
     bottom-[calc(1rem+env(safe-area-inset-bottom))]
     lg:bottom-auto lg:top-[145px] 2xl:top-[68px]">

    {{-- Botón FAB --}}
    <button wire:click="toggle" class="relative flex items-center justify-center w-12 sm:w-14 h-12 sm:h-14 rounded-full shadow-lg
                   bg-[#47A8DF] text-white focus:outline-none focus:ring-4 focus:ring-[#47A8DF] transition duration-500
                   lg:opacity-100" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="cart-popover"
        data-fab-btn>
        <img src="{{ asset('img/icons/cart-white.png') }}" alt="Carrito" class="w-9 sm:w-11 h-9 sm:h-11">
        @if ($count > 0)
        <span
            class="absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1 rounded-full bg-red-600 text-white text-[11px] font-semibold flex items-center justify-center shadow ring-2 ring-white">
            {{ $count }}
        </span>
        @endif
    </button>

    {{-- Overlay para cerrar al hacer click fuera (solo si está abierto) --}}
    @if ($open)
    <button type="button" wire:click="close" class="fixed inset-0 z-[60] cursor-default bg-transparent"
        aria-hidden="true" tabindex="-1"></button>
    @endif

    {{-- Popover --}}
    @if ($open)
    <div id="cart-popover" class="absolute w-[min(92vw,380px)] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-[80]
                    bottom-full mb-2 right-0
                    lg:bottom-auto lg:mb-0 lg:top-full lg:mt-2 lg:right-0
                    origin-bottom-right lg:origin-top-right" role="dialog" aria-label="Contenido del carrito">
        <div class="p-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold">Tu carrito</h3>
            <button wire:click="close" class="p-1 rounded hover:bg-gray-100" aria-label="Cerrar">
                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @if ($count === 0)
        <div class="p-4 text-sm text-gray-600">Tu carrito está vacío.</div>
        @else
        <div class="max-h-[60vh] overflow-auto divide-y divide-gray-100">
            @foreach ($items as $item)
            <div class="p-3 flex gap-3" wire:key="cart-item-{{ $item['key'] }}">
                @if (!empty($item['image']))
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                    class="w-12 h-12 rounded object-cover border" />
                @else
                <div
                    class="w-12 h-12 rounded bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
                    IMG</div>
                @endif

                <div class="flex-1">
                    <div class="text-sm font-medium line-clamp-1">{{ $item['name'] }}</div>
                    <div class="text-xs text-gray-500">x{{ $item['qty'] }}</div>
                    <div class="text-sm font-semibold mt-1">${{ number_format($item['subtotal'],0,',','.') }}</div>
                </div>

                {{-- Remover item (Livewire directo) --}}
                <button type="button" wire:click="removeItem('{{ $item['key'] }}')" wire:loading.attr="disabled"
                    class="self-start p-1 rounded hover:bg-gray-100" title="Quitar">
                    <x-heroicon-o-trash class="h-5 w-5 text-gray-500" />
                </button>
            </div>
            @endforeach
        </div>

        <div class="p-3 border-t border-gray-100 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold">${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                {{-- Link nativo para evitar carreras de eventos --}}
                <a href="{{ route('cart.index') }}" wire:navigate
                    class="px-3 py-2 rounded-lg border text-sm font-medium hover:bg-gray-50 text-center">
                    Ver carrito
                </a>

                {{-- Si tienes checkout por Livewire, deja wire:click. Si es ruta, usa <a>. --}}
                    <button wire:click="checkout"
                        class="px-3 py-2 rounded-lg bg-[#19355C] text-white text-sm font-semibold hover:bg-[#47A8DF] transition">
                        Pagar
                    </button>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('[data-fab-btn]');
    if (!btn) return;

    let timer;
    const isMobile = () => window.innerWidth < 1024;

    const bumpFade = () => {
        clearTimeout(timer);
        if (!isMobile()) { btn.classList.remove('opacity-40'); return; }
        btn.classList.remove('opacity-40');
        timer = setTimeout(() => btn.classList.add('opacity-40'), 4000);
    };

    bumpFade();
    const onActivity = () => bumpFade();
    const onResize = () => { bumpFade(); };

    window.addEventListener('resize', onResize);
    window.addEventListener('scroll', onActivity, { passive: true });
    window.addEventListener('touchstart', onActivity, { passive: true });
    window.addEventListener('mousemove', onActivity, { passive: true });
    window.addEventListener('keydown', onActivity);
});
</script>
