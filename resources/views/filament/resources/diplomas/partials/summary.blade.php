@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    function format_rut($rut)
    {
        if (!$rut) {
            return '';
        }

        $rut = preg_replace('/[^0-9kK]/', '', $rut);

        $dv = strtoupper(substr($rut, -1));
        $num = substr($rut, 0, -1);

        if ($num === '') {
            return $rut; // fallback raro
        }

        $num = number_format((int) $num, 0, ',', '.');

        return $num . '-' . $dv;
    }
@endphp

<style>
    /* ===========================
       ESTILOS BASE (MODO CLARO)
       =========================== */

    .diploma-summary {
        font-size: 14px;
        line-height: 1.5;
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 4px;
    }

    .diploma-summary * {
        box-sizing: border-box;
    }

    .diploma-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        background-color: #f3f4f6;
        color: #111827;
    }

    .diploma-summary-card h3 {
        margin: 0 0 8px 0;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
    }

    .diploma-summary-card p {
        margin: 2px 0;
    }

    .diploma-summary-muted {
        color: #6b7280;
        font-size: 12px;
    }

    .diploma-summary-strong {
        font-weight: 600;
    }

    .diploma-summary-status-ok {
        color: #16a34a;
        font-size: 12px;
        font-weight: 600;
    }

    .diploma-summary-status-bad {
        color: #dc2626;
        font-size: 12px;
        font-weight: 600;
    }

    .diploma-signature-preview {
        margin-top: 6px;
        max-height: 60px;
        max-width: 220px;
        object-fit: contain;
        display: block;
    }

    .diploma-summary-table-wrapper {
        width: 100%;
        overflow-x: auto;
        margin-top: 8px;
    }

    .diploma-summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        background-color: #ffffff;
    }

    .diploma-summary-table thead {
        background-color: #e5e7eb;
    }

    .diploma-summary-table th,
    .diploma-summary-table td {
        padding: 6px 8px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        color: #111827;
    }

    .diploma-summary-table th {
        font-weight: 600;
    }

    .diploma-summary-table tr:nth-child(even) td {
        background-color: #f9fafb;
    }

    .diploma-summary-table tr:last-child td {
        border-bottom: none;
    }

    .diploma-summary-date {
        font-size: 12px;
        color: #6b7280;
    }

    .diploma-summary-background-signature {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 2px;
        width: fit-content;
        display: flex;
        align-items: center;
    }

    .diploma-summary-total {
        margin-bottom: 4px;
    }

    /* ===========================
       MODO OSCURO
       =========================== */

    .dark .diploma-summary-card {
        border-color: #4f4f50;
        background-color: #252525;
        color: #e5e7eb;
    }

    .dark .diploma-summary-card h3 {
        color: #f9fafb;
    }

    .dark .diploma-summary-muted {
        color: #9ca3af;
    }

    .dark .diploma-summary-table {
        background-color: #111111;
    }

    .dark .diploma-summary-table thead {
        background-color: #323336;
    }

    .dark .diploma-summary-table th,
    .dark .diploma-summary-table td {
        border-bottom-color: #323437;
        color: #e5e7eb;
    }

    .dark .diploma-summary-table tr:nth-child(even) td {
        background-color: #191a1b;
    }

    .dark .diploma-summary-date {
        color: #9ca3af;
    }

    .dark .diploma-summary-background-signature {
        background-color: #cbcbcb;
        border-radius: 15px;
        padding: 2px;
        width: fit-content;
        display: flex;
        align-items: center;
    }

    .dark .diploma-summary-status-ok {
        color: #16a34a;
    }

    .dark .diploma-summary-status-bad {
        color: #fca5a5;
    }
</style>

<div class="diploma-summary">
    {{-- Curso --}}
    <div class="diploma-summary-card">
        <h3>Curso seleccionado</h3>

        @if ($course)
            <p><span class="diploma-summary-strong">Nombre:</span> {{ $course->nombre }}</p>
            <p><span class="diploma-summary-strong">Modalidad:</span> {{ ucfirst($course->modality) }}</p>
            <p><span class="diploma-summary-strong">Horas totales:</span> {{ $course->total_hours }}</p>
            <p><span class="diploma-summary-strong">Detalle horas:</span> {{ $course->hours_description }}</p>
        @else
            <p class="diploma-summary-muted">No hay curso seleccionado.</p>
        @endif
    </div>

    {{-- Docentes --}}
    <div class="diploma-summary-card">
        <h3>Docentes</h3>

        @if ($teachers && $teachers->count())
            @php
                // Número máximo de columnas (máximo 4, pero puede ser 1–4 según docentes)
                $cols = min($teachers->count(), 4);
            @endphp

            <div class="grid gap-6 mt-2"
                style="display: grid; grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">

                @foreach ($teachers as $teacher)
                    <div class="flex flex-col items-center text-center">

                        {{-- Nombre --}}
                        <p class="diploma-summary-strong mb-1">
                            {{ $teacher->nombre }} {{ $teacher->apellido }}
                        </p>

                        {{-- Firma --}}
                        @php
                            $signatureUrl = null;
                            $hasSignature = false;

                            if (!empty($teacher->signature)) {
                                $path = $teacher->signature;
                                $signatureUrl = Storage::disk('public')->url($path);
                                $hasSignature = true;
                            }
                        @endphp

                        @if ($hasSignature && $signatureUrl)
                            <div class="diploma-summary-background-signature mx-auto">
                                <img src="{{ $signatureUrl }}" alt="Firma docente" class="diploma-signature-preview">
                            </div>
                        @else
                            <p class="diploma-summary-status-bad text-xs mt-2">
                                (Sin firma)
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="diploma-summary-muted">No se han seleccionado docentes.</p>
        @endif
    </div>



    {{-- Estudiantes --}}
    <div class="diploma-summary-card">
        <h3>Estudiantes seleccionados para diploma</h3>

        @if ($students->isEmpty())
            <p class="diploma-summary-muted">No hay estudiantes seleccionados.</p>
        @else
            <p class="diploma-summary-total">
                Total: <span class="diploma-summary-strong">{{ $students->count() }}</span>
            </p>

            <div class="diploma-summary-table-wrapper">
                <table class="diploma-summary-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>RUT</th>
                            <th>Nota final</th>
                            <th>Aprobado</th>
                            <th>Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $s)
                            <tr>
                                <td>{{ $s['name'] ?? '' }}</td>
                                <td>{{ format_rut($s['rut'] ?? '') }}</td>
                                <td>{{ $s['final_grade'] ?? '-' }}</td>
                                <td>{{ !empty($s['approved']) ? 'Aprobado' : 'Reprobado' }}</td>
                                <td>{{ isset($s['attendance']) ? $s['attendance'] . '%' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
