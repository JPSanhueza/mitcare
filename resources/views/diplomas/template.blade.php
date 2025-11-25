<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Diploma</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .page {
            width: 100%;
            height: 100%;
            padding: 60px 70px;
            box-sizing: border-box;
            position: relative;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        .title {
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #555;
        }

        .student-name {
            font-size: 26px;
            font-weight: 700;
            color: #0078b7;
            margin-bottom: 4px;
            text-align: center;
        }

        .student-rut {
            font-size: 20px;
            font-weight: 600;
            color: #0078b7;
            text-align: center;
            margin-bottom: 25px;
        }

        .section {
            font-size: 15px;
            line-height: 1.6;
            margin: 14px 0;
        }

        .course-title {
            font-size: 22px;
            font-weight: 700;
            color: #008fd1;
            margin: 20px 0;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        /* Logo Confidence abajo izq */
        .bottom-left-logo {
            position: absolute;
            bottom: 40px;
            left: 40px;
            width: 130px;
        }

        .footer-text {
            text-align: center;
            position: absolute;
            bottom: 40px;
            width: 100%;
            font-size: 13px;
            color: #444;
            font-weight: 600;
        }

        /* Espacios para firmas (solo líneas por ahora) */
        .signatures {
            margin-top: 80px;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .signature-block {
            width: 40%;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            width: 100%;
        }

        .signature-name {
            font-size: 15px;
            font-weight: 700;
            margin-top: 6px;
        }

        .signature-role {
            font-size: 13px;
            color: #555;
        }
    </style>
</head>

<body>
    @php
        // Formatear RUT aquí para no repetir lógica en el controlador
        $formatRut = function (?string $rut) {
            if (!$rut) {
                return '';
            }

            $rut = preg_replace('/[^0-9kK]/', '', $rut);
            $dv = strtoupper(substr($rut, -1));
            $num = substr($rut, 0, -1);

            if ($num === '') {
                return $rut;
            }

            $num = number_format((int) $num, 0, ',', '.');

            return $num . '-' . $dv;
        };

        $rutFormateado = $formatRut($student->rut ?? '');

        $qrData = base64_encode(Storage::disk('public')->get($diploma->qr_path));
    @endphp

    <div class="page">

        {{-- Título principal --}}
        <h2 class="title">CERTIFICADO DE APROBACIÓN A:</h2>

        {{-- Nombre + RUT --}}
        <div class="student-name">
            {{ $student->nombre }} {{ $student->apellido }}
        </div>

        <div class="student-rut">
            RUT {{ $rutFormateado }}
        </div>

        {{-- Descripción del curso --}}
        <div class="section">
            Por cursar <strong>{{ $course->total_hours }} hrs</strong> cronológicas en formato
            <strong>{{ ucfirst($course->modality) }}</strong>
            @if ($course->location)
                <strong>{{ $course->location }}</strong>
            @endif
            @if ($course->hours_description)
                ({{ $course->hours_description }})
            @endif
            obtenido:
        </div>

        {{-- Título del curso --}}
        <div class="course-title">
            {{ $course->nombre }}
        </div>

        {{-- Nota + asistencia --}}
        <div class="section">
            Calificación final de <strong>{{ number_format($finalGrade, 1, ',', '.') }}</strong>
            con escala de <strong>1 a 7</strong> y un
            <strong>{{ $attendance }}%</strong> de asistencia.
        </div>

        {{-- Fecha --}}
        <div class="section center" style="margin-top: 25px;">
            Se extiende el siguiente certificado con fecha
            <strong>{{ $issuedAt->format('d \d\e F \d\e Y') }}</strong>.
        </div>

        {{-- Espacios firmas (sin firma ni logos todavía) --}}
        <div class="signatures">

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">
                    {{ $teacher->nombre }} {{ $teacher->apellido }}
                </div>
                <div class="signature-role">
                    Docente
                </div>
            </div>

            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">
                    {{ $organization->nombre ?? 'OTEC Mitcare SPA' }}
                </div>
                <div class="signature-role">
                    Organización
                </div>
            </div>

        </div>

        {{-- Logo fijo abajo izquierda --}}
        <img src="{{ public_path('img/logos/confidence-logo2.png') }}" class="bottom-left-logo">

        {{-- Texto fijo abajo centro --}}
        <div class="footer-text">
            OTECMITCARE INN: A-13052 NCh2728:2015
        </div>

        <img src="data:image/png;base64,{{ $qrData }}" alt="Código QR" width="120">

    </div>

</body>

</html>
