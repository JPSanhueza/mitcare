<div class="space-y-6 text-sm">
    {{-- Curso --}}
    <div class="border rounded-lg p-4 bg-gray-50">
        <h3 class="text-base font-semibold text-gray-800 mb-2">
            Curso seleccionado
        </h3>

        @if ($course)
            <p><span class="font-semibold">Nombre:</span> {{ $course->nombre }}</p>
            <p><span class="font-semibold">Modalidad:</span> {{ ucfirst($course->modality) }}</p>
            <p><span class="font-semibold">Horas totales:</span> {{ $course->total_hours }}</p>
            <p><span class="font-semibold">Detalle horas:</span> {{ $course->hours_description }}</p>
        @else
            <p class="text-gray-500">No hay curso seleccionado.</p>
        @endif
    </div>

    {{-- Docente --}}
    <div class="border rounded-lg p-4 bg-gray-50">
        <h3 class="text-base font-semibold text-gray-800 mb-2">
            Docente
        </h3>

        @if ($teacher)
            <p><span class="font-semibold">Nombre:</span> {{ $teacher->nombre }} {{ $teacher->apellido }}</p>
            <p><span class="font-semibold">Email:</span> {{ $teacher->email }}</p>
            <p class="text-xs text-gray-500 mt-1">
                Recuerda que este docente debe tener cargada su firma (signature) para el diploma.
            </p>
        @else
            <p class="text-gray-500">No hay docente seleccionado.</p>
        @endif
    </div>

    {{-- Estudiantes --}}
    <div class="border rounded-lg p-4 bg-gray-50">
        <h3 class="text-base font-semibold text-gray-800 mb-2">
            Estudiantes seleccionados para diploma
        </h3>

        @if ($students->isEmpty())
            <p class="text-gray-500">No hay estudiantes seleccionados.</p>
        @else
            <p class="mb-2">
                Total: <span class="font-semibold">{{ $students->count() }}</span>
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Nombre</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">RUT</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Nota final</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Aprobado</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($students as $s)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $s['name'] ?? '' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $s['rut'] ?? '' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $s['final_grade'] ?? '-' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ !empty($s['approved']) ? 'Aprobado' : 'Reprobado' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ isset($s['attendance']) ? $s['attendance'].'%' : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Fecha --}}
    @if ($issued_at)
        <p class="text-xs text-gray-500">
            Fecha de emisión seleccionada:
            <span class="font-semibold">
                {{ \Carbon\Carbon::parse($issued_at)->format('d-m-Y') }}
            </span>
        </p>
    @endif
</div>
