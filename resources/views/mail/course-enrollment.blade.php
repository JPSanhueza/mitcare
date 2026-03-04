@component('mail::message')
{{-- PREHEADER (oculto en la mayoría de clientes) --}}
<span style="display:none!important;opacity:0;color:transparent;visibility:hidden;max-height:0;max-width:0;overflow:hidden;">
    {{ $previewText ?? ('Tu inscripción a '. strip_tags_for_order_name($item->course_name) .' fue confirmada.') }}
</span>

# ¡Hola {{ $attendee->name }}! 👋

¡Gracias por inscribirte! Tu matrícula al curso **{{ strip_tags_for_order_name($item->course_name) }}** ha sido **confirmada**.
A continuación te dejamos un resumen y los próximos pasos.

@component('mail::panel')
**Resumen de tu inscripción**

- **Curso:** {{ strip_tags_for_order_name($item->course_name) }}
- **Orden:** {{ $order->code ?? ('#'.$order->id) }}
- **Participante:** {{ $attendee->name }} ({{ $attendee->email }})

@isset($item->starts_at)
- **Fecha de inicio:** {{ \Illuminate\Support\Carbon::parse($item->starts_at)->translatedFormat('d \d\e F, H:i') }}
@endisset
@isset($item->modality)
- **Modalidad:** {{ ucfirst($item->modality) }}
@endisset
@endcomponent

{{-- @php
    $primaryUrl = $courseUrl ?? $dashboardUrl ?? route('home');
@endphp --}}

@component('mail::button', ['url' => "https://aulavirtual.otecmitcare.cl/"])
Ir a mi curso
@endcomponent

## ¿Qué sigue?
- Te enviaremos instrucciones de acceso y materiales previos (si aplica).
- Si ya tienes cuenta, podrás ver el curso en tu **panel**.
- Si es tu primera vez, revisa tu correo por si llega un mensaje para **activar tu cuenta** o **establecer contraseña**.

{{-- @isset($dashboardUrl)
Puedes revisar el estado de tu inscripción en tu panel:
@component('mail::button', ['url' => $dashboardUrl])
Ir a mi panel
@endcomponent
@endisset --}}

---

### ¿Necesitas ayuda?
Si tienes dudas o algún problema con el acceso, respóndenos a este correo o escríbenos a **{{ config('mail.from.address') }}**.

Gracias por confiar en **{{ config('app.name') }}**.
¡Nos vemos en clases!

{{-- @include('mail::subcopy', [
    'slot' => "Si el botón no funciona, copia y pega esta URL en tu navegador: ".$primaryUrl
]) --}}
@endcomponent
