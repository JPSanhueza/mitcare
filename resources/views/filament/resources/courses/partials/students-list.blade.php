<div class="space-y-4">
    @if ($students->isEmpty())
        <p class="text-sm text-gray-500">
            Este curso no tiene estudiantes inscritos aún.
        </p>
    @else
        <p class="text-sm text-gray-600">
            Total estudiantes: <span class="font-semibold">{{ $students->count() }}</span>
        </p>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Nombre</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">RUT</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Inscrito</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Nota final</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Aprobado</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Asistencia (%)</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Diploma</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($students as $student)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $student->nombre }} {{ $student->apellido }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $student->email }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $student->rut }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ optional($student->pivot->enrolled_at)->format('d-m-Y H:i') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $student->pivot->final_grade !== null ? number_format($student->pivot->final_grade, 1, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if ($student->pivot->approved)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                        ✔ Aprobado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                        ✘ Reprobado
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $student->pivot->attendance !== null ? $student->pivot->attendance.'%' : '-' }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if ($student->pivot->diploma_issued)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                        🎓 Emitido
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
