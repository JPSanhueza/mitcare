<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Restablece tu contraseña</title>
</head>

<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:520px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 10px 30px rgba(15,23,42,.08);">
                    <tr>
                        <td style="padding:26px 24px 10px 24px;text-align:center;">
                            <div
                                style="font-size:22px;font-weight:800;letter-spacing:-.2px;color:#19355C;line-height:1.25;">
                                Restablece tu contraseña
                            </div>

                            <div style="margin-top:10px;font-size:14px;line-height:1.55;color:#475569;">
                                Hola <strong style="color:#0f172a;">{{ $student->nombre }}
                                    {{ $student->apellido }}</strong>.<br>
                                Recibimos una solicitud para restablecer tu contraseña de acceso a certificados.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 10px 24px;text-align:center;">
                            <a href="{{ $url }}"
                                style="display:inline-block;background:#19355C;color:#ffffff;text-decoration:none;font-weight:700;
                                      padding:12px 18px;border-radius:14px;font-size:14px;">
                                Restablecer contraseña
                            </a>

                            <div style="margin-top:12px;font-size:12px;color:#64748b;line-height:1.45;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:
                            </div>
                            <div style="margin-top:6px;font-size:12px;line-height:1.45;word-break:break-all;">
                                <a href="{{ $url }}" style="color:#19355C;text-decoration:underline;">
                                    {{ $url }}
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 24px 22px 24px;">
                            <div
                                style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px 14px;
                                        font-size:12px;line-height:1.5;color:#9a3412;text-align:left;">
                                <strong>¿No fuiste tú?</strong> Ignora este correo y tu contraseña no cambiará.
                            </div>

                            <div
                                style="border-top:1px solid #e2e8f0;margin-top:16px;padding-top:14px;font-size:12px;color:#64748b;
                                        line-height:1.5;text-align:center;">
                                © {{ date('Y') }} MITCARE · Certificados y diplomas
                            </div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>
