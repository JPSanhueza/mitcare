<div class="space-y-6">

    @if (empty($errors))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No se registraron errores en el último proceso de importación.
            </p>
        </x-filament::section>
    @else
        <x-filament::section class="rounded-xl shadow-sm">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Se encontraron
                <span class="font-bold text-primary-600">{{ count($errors) }}</span>
                errores. Revisa el detalle:
            </p>
        </x-filament::section>

        @php
            $labels = [
                'missing_columns' => [
                    'label' => 'Columnas faltantes',
                    'color' => 'danger',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'help' => 'El archivo no contiene todas las columnas mínimas requeridas.',
                ],
                'missing_required' => [
                    'label' => 'Datos faltantes',
                    'color' => 'warning',
                    'icon' => 'heroicon-o-information-circle',
                    'help' => 'Una o más celdas requeridas están vacías.',
                ],
                'invalid_rut' => [
                    'label' => 'RUT inválido',
                    'color' => 'danger',
                    'icon' => 'heroicon-o-x-circle',
                    'help' => 'El dígito verificador no coincide con un RUT válido.',
                ],
                'duplicate' => [
                    'label' => 'Duplicado',
                    'color' => 'warning',
                    'icon' => 'heroicon-o-arrow-path-rounded-square',
                    'help' => 'Ya existe un estudiante registrado con este RUT.',
                ],
                'validation' => [
                    'label' => 'Error de validación',
                    'color' => 'danger',
                    'icon' => 'heroicon-o-shield-exclamation',
                    'help' => 'Los datos no cumplen las reglas de validación.',
                ],
                'exception' => [
                    'label' => 'Error inesperado',
                    'color' => 'danger',
                    'icon' => 'heroicon-o-fire',
                    'help' => 'Ocurrió un error interno durante el procesamiento.',
                ],
                'empty_file' => [
                    'label' => 'Archivo vacío',
                    'color' => 'info',
                    'icon' => 'heroicon-o-document',
                    'help' => 'El archivo no contiene filas procesables.',
                ],
            ];
        @endphp

        <div class="rounded-xl border shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

                {{-- Encabezado --}}
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Fila
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Tipo
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Mensaje
                        </th>
                    </tr>
                </thead>

                {{-- Cuerpo --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900 text-sm">

                    @foreach ($errors as $error)
                        @php
                            $info = $labels[$error['type']] ?? [
                                'label' => $error['type'],
                                'color' => 'secondary',
                                'icon' => 'heroicon-o-question-mark-circle',
                                'help' => 'Sin información adicional.',
                            ];
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">

                            {{-- Fila --}}
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $error['row'] ?? '—' }}
                            </td>

                            {{-- Tipo: badge con icono + tooltip nativo --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center" title="{{ $info['help'] }}">
                                    <x-filament::badge size="lg" :color="$info['color']" :icon="$info['icon']">
                                        {{ $info['label'] }}
                                    </x-filament::badge>
                                </span>
                            </td>

                            {{-- Mensaje --}}
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $error['message'] }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
            Tip: También puedes revisar <code>storage/logs/laravel.log</code> para más detalles técnicos.
        </p> --}}

    @endif

</div>
