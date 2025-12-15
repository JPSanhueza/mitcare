@php
    // Closure para formatear el RUT (no se redeclara)
    $formatRut = function (?string $rut) {
        if (!$rut) return '';

        $rut = preg_replace('/[^0-9kK]/', '', $rut);

        $dv  = strtoupper(substr($rut, -1));
        $num = substr($rut, 0, -1);

        if ($num === '') {
            return $rut;
        }

        $num = number_format((int) $num, 0, ',', '.');

        return $num . '-' . $dv;
    };

    $isApproved   = (bool) $approved;
    $statusClass  = $isApproved ? 'diploma-student-row--ok' : 'diploma-student-row--bad';
    $statusText   = $isApproved ? 'Aprobado' : 'Reprobado';
    $formattedRut = $formatRut($rut ?? '');
@endphp


@once
<style>
    .diploma-student-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)); /* desktop */
        gap: 6px 16px;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        /* background-color: #f9fafb; */
        font-size: 13px;
        line-height: 1.4;
        box-sizing: border-box;
        margin-top: 6px;
    }

    .diploma-student-row-field-label {
        font-weight: 600;
        color: #111827;
        display: block;
        margin-bottom: 1px;
    }

    .diploma-student-row-field-value {
        color: #111827;
        word-break: break-word;
    }

    .diploma-student-row-status {
        grid-column: span 2;
        display: flex;
        align-items: center;
    }

    .diploma-student-row-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        min-width: 90px;
    }

    .diploma-student-row--ok {
        border-left: 4px solid #16a34a;
    }

    .diploma-student-row--ok .diploma-student-row-status-pill {
        background-color: #dcfce7;
        color: #166534;
    }

    .diploma-student-row--bad {
        border-left: 4px solid #dc2626;
    }

    .diploma-student-row--bad .diploma-student-row-status-pill {
        background-color: #fee2e2;
        color: #991b1b;
    }

    /* ====== Responsivo ====== */

    /* Tablets / pantallas medianas: 2 columnas */
    @media (max-width: 900px) {
        .diploma-student-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .diploma-student-row-status {
            grid-column: 1 / -1; /* ocupa todo el ancho */
            margin-top: 4px;
        }
    }

    /* Móviles angostos: 1 columna */
    @media (max-width: 600px) {
        .diploma-student-row {
            grid-template-columns: 1fr;
            gap: 4px 0;
        }

        .diploma-student-row-status {
            justify-content: flex-start;
        }
    }

    /* ====== Modo oscuro ====== */

    .dark .diploma-student-row {
        border-color: #2d2e2e;
        /* background-color: #111827; */
    }

    .dark .diploma-student-row-field-label {
        color: #e5e7eb;
    }

    .dark .diploma-student-row-field-value {
        color: #e5e7eb;
    }

    .dark .diploma-student-row--ok {
        border-left-color: #22c55e;
    }

    .dark .diploma-student-row--ok .diploma-student-row-status-pill {
        background-color: #14532d;
        color: #bbf7d0;
    }

    .dark .diploma-student-row--bad {
        border-left-color: #ef4444;
    }

    .dark .diploma-student-row--bad .diploma-student-row-status-pill {
        background-color: #7f1d1d;
        color: #fecaca;
    }
</style>
@endonce

<div class="diploma-student-row {{ $statusClass }}">
    {{-- Fila 1 (en desktop) --}}
    <div>
        <span class="diploma-student-row-field-label">Nombre:</span>
        <span class="diploma-student-row-field-value">{{ $name }}</span>
    </div>

    <div>
        <span class="diploma-student-row-field-label">RUT:</span>
        <span class="diploma-student-row-field-value">{{ $formattedRut }}</span>
    </div>

    <div>
        <span class="diploma-student-row-field-label">Nota final:</span>
        <span class="diploma-student-row-field-value">{{ $final_grade }}</span>
    </div>

    {{-- Fila 2 (se reordena en responsive por el grid) --}}
    <div>
        <span class="diploma-student-row-field-label">Asistencia:</span>
        <span class="diploma-student-row-field-value">
            {{ $attendance !== null ? $attendance . '%' : '-' }}
        </span>
    </div>

    <div class="diploma-student-row-status">
        <span class="diploma-student-row-status-pill">
            {{ $statusText }}
        </span>
    </div>
</div>
