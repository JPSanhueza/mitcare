<x-layouts.app>
    @php
    $commit = data_get($order->meta, 'webpay_commit', []);
    $txDateRaw = data_get($commit, 'transaction_date');
    $txDate = $txDateRaw ? \Illuminate\Support\Carbon::parse($txDateRaw)->timezone(config('app.timezone')) : null;

    $paymentType = data_get($commit, 'payment_type'); // VD, VN, etc.
    $ptLabels = [
    'VD' => 'Débito',
    'VN' => 'Crédito (cuota normal)',
    'VC' => 'Crédito (cuotas)',
    'SI' => 'Crédito 3 cuotas sin interés',
    'S2' => 'Crédito 2 cuotas sin interés',
    'S3' => 'Crédito 3 cuotas sin interés',
    'NC' => 'Crédito N cuotas',
    'VP' => 'Prepago',
    ];
    $ptHuman = $ptLabels[$paymentType ?? ''] ?? 'Webpay Plus';

    // Map de estados para asistentes (en español)
    $attStatusMap = [
    'enrolled' => ['label' => 'Inscrito', 'class' => 'bg-green-100 text-green-800'],
    'pending' => ['label' => 'Pendiente', 'class' => 'bg-yellow-100 text-yellow-800'],
    'failed' => ['label' => 'Fallido', 'class' => 'bg-red-100 text-red-800'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-gray-100 text-gray-700'],
    ];

    // Función para iniciales
    $initials = function (?string $name): string {
    $name = trim((string)$name);
    if ($name === '') return '??';
    $parts = preg_split('/\s+/', $name);
    $ini = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) {
    $ini .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    }
    return $ini;
    };
    @endphp

    <div class="max-w-6xl mx-auto px-2 sm:px-4 py-10">
        {{-- Encabezado --}}
        <div class="flex items-start gap-3">
            <div class="shrink-0 mt-1">
                <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M12 2.25a9.75 9.75 0 1 0 9.75 9.75A9.76 9.76 0 0 0 12 2.25Zm4.28 7.22a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2.5-2.5a.75.75 0 0 1 1.06-1.06l1.97 1.97 4.47-4.47a.75.75 0 0 1 1.06 0Z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">¡Pago realizado con éxito!</h1>
                <p class="text-gray-600 mt-1">Hemos recibido tu pago. En breve enviaremos las instrucciones a cada
                    estudiante.</p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Detalle de compra --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="text-lg font-semibold">Detalle de la compra</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2 text-left">Curso</th>
                                    <th class="px-4 py-2 text-right">Precio</th>
                                    <th class="px-4 py-2 text-center">Cant.</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 break-words">{!! $item->course_name !!}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">${{ number_format($item->unit_price, 0, ',', '.')
                                        }}</td>
                                    <td class="px-4 py-3 text-center">x{{ $item->qty }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">${{ number_format($item->subtotal, 0,
                                        ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-700">Total pagado
                                    </td>
                                    <td class="px-4 py-3 text-right font-extrabold text-gray-900">
                                        ${{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Estudiantes (más amigable) --}}
                <div class="rounded-xl border border-gray-200 bg-white">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="text-lg font-semibold">Estudiantes</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium">{!! $item->course_name !!}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Cupos asignados: x{{ $item->qty }}</div>
                                </div>
                            </div>

                            <ul class="mt-4 space-y-3">
                                @foreach ($item->attendees as $a)
                                @php
                                $st = $attStatusMap[$a->status] ?? ['label' => ucfirst($a->status), 'class' =>
                                'bg-gray-100 text-gray-700'];
                                $ini = $initials($a->name);
                                @endphp
                                <li class="flex items-center justify-between gap-3 flex-wrap">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center text-xs font-bold border shrink-0">
                                            {{ $ini }}
                                        </div>
                                        <div class="leading-tight min-w-0">
                                            <div class="text-sm font-medium text-gray-900 break-words">
                                                {{ $a->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 break-all">
                                                {{ $a->email }}
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] px-2 py-1 rounded-full {{ $st['class'] }} shrink-0 whitespace-nowrap">
                                        {{ $st['label'] }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Resumen --}}
            <aside class="space-y-6">
                <div class="p-2 sm:p-5 rounded-xl border border-gray-200 bg-white">
                    <h2 class="text-lg font-semibold">Resumen</h2>

                    <dl class="mt-4 grid grid-cols-1 gap-y-2 text-sm sm:grid-cols-2">
                        {{-- <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Total pagado</dt>
                            <dd class="font-semibold whitespace-nowrap">
                                ${{ number_format($order->total, 0, ',', '.') }}
                            </dd>
                        </div> --}}

                        <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Comprador</dt>
                            <dd class="text-right break-words">{{ $order->buyer_name }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Correo</dt>
                            <dd class="text-right break-all">{{ $order->buyer_email }}</dd>
                        </div>

                        @if($txDate)
                        <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Fecha de transacción</dt>
                            <dd class="text-right break-all">{{ $txDate->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif

                        <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Medio de pago</dt>
                            <dd class="text-right break-words">{{ $ptHuman }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:col-span-2">
                            <dt class="text-gray-600">Nº de orden</dt>
                            <dd class="text-right break-all">{{ $order->code ?? ('#'.$order->id) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('home') }}"
                        class="mt-4 block w-full text-center px-4 py-2 rounded-lg bg-[#19355C] text-white font-semibold hover:bg-[#47A8DF] transition">
                        Volver al inicio
                    </a>
                </div>

                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50 text-sm text-blue-900">
                    <h3 class="font-semibold mb-1">¿Qué sigue?</h3>
                    <ul class="list-disc ml-4 space-y-1">
                        <li>Enviaremos un correo a cada estudiante con las instrucciones de acceso.</li>
                        <li>Si no te llega el correo en unos minutos, revisa SPAM o contáctanos.</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
