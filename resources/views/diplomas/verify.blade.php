<x-layouts.app>

    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#19355C]">
                    Verificación de certificado
                </h1>

                <p class="text-xl text-gray-600">
                    Este diploma fue <span class="font-semibold text-green-700">válidamente otorgado</span>
                     por <span class="font-semibold text-[#19355C]">OTEC MITCARE.</span>
                </p>

                <p class="text-sm text-gray-500 mt-1">
                    Código de verificación:
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">
                        {{ $diploma->verification_code }}
                    </span>
                </p>
            </div>

            {{-- Podrías agregar el logo del OTEC aquí si quisieras --}}
        </div>

        {{-- Tarjeta con información del diploma --}}
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-5 shadow-sm space-y-4">

            {{-- Estudiante --}}
            <div>
                <h2 class="font-semibold text-[#19355C] mb-1 text-xl">
                    Estudiante
                </h2>

                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Nombre:</span>
                    {{ $diploma->student->nombre }} {{ $diploma->student->apellido }}
                </p>

                @if (!empty($diploma->student->rut))
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">RUT:</span>
                        {{ \App\Models\Student::formatRut($diploma->student->rut) }}
                    </p>
                @endif
            </div>

            {{-- Curso --}}
            <div class="border-t border-gray-100 pt-4">
                <h2 class="font-semibold text-[#19355C] mb-1 text-xl">
                    Curso
                </h2>

                <p class="text-sm text-gray-700">
                    {{ $diploma->course->nombre ?? 'Curso' }}
                </p>

                @if (!empty($diploma->course->hours))
                    <p class="text-sm text-gray-600">
                        Carga horaria: {{ $diploma->course->hours }} horas
                    </p>
                @endif
            </div>

            {{-- Información del diploma --}}
            <div class="border-t border-gray-100 pt-4">
                <h2 class="font-semibold text-[#19355C] mb-1 text-xl">
                    Información del diploma
                </h2>

                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Emitido el:</span>
                    {{ optional($diploma->issued_at)->format('d-m-Y') ?? 'N/D' }}
                </p>

                {{-- @if (!is_null($diploma->final_grade))
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Nota final:</span> {{ number_format($diploma->final_grade, 2, ',', '.') }}
                    </p>
                @endif --}}
            </div>

            {{-- Opcional: botón para descargar, solo si quieres permitirlo también aquí --}}
            {{-- 
            <div class="border-t border-gray-100 pt-4 flex justify-end">
                <a href="{{ route('student.diplomas.download', $diploma) }}"
                    class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold bg-[#19355C] text-white hover:bg-[#142843] transition">
                    Descargar diploma (PDF)
                </a>
            </div>
            --}}
        </div>

        {{-- Pie informativo --}}
        <div class="mt-6 text-xs text-gray-500">
            <p>
                Este certificado fue emitido por
                <span class="font-semibold">OTEC MITCARE</span>.
            </p>
            @if (config('mail.from.address'))
                <p>
                    Si tienes dudas sobre la autenticidad de este diploma, puedes escribir a
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-[#19355C] underline">
                        {{ config('mail.from.address') }}
                    </a>.
                </p>
            @endif
        </div>
    </div>

</x-layouts.app>
