<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Diploma</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background-image: url('{{ public_path('img/fondos/fondo-diploma.jpg') }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center;
            color: #1d2850;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .content {
            position: absolute;
            top: 90px; /* antes era 70 + problemas */
            left: 120px;
            right: 120px;
            text-align: center;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
            padding: 0;
        }

        /* Logo superior centro */
        .logo-top {
            width: 220px;
            margin: 10px auto 25px;
        }

        .cert-title {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 40px;
        }

        .student-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .student-rut {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 35px;
        }

        .section {
            font-size: 15px;
            line-height: 1.7;
            margin: 8px 0;
        }

        .course-title {
            font-size: 19px;
            font-weight: 700;
            margin: 16px 0 10px;
        }

        /* Firmas (fila centrada) */
        .signature-area {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 120px;
            text-align: center;
        }

        /* Logos inferiores */
        .logo-confidence {
            position: absolute;
            left: 60px;
            bottom: 60px;
            width: 150px;
        }

        .logo-inn {
            position: absolute;
            right: 60px;
            bottom: 60px;
            width: 120px;
        }

        /* QR arriba izquierda */
        .qr-code {
            position: absolute;
            top: 80px;   /* ajusta fino si quieres */
            left: 80px;  /* ajusta fino si quieres */
            width: 95px;
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
            $dv  = strtoupper(substr($rut, -1));
            $num = substr($rut, 0, -1);

            if ($num === '') {
                return $rut;
            }

            $num = number_format((int) $num, 0, ',', '.');

            return $num . '-' . $dv;
        };

        $rutFormateado = $formatRut($student->rut ?? '');

        use Illuminate\Support\Facades\Storage;

        $qrSvgRaw   = Storage::disk('public')->get($diploma->qr_path);
        $qrSvgBase64 = base64_encode($qrSvgRaw);

        $teacher = $teachers->first();
    @endphp

    <div class="page">
        <div class="content">
            {{-- Logo OTEC arriba centro --}}
            <img src="{{ public_path('img/logos/otec-mitcare-logo-azul.png') }}"
                 alt="OTEC Mitcare"
                 class="logo-top">

            {{-- Título principal --}}
            <h2 class="cert-title">OTEC MITCARE CERTIFICA A:</h2>

            {{-- Nombre + RUT --}}
            <div class="student-name">
                {{ $student->nombre }} {{ $student->apellido }}
            </div>

            <div class="student-rut">
                RUT {{ $rutFormateado }}
            </div>

            {{-- Descripción del curso --}}
            <p class="section">
                Por cursar <strong>{{ $course->total_hours }} hrs</strong> cronológicas en formato
                <strong>{{ ucfirst($course->modality) }}</strong>
                @if ($course->location)
                    {{ $course->location }}
                @endif
                @if ($course->hours_description)
                    ({{ $course->hours_description }})
                @endif
                obtenido:
            </p>

            {{-- Nombre del curso --}}
            <p class="course-title">
                {{ $course->nombre }}
            </p>

            {{-- Nota + asistencia --}}
            <p class="section">
                Calificación de <strong>{{ number_format($finalGrade, 1, ',', '.') }}</strong>
                con escala de <strong>1 a 7</strong> y un
                <strong>{{ $attendance }}%</strong> de asistencia.
            </p>

            {{-- Fecha --}}
            <p class="section" style="margin-top: 24px;">
                Se extiende el siguiente certificado con fecha
                <strong>{{ $issuedAt->format('d \d\e F \d\e Y') }}, Santiago de Chile.</strong>
            </p>
        </div>

        {{-- FIRMAS DINÁMICAS --}}
        <div class="signature-area">
            <table style="width:100%; text-align:center; margin-top:20px;">
                <tr>
                    @foreach ($teachers as $t)
                        <td style="width:33%;">
                            @if ($t->signature)
                                <img src="{{ public_path('storage/' . $t->signature) }}"
                                     style="height:70px; margin-bottom:5px;">
                            @else
                                <div style="height:70px; margin-bottom:5px;"></div>
                            @endif

                            <div style="border-top:1px solid #0a2342; width:70%; margin:0 auto;"></div>

                            <div style="margin-top:5px; font-size:13px; font-weight:bold;">
                                {{ $t->nombre }} {{ $t->apellido }}
                            </div>

                            <div style="font-size:11px;">
                                {{ $t->especialidad ?? 'Docente' }}
                            </div>
                             <div style="font-size:11px;">
                                {{ $t->organization->nombre ?? 'Docente' }}
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- Logo Confidence + texto --}}
        <img src="{{ public_path('img/logos/confidence-logo3.png') }}"
             alt="Confidence Certification"
             class="logo-confidence">

        {{-- Logo INN Chile --}}
        <img src="{{ public_path('img/logos/inn-logo2.png') }}"
             alt="INN Chile"
             class="logo-inn">

        {{-- QR arriba izquierda --}}
        <img src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
             alt="Código QR"
             class="qr-code">
    </div>

</body>

</html>
