<x-layouts.app>

    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#19355C]">
                    Mis certificados
                </h1>
                <p class="text-sm text-gray-600">
                    Estudiante: {{ $student->nombre }} {{ $student->apellido }} — RUT:
                    {{ \App\Models\Student::formatRut($student->rut) }}
                </p>
            </div>

            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold bg-gray-200 hover:bg-gray-300 text-gray-800 transition">
                    Cerrar sesión
                </button>
            </form>
        </div>

        @if ($diplomas->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-600">
                Aún no tienes diplomas emitidos asociados a tu cuenta.
            </div>
        @else
            <div class="grid gap-4">
                @foreach ($diplomas as $diploma)
                    <div
                        class="rounded-xl border border-gray-200 bg-white px-4 py-4 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-[#19355C]">
                                {{ $diploma->course->nombre ?? 'Curso' }}
                            </h2>
                            <p class="text-sm text-gray-600">
                                Emitido el: {{ optional($diploma->issued_at)->format('d-m-Y') ?? 'N/D' }}
                            </p>
                            @if ($diploma->verification_code)
                                <p class="text-xs text-gray-500">
                                    Código de verificación: {{ $diploma->verification_code }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('student.diplomas.download', $diploma) }}"
                                class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold bg-[#19355C] text-white hover:bg-[#142843] transition">
                                Descargar diploma
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</x-layouts.app>
