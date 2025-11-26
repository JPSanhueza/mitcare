<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Diploma</title>

    <style>
        @page {
            margin: 0; /* fondo a página completa */
        }

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
            top: 90px;
            left: 120px;
            right: 120px;
            text-align: center;
        }

        h1, h2, h3, p {
            margin: 0;
            padding: 0;
        }

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
            margin-bottom: 4px;
        }

        .student-rut {
            font-size: 16px;
            font-weight: 500;
        }

        /* Línea amarilla central (bajo nombre y RUT) */
        .center-separator {
            height: 1px;
            background-color: #E5B947;
            width: 80%;
            margin: px auto 20px;
        }

        /* Texto principal: todo uniforme, sin negrita y con menos interlineado */
        .section {
            font-size: 15px;
            line-height: 1.4;
            margin: 4px 0;
            font-weight: 400;
        }

        .section strong {
            font-weight: 400; /* por si quedara alguno, lo fuerza a normal */
        }

        .course-title {
            font-size: 19px;
            font-weight: 700;
            margin: 14px 0 10px;
        }

        /* Firmas */
        .signature-area {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 100px; /* antes 150: ahora más abajo */
            text-align: center;
        }

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
            top: 80px;
            left: 80px;
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
    @endphp

    <div class="page">
        {{-- QR arriba izquierda --}}
        <img src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
             alt="Código QR"
             class="qr-code">

        <div class="content">
            {{-- Logo OTEC arriba centro --}}
            <img src="{{ public_path('img/logos/otec-mitcare-logo-azul.png') }}"
                 alt="OTEC Mitcare"
                 class="logo-top">

            {{-- Título principal --}}
            <h2 class="cert-title">OTEC MITCARE CERTIFICA A:</h2>

            {{-- Nombre + RUT (sobre la línea amarilla) --}}
            <div class="student-name">
                {{ $student->nombre }} {{ $student->apellido }}
            </div>

            <div class="student-rut">
                RUT {{ $rutFormateado }}
            </div>

            <div class="center-separator"></div>

            {{-- Descripción del curso (texto uniforme, sin negritas) --}}
            <p class="section">
                Por cursar {{ $course->total_hours }} hrs cronológicas en formato
                {{ ucfirst($course->modality) }}
                @if ($course->location)
                    {{ $course->location }}
                @endif
                @if ($course->hours_description)
                    ({{ $course->hours_description }})
                @endif
                obtenido:
            </p>

            {{-- Nota + asistencia (sin negrita en el cuerpo) --}}
            <p class="section">
                Certificación en {{ $course->nombre }}
                <br>
                Calificación de {{ number_format($finalGrade, 1, ',', '.') }}
                con escala de 1 a 7 y un
                {{ $attendance }}% de asistencia.
            </p>

            {{-- Rango de fechas + ubicación --}}
            <p class="section" style="margin-top: 16px;">
                Se extiende el siguiente certificado con fecha
                {{ optional($course->start_at)->format('d \d\e F \d\e Y') }}
                @if($course->start_at && $course->end_at)
                    al {{ $course->end_at->format('d \d\e F \d\e Y') }},
                @endif
                {{ $course->location }}.
            </p>
        </div>

        {{-- FIRMAS DINÁMICAS --}}
        <div class="signature-area">
            <table style="width:100%; text-align:center;">
                <tr>
                    @foreach ($teachers as $t)
                        <td style="width:33%;">
                            @if ($t->signature)
                                {{-- Firma grande --}}
                                <img src="{{ public_path('storage/' . $t->signature) }}"
                                     style="height:110px; margin-bottom:5px;">
                            @else
                                <div style="height:110px; margin-bottom:5px;"></div>
                            @endif

                            {{-- Línea amarilla --}}
                            <div style="border-top:2px solid #E5B947; width:80%; margin:5px auto 0;"></div>

                            {{-- Nombre --}}
                            <div style="margin-top:5px; font-size:13px; font-weight:bold;">
                                {{ $t->nombre }} {{ $t->apellido }}
                            </div>

                            {{-- Especialidad --}}
                            <div style="font-size:11px;">
                                {{ $t->especialidad ?? 'Docente' }}
                            </div>

                            {{-- Organización --}}
                            <div style="font-size:11px; margin-top:3px;">
                                {{ $t->organization->nombre ?? '' }}
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- Logos inferiores --}}
        <img src="{{ public_path('img/logos/confidence-logo3.png') }}"
             alt="Confidence Certification"
             class="logo-confidence">

        <img src="{{ public_path('img/logos/inn-logo2.png') }}"
             alt="INN Chile"
             class="logo-inn">
    </div>

</body>

</html>
