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
            left: 255px;
            right: 255px;
            bottom: 38px;
            text-align: center;
        }

        .logo-confidence {
            position: absolute;
            left: 60px;
            bottom: 60px;
            width: 200px;
        }

        .logo-inn {
            position: absolute;
            right: 60px;
            bottom: 60px;
            width: 110px;
        }

        /* QR arriba izquierda */
        .qr-code {
            position: absolute;
            top: 60px;
            left: 60px;
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

            return "{$day} de {$month}";
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

            return '- ' . $num . '-' . $dv;
        };

        $rutFormateado = $formatRut($student->rut ?? '');

        use Illuminate\Support\Facades\Storage;

        $qrSvgRaw = Storage::disk('public')->get($diploma->qr_path);
        $qrSvgBase64 = base64_encode($qrSvgRaw);

        $teacherCount = $teachers->count();

        // Ancho de la tabla de firmas según cantidad
        $signTableWidth = match ($teacherCount) {
            1 => '70%',
            2 => '90%',
            default => '92%', // 3 o más
        };

        // Altura de firma e intensidades de texto
        $signImageHeight = $teacherCount >= 3 ? 105 : 130;
        $signNameFont = $teacherCount >= 3 ? 16 : 20;
        $signSubFont = $teacherCount >= 3 ? 13 : 17;

        // Firmas en base64 por profesor
        $teacherSignatures = [];

        foreach ($teachers as $prof) {
            if (!$prof->signature) {
                continue;
            }

            $disk = 'public'; // o 's3' si tu FileUpload usa ese disco

            if (!Storage::disk($disk)->exists($prof->signature)) {
                continue;
            }

            $raw = Storage::disk($disk)->get($prof->signature);
            $mime = Storage::disk($disk)->mimeType($prof->signature) ?? 'image/png';

            $teacherSignatures[$prof->id] = 'data:' . $mime . ';base64,' . base64_encode($raw);
        }
    @endphp

    <div class="page">
        {{-- QR arriba izquierda --}}
        <img src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}" alt="Código QR" class="qr-code">

        <div class="content">
            {{-- Logo OTEC arriba centro --}}
            <img src="{{ public_path('img/logos/logo-superior-diploma.png') }}" alt="OTEC Mitcare" class="logo-top">

            {{-- Nombre + RUT (sobre la línea amarilla) --}}
            <div class="student-name" style="margin-top: 15px;">
                {{ $student->nombre }} {{ $student->apellido }} {{ $rutFormateado }}
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
                    al {{ $formatDateEs($course->end_at) }} {{ date('Y') }},
                @endif
                {{ $course->location }}.
            </p>
        </div>

        {{-- FIRMAS DINÁMICAS --}}
        <div class="signature-area">
            <table style="width: {{ $signTableWidth }}; margin: 0 auto; text-align:center;">
                <tr>
                    @foreach ($teachers as $t)
                        <td style="width: {{ 100 / max($teacherCount, 1) }}%; padding: 0 10px;">
                            <div
                                style="
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:flex-start;
        height: 230px;
    ">
                                @php
                                    $signSrc = $teacherSignatures[$t->id] ?? null;
                                @endphp

                                @if ($signSrc)
                                    <img src="{{ $signSrc }}" style="height: {{ $signImageHeight }}px;">
                                @else
                                    <div style="height: {{ $signImageHeight }}px;"></div>
                                @endif

                                <div style="border-top:1px solid #f6d686; width:75%; margin:4px auto;"></div>

                                <div
                                    style="font-size: {{ $signNameFont }}px; font-weight:700; line-height:1; margin-top:4px;">
                                    {{ $t->nombre }} {{ $t->apellido }}
                                </div>

                                <div style="font-size: {{ $signSubFont }}px; font-weight:300; line-height:1;">
                                    {{ $t->especialidad ?? 'Docente' }}
                                </div>

                                <div style="font-size: {{ $signSubFont }}px; font-weight:300; line-height:1;">
                                    {{ $t->organization_name ?? '' }}
                                </div>

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
