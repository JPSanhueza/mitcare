{{-- resources/views/livewire/checkout-page.blade.php --}}
<div class="max-w-6xl mx-auto px-4 py-10">
    {{-- Encabezado --}}
    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Resumen de compra</h1>
    <p class="text-gray-600 mt-1">Completa los datos y confirma tu compra.</p>

    {{-- Stepper --}}
    @php
    // Propiedad esperada en el componente: public int $step = 1; (1..3)
    $s1 = ($step ?? 1) >= 1;
    $s2 = ($step ?? 1) >= 2;
    $s3 = ($step ?? 1) >= 3;
    @endphp

    <ol class="mt-6 flex items-center gap-4 text-sm">
        <li class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center
                        {{ $s1 ? 'bg-[#19355C] text-white' : 'bg-gray-200 text-gray-600' }}">
                1
            </div>
            <span class="font-medium {{ $s1 ? 'text-[#19355C]' : 'text-gray-500' }}">Información de compra</span>
        </li>
        <li class="flex-1 h-px bg-gray-200"></li>
        <li class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center
                        {{ $s2 ? 'bg-[#19355C] text-white' : 'bg-gray-200 text-gray-600' }}">
                2
            </div>
            <span class="font-medium {{ $s2 ? 'text-[#19355C]' : 'text-gray-500' }}">Estudiantes</span>
        </li>
        <li class="flex-1 h-px bg-gray-200"></li>
        <li class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center
                        {{ $s3 ? 'bg-[#19355C] text-white' : 'bg-gray-200 text-gray-600' }}">
                3
            </div>
            <span class="font-medium {{ $s3 ? 'text-[#19355C]' : 'text-gray-500' }}">Pago</span>
        </li>
    </ol>

    {{-- Grid principal --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Columna izquierda: contenido por paso --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Paso 1: Datos comprador + correo comprobante --}}
            @if (($step ?? 1) === 1)
            <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-6">
                <h2 class="text-lg font-semibold">Datos del comprador</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" wire:model.live="buyer_name" placeholder="Nombre y apellido"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-1.5">
                        @error('buyer_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo</label>
                        <input type="email" wire:model.live="buyer_email" placeholder="comprador@correo.com"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-1.5">
                        @error('buyer_email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        El comprobante de pago se enviará al correo del comprador.
                    </p>
                </div>
            </div>
            @endif

            {{-- Paso 2: Estudiantes por ítem (repetidor por cantidad) --}}
            @if (($step ?? 1) === 2)
            <div class="space-y-6">
                <div class="p-5 rounded-xl border border-gray-200 bg-white">
                    <h2 class="text-lg font-semibold">Asigna estudiantes</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Por cada curso comprado, indica el nombre y correo de cada estudiante.
                    </p>
                </div>

                @forelse ($items ?? [] as $item)
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50 space-y-4"
                    wire:key="course-{{ $item['key'] }}">
                    {{-- Encabezado del curso --}}
                    <div class="flex items-start gap-4">
                        @if(!empty($item['image']))
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                            class="w-14 h-14 rounded object-cover border">
                        @else
                        <div
                            class="w-14 h-14 rounded bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
                            IMG</div>
                        @endif
                        <div class="flex-1">
                            <div class="font-semibold">{{ $item['name'] }}</div>
                            <div class="text-sm text-gray-500">
                                Cantidad comprada: <span class="font-medium">x{{ $item['qty'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Repetidor: tantas filas como qty --}}
                    <div class="space-y-3">
                        @for ($i = 0; $i < (int) $item['qty']; $i++) <div class="grid grid-cols-12 gap-3 items-end"
                            wire:key="attendee-{{ $item['key'] }}-{{ $i }}-v{{ $attVersion }}">
                            <div class="col-span-12 sm:col-span-5">
                                <label class="block text-xs font-medium text-gray-700">Nombre del estudiante #{{ $i+1
                                    }}</label>
                                <input type="text" wire:model="attendees.{{ $item['key'] }}.{{ $i }}.name"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-1.5">
                                @error("attendees.{$item['key']}.{$i}.name")
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-span-12 sm:col-span-5">
                                <label class="block text-xs font-medium text-gray-700">Correo del estudiante</label>
                                <input type="email" wire:model="attendees.{{ $item['key'] }}.{{ $i }}.email"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-1.5">
                                @error("attendees.{$item['key']}.{$i}.email")
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-span-12 sm:col-span-2">
                                <button type="button" class="w-full px-3 py-2 text-xs rounded-md border hover:bg-white"
                                    wire:click="useBuyerFor('{{ $item['key'] }}', {{ $i }})"
                                    wire:loading.attr="disabled" wire:target="useBuyerFor">
                                    Usar datos del comprador
                                </button>
                                {{-- debug temporal --}}
                                {{--
                                <pre
                                    class="text-xs text-gray-500">{{ json_encode($attendees[$item['key']] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                --}}
                            </div>
                    </div>
                    @endfor
                </div>
            </div>
            @empty
            <div class="p-6 rounded-xl border border-gray-200 bg-white text-center">
                <p class="text-gray-600">No hay cursos en el carrito.</p>
            </div>
            @endforelse
        </div>
        @endif

        {{-- Paso 3: Método de pago --}}
        @if (($step ?? 1) === 3)
        <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-6">
            <h2 class="text-lg font-semibold">Método de pago</h2>
            {{-- En este proyecto solo tendremos el metodo de pago webpay, por lo que ya no debe estar payku y debe
            estar marcada por defecto webpay --}}
            <div class="space-y-3">
                <label class="flex items-center bg-gray-100 p-4 rounded-lg border cursor-pointer">
                    <input type="radio" name="payment_method" value="webpayplus" class="mr-3"
                        wire:model="payment_method">
                    <img src="{{ asset('img/webpay-logo.png') }}" alt="WebPay Plus" class="h-8">
                </label>
            </div>

            <label class="flex items-start gap-3">
                <input type="checkbox" class="mt-1" wire:model.live="terms_accepted">
                <span class="text-sm text-gray-700">
                    Acepto los <a href="{{ url('/terminos') }}" target="_blank"
                        class="text-[#19355C] underline">Términos y Condiciones</a>.
                </span>
            </label>
            @error('terms_accepted') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Navegación de pasos --}}
        <div class="flex items-center justify-between">
            <div>
                @if (($step ?? 1) > 1)
                <button type="button" class="px-4 py-2 rounded-lg border text-sm font-medium hover:bg-gray-50"
                    wire:click="prevStep">
                    Volver
                </button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if (($step ?? 1) < 3) <button type="button" class="px-4 py-2 rounded-lg
                bg-[#19355C] text-white text-sm font-semibold hover:bg-[#47A8DF] transition
                disabled:opacity-50 disabled:cursor-not-allowed" wire:click="nextStep" wire:target="nextStep">
                    Continuar
                    </button>
                    @else
                    <form wire:submit.prevent="confirmAndPay">
                        @if ($errors->any())
                        <div class="p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-700">
                            Hay errores en pasos anteriores. Revisa los campos marcados.
                        </div>
                        @endif
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#19355C] text-white text-sm font-semibold hover:bg-[#47A8DF] transition
                   data-[off=true]:opacity-50 data-[off=true]:cursor-not-allowed"
                            data-off="{{ $this->canPay ? 'false' : 'true' }}">
                            <span wire:loading.remove wire:target="confirmAndPay">Confirmar y pagar</span>
                            <span wire:loading wire:target="confirmAndPay">Procesando…</span>
                        </button>
                    </form>

                    @endif
                    @if ($errors->any())
                    <div class="p-3 rounded-md bg-red-50 border border-red-200 text-sm text-red-700">
                        <ul class="list-disc ml-5">
                            @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

            </div>
        </div>
    </div>

    {{-- Columna derecha: Resumen --}}
    <aside class="lg:col-span-1">
        <div class="lg:sticky lg:top-6">
            <div class="p-5 rounded-xl border border-gray-200 bg-white">
                <h2 class="text-lg font-semibold">Resumen</h2>

                @if (($count ?? 0) === 0)
                <div class="mt-4 text-sm text-gray-600">
                    No hay cursos en el carrito.
                </div>
                @else
                <div class="divide-y divide-gray-100 mt-4">
                    @foreach ($items ?? [] as $item)
                    <div class="flex justify-between py-3 text-sm">
                        <div class="pr-2">
                            <div class="font-medium line-clamp-2">{{ $item['name'] }}</div>
                            <div class="text-gray-500">x{{ $item['qty'] }}</div>
                        </div>
                        <div class="text-right font-semibold">
                            ${{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-4 text-base font-semibold text-gray-900">
                    <span>Total</span>
                    <span>${{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('cart.index') }}"
                    class="block text-center text-sm text-[#19355C] hover:underline mt-3">
                    Volver al carrito
                </a>
                @endif
            </div>
        </div>
    </aside>
</div>
</div>
