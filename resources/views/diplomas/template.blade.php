<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Diploma</title>

    <style>
        @font-face {
            font-family: 'Lexend';
            src: url('{{ public_path('fonts/lexend/Lexend-Light.ttf') }}') format('truetype');
            font-weight: 300;
        }

        @font-face {
            font-family: 'Lexend';
            src: url('{{ public_path('fonts/lexend/Lexend-Regular.ttf') }}') format('truetype');
            font-weight: 400;
        }

        @font-face {
            font-family: 'Lexend';
            src: url('{{ public_path('fonts/lexend/Lexend-Medium.ttf') }}') format('truetype');
            font-weight: 500;
        }

        @font-face {
            font-family: 'Lexend';
            src: url('{{ public_path('fonts/lexend/Lexend-SemiBold.ttf') }}') format('truetype');
            font-weight: 600;
        }

        @font-face {
            font-family: 'Lexend';
            src: url('{{ public_path('fonts/lexend/Lexend-Bold.ttf') }}') format('truetype');
            font-weight: 700;
        }

        @page {
            margin: 0;
            /* fondo a página completa */
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Lexend', sans-serif;
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
            top: 65px;
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

        .logo-top {
            width: 420px;
            margin-bottom: 10px;
        }

        .cert-title {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 40px;
        }

        .student-name {
            font-size: 25px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .student-rut {
            font-size: 16px;
            font-weight: 500;
        }

        /* Línea amarilla central (bajo nombre y RUT) */
        .center-separator {
            height: 0.5px;
            background-color: #f6d686;
            width: 100%;
            margin: 5px auto 10px;
        }

        /* Texto principal: todo uniforme, sin negrita y con menos interlineado */
        .section {
            font-size: 17px;
            line-height: 1.1;
            margin: 3px 0;
            font-weight: 300;
        }

        .course-title {
            font-size: 19px;
            font-weight: 700;
            margin: 14px 0 10px;
        }

        /* Firmas */
        .signature-area {
            position: absolute;
            left: 30px;
            right: 30px;
            bottom: 60px;
            /* antes 150: ahora más abajo */
            text-align: center;
        }

        .logo-confidence {
            position: absolute;
            left: 40px;
            bottom: 55px;
            width: 250px;
        }

        .logo-inn {
            position: absolute;
            right: 50px;
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
        $formatDateEs = function ($date) {
            if (!$date) {
                return '';
            }

            $months = [
                1 => 'enero',
                2 => 'febrero',
                3 => 'marzo',
                4 => 'abril',
                5 => 'mayo',
                6 => 'junio',
                7 => 'julio',
                8 => 'agosto',
                9 => 'septiembre',
                10 => 'octubre',
                11 => 'noviembre',
                12 => 'diciembre',
            ];

            // Asegurarse de tener un Carbon
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }

            $day = $date->format('d');
            $month = $months[(int) $date->format('n')] ?? '';
            $year = $date->format('Y');

            return "{$day} de {$month} de {$year}";
        };

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

        use Illuminate\Support\Facades\Storage;

        $qrSvgRaw = Storage::disk('public')->get($diploma->qr_path);
        $qrSvgBase64 = base64_encode($qrSvgRaw);
    @endphp

    <div class="page">
        {{-- QR arriba izquierda --}}
        <img src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}" alt="Código QR" class="qr-code">

        <div class="content">
            {{-- Logo OTEC arriba centro --}}
            <img src="{{ public_path('img/logos/logo-superior-diploma.png') }}" alt="OTEC Mitcare" class="logo-top">

            {{-- Nombre + RUT (sobre la línea amarilla) --}}
            <div class="student-name" style="margin-top: 15px;">
                {{ $student->nombre }} {{ $student->apellido }} - {{ $rutFormateado }}
            </div>

            <div class="center-separator"></div>

            {{-- Descripción del curso (texto uniforme, sin negritas) --}}
            <p class="section">
                Por cursar {{ $course->total_hours }} hrs cronológicas en formato
                {{ ucfirst($course->modality) }}
                @if ($course->location)
                    {{ $course->location }}
                @endif
                <br />
                @if ($course->hours_description)
                    ({{ $course->hours_description }})
                @endif
                obtenido:
            </p>

            {{-- Nota + asistencia (sin negrita en el cuerpo) --}}
            <p class="section" style="margin-top: 18px;">
                Certificación en {{ $course->nombre_diploma }}
                <br>
                Calificación de {{ number_format($finalGrade, 1, ',', '.') }}
                con escala de 1 a 7 y un
                {{ $attendance }}% de asistencia
            </p>

            {{-- Rango de fechas + ubicación --}}
            <p class="section" style="margin-top: 18px;">
                Se extiende el siguiente certificado con fecha
                {{ $formatDateEs($course->start_at) }}
                @if ($course->start_at && $course->end_at)
                    al {{ $formatDateEs($course->end_at) }},
                @endif
                {{ $course->location }}.
            </p>
        </div>

        {{-- FIRMAS DINÁMICAS --}}
        <div class="signature-area">
            <table style="width:100%; text-align:center;">
                <tr>
                    @foreach ($teachers as $t)
                        <td style="width:33%; padding: 0px 30px 0px 30px; line-height:1.1;">
                            @if ($t->signature)
                                {{-- Firma grande --}}
                                <img src="{{ public_path('storage/' . $t->signature) }}"
                                    style="height:130px; margin-bottom:2px;">
                            @else
                                <div style="height:130px; margin-bottom:2px;"></div>
                            @endif

                            {{-- Línea amarilla --}}
                            <div style="border-top:1px solid #f6d686; width:30%; margin:5px auto 0;"></div>

                            {{-- Nombre --}}
                            <div style="margin-top:4px; font-size:20px; font-weight:bold;">
                                {{ $t->nombre }} {{ $t->apellido }}
                            </div>

                            {{-- Especialidad --}}
                            <div style="font-size:18px; font-weight:Light;">
                                {{ $t->especialidad ?? 'Docente' }}
                            </div>

                            {{-- Organización --}}
                            <div style="font-size:18px; font-weight:Light;">
                                {{ $t->organization->nombre ?? '' }}
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- Logos inferiores --}}
        <img src="{{ public_path('img/logos/confidence-logo3.png') }}" alt="Confidence Certification"
            class="logo-confidence">

        <img src="{{ public_path('img/logos/inn-logo2.png') }}" alt="INN Chile" class="logo-inn">
    </div>

</body>

</html>
