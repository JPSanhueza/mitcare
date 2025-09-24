@props([
// Puedes pasar la Order completa o solo los campos necesarios
'order' => null, // Eloquent Order (opcional)
'status' => null, // 'failed' | 'pending' | ...
'orderCode' => null, // Ej: 'ORD-123456'
])

@php
$resolvedStatus = $status ?? ($order->status ?? null);
$resolvedOrder = $orderCode ?? ($order->code ?? $order->order_code ?? null); // Ajusta a tu columna
$shouldShow = in_array($resolvedStatus, ['failed','pending'], true);
$supportEmail = config('mail.from.address');
@endphp

@if ($shouldShow)
<div class="rounded-xl border border-amber-300 bg-amber-50 p-4 sm:p-6 text-amber-900">
    <div class="flex items-start gap-3">
        <svg class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M11 7h2v6h-2V7zm0 8h2v2h-2v-2z" />
            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477
                10 10 10 10-4.477 10-10S17.523 2 12 2zM4 12c0-4.418 3.582-8 8-8s8
                3.582 8 8-3.582 8-8 8-8-3.582-8-8z" clip-rule="evenodd" />
        </svg>

        <div class="space-y-3">
            <h3 class="text-base sm:text-lg font-semibold">
                Tu pago podría estar aprobado, pero la transacción figura como “{{ $resolvedStatus }}”.
            </h3>

            <p class="text-sm sm:text-base leading-relaxed">
                Ocurrió un problema al confirmar la transacción en el sistema. Si realizaste el pago con éxito en
                Transbank, por favor sigue estos pasos para que podamos validar manualmente tu compra.
            </p>

            <ol class="list-decimal ms-5 space-y-2 text-sm sm:text-base">
                <li>
                    Escribe un correo a
                    <a href="mailto:{{ $supportEmail }}" class="underline font-medium">{{ $supportEmail }}</a>.
                </li>
                <li>
                    Adjunta el <span class="font-semibold">comprobante de pago de Transbank</span>
                    (captura o PDF).
                </li>
                <li>
                    Incluye tu <span class="font-semibold">número de orden</span>
                    @if($resolvedOrder)
                    <span>(por ejemplo: <code
                            class="px-1 py-0.5 bg-amber-100 rounded">{{ $resolvedOrder }}</code>)</span>
                    @else
                    <span>(formato: <code class="px-1 py-0.5 bg-amber-100 rounded">ORD-XXXXXX</code>)</span>
                    @endif
                    . Debe comenzar con <span class="font-semibold">ORD-</span>.
                </li>
            </ol>

            <details class="mt-2">
                <summary class="cursor-pointer text-sm text-amber-800 underline">Consejos</summary>
                <ul class="list-disc ms-6 mt-2 text-sm text-amber-900">
                    <h3 class="text-base sm:text-lg font-semibold">
                        Tu pago podría estar aprobado, pero la transacción figura como “{{ $resolvedStatus }}”.
                    </h3>

                    <p class="text-sm sm:text-base leading-relaxed">
                        Ocurrió un problema al confirmar la transacción en el sistema. Si realizaste el pago con éxito
                        en
                        Transbank, por favor sigue estos pasos para que podamos validar manualmente tu compra.
                    </p>

                    <ol class="list-decimal ms-5 space-y-2 text-sm sm:text-base">
                        <li>
                            Escribe un correo a
                            <a href="mailto:{{ $supportEmail }}" class="underline font-medium">{{ $supportEmail }}</a>.
                        </li>
                        <li>
                            Adjunta el <span class="font-semibold">comprobante de pago de Transbank</span>
                            (captura o PDF).
                        </li>
                        <li>
                            Incluye tu <span class="font-semibold">número de orden</span>
                            @if($resolvedOrder)
                            <span>(por ejemplo: <code
                                    class="px-1 py-0.5 bg-amber-100 rounded">{{ $resolvedOrder }}</code>)</span>
                            @else
                            <span>(formato: <code class="px-1 py-0.5 bg-amber-100 rounded">ORD-XXXXXX</code>)</span>
                            @endif
                            . Debe comenzar con <span class="font-semibold">ORD-</span>.
                        </li>
                    </ol>

                    <li>Revisa tu correo por un eventual comprobante de Transbank.</li>
                    <li>Si no ves tu orden, anota el <em>token</em> o ID de Transbank si lo tienes.</li>
                    <li>No intentes pagar de nuevo sin confirmar con soporte para evitar cargos duplicados.</li>
                </ul>
            </details>

            {{-- Acciones rápidas con Alpine (opcional) --}}
            {{-- <div x-data="{ copied:false }" class="flex flex-wrap gap-3 pt-2">
                <button type="button" class="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm"
                    @click="navigator.clipboard.writeText('{{ $supportEmail }}'); copied=true; setTimeout(()=>copied=false,1500)">
                    Copiar correo de soporte
                </button>

                @if($resolvedOrder)
                <button type="button"
                    class="px-3 py-2 rounded-lg bg-white text-amber-900 border border-amber-300 hover:bg-amber-100 text-sm"
                    @click="navigator.clipboard.writeText('{{ $resolvedOrder }}'); copied=true; setTimeout(()=>copied=false,1500)">
                    Copiar número de orden
                </button>
                @endif

                <span x-show="copied" class="text-sm text-amber-700">¡Copiado!</span>
            </div> --}}
        </div>
    </div>
</div>
@endif
