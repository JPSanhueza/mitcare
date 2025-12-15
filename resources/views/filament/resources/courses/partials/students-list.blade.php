<div>
    <style>
        /* ===========================
       LISTADO DE ESTUDIANTES
       (COHERENTE CON diploma-summary)
       =========================== */

        .students-wrapper {
            font-size: 14px;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 4px;
        }

        .students-wrapper * {
            box-sizing: border-box;
        }

        .students-empty-text {
            color: #6b7280;
            /* igual que .diploma-summary-muted */
            font-size: 12px;
            margin: 0;
        }

        .students-count-text {
            margin: 0;
            font-size: 13px;
            color: #111827;
        }

        .students-count-number {
            font-weight: 600;
        }

        .students-table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-top: 4px;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background-color: #ffffff;
            /* igual que .diploma-summary-table */
        }

        .students-table thead {
            background-color: #e5e7eb;
            /* igual que .diploma-summary-table thead */
        }

        .students-table th,
        .students-table td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
            color: #111827;
        }

        .students-table th {
            font-weight: 600;
        }

        .students-table tr:nth-child(even) td {
            background-color: #f9fafb;
            /* igual que .diploma-summary-table tr:nth-child(even) */
        }

        .students-table tr:last-child td {
            border-bottom: none;
        }

        /* ===========================
       BADGES (Aprobado / Reprobado / etc.)
       alineadas con status-ok / status-bad
       =========================== */

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-success {
            color: #16a34a;
            /* igual que .diploma-summary-status-ok */
            background-color: #dcfce7;
        }

        .badge-danger {
            color: #dc2626;
            /* igual que .diploma-summary-status-bad (modo claro) */
            background-color: #fee2e2;
        }

        .badge-info {
            color: #1d4ed8;
            background-color: #dbeafe;
        }

        .badge-secondary {
            color: #6b7280;
            background-color: #e5e7eb;
        }

        /* ===========================
       MODO OSCURO (usa .dark como en diploma-summary)
       =========================== */

        .dark .students-count-text {
            color: #e5e7eb;
        }

        .dark .students-empty-text {
            color: #9ca3af;
            /* igual que .diploma-summary-muted en dark */
        }

        .dark .students-table {
            background-color: #111111;
            /* igual que .diploma-summary-table */
        }

        .dark .students-table thead {
            background-color: #323336;
            /* igual que .diploma-summary-table thead */
        }

        .dark .students-table th,
        .dark .students-table td {
            border-bottom-color: #323437;
            color: #e5e7eb;
        }

        .dark .students-table tr:nth-child(even) td {
            background-color: #191a1b;
            /* igual que .diploma-summary-table tr:nth-child(even) */
        }

        /* Badges en modo oscuro, siguiendo tus status */
        .dark .badge-success {
            color: #16a34a;
            /* mismo verde que status-ok */
            background-color: rgba(22, 163, 74, 0.18);
        }

        .dark .badge-danger {
            color: #fca5a5;
            /* mismo rojo claro que status-bad dark */
            background-color: rgba(248, 113, 113, 0.18);
        }

        .dark .badge-info {
            color: #bfdbfe;
            background-color: rgba(59, 130, 246, 0.2);
        }

        .dark .badge-secondary {
            color: #e5e7eb;
            background-color: #323336;
        }
    </style>

    <div class="students-wrapper">
        @if ($students->isEmpty())
            <p class="students-empty-text">
                Este curso no tiene estudiantes inscritos aún.
            </p>
        @else
            <p class="students-count-text">
                Total estudiantes:
                <span class="students-count-number">{{ $students->count() }}</span>
            </p>

            <div class="students-table-wrapper">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            {{-- <th>Email</th> --}}
                            <th>RUT</th>
                            <th>Inscrito</th>
                            <th>Nota final</th>
                            <th>Aprobado</th>
                            <th>Asistencia (%)</th>
                            <th>Diploma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->nombre }} {{ $student->apellido }}</td>
                                {{-- <td>{{ $student->email }}</td> --}}
                                <td>{{ $student->rut }}</td>
                                <td>{{ optional($student->pivot->enrolled_at)->format('d-m-Y H:i') }}</td>
                                <td>
                                    {{ $student->pivot->final_grade !== null ? number_format($student->pivot->final_grade, 2, ',', '.') : '-' }}
                                </td>
                                <td>
                                    @if ($student->pivot->approved)
                                        <span class="badge badge-success">Aprobado</span>
                                    @else
                                        <span class="badge badge-danger">Reprobado</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $student->pivot->attendance !== null ? $student->pivot->attendance . '%' : '-' }}
                                </td>
                                <td>
                                    @if ($student->pivot->diploma_issued)
                                        <span class="badge badge-info">Emitido</span>
                                    @else
                                        <span class="badge badge-secondary">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
