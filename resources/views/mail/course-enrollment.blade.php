@component('mail::message')
# ¡Hola {{ $attendee->name }}!

Tu inscripción al curso **{{ $item->course_name }}** ha sido confirmada.

**Orden:** {{ $order->code ?? ('#'.$order->id) }}
**Correo de contacto:** {{ $attendee->email }}

En breve recibirás más instrucciones para acceder a tus contenidos.

@component('mail::button', ['url' => route('home')])
Ir al sitio
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
