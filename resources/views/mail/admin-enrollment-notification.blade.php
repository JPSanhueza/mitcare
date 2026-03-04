@component('mail::message')
{{-- PREHEADER (oculto en la mayoría de clientes) --}}
<span style="display:none!important;opacity:0;color:transparent;visibility:hidden;max-height:0;max-width:0;overflow:hidden;">
    Nueva inscripción de {{ $attendee->name }} en {{ strip_tags_for_order_name($item->course_name) }}
</span>

# 📋 Nueva Inscripción Registrada

Hola **OTEC Mitcare Admin**,

Te informamos que se ha registrado una nueva inscripción en el sistema.

@component('mail::panel')
**Detalles del Participante**

- **Nombre:** {{ $attendee->name }}
- **Email:** {{ $attendee->email }}
- **Estado:** {{ ucfirst($attendee->status) }}

@isset($attendee->moodle_username)
- **Usuario Moodle:** {{ $attendee->moodle_username }}
@endisset
@endcomponent

@component('mail::panel')
**Detalles del Curso**

- **Curso:** {{ strip_tags_for_order_name($item->course_name) }}
@isset($item->modality)
- **Modalidad:** {{ ucfirst($item->modality) }}
@endisset
@isset($item->starts_at)
- **Fecha de inicio:** {{ \Illuminate\Support\Carbon::parse($item->starts_at)->translatedFormat('d \d\e F, H:i') }}
@endisset
@endcomponent

@component('mail::panel')
**Detalles de la Orden**

- **Código de Orden:** {{ $order->code ?? ('#'.$order->id) }}
- **Comprador:** {{ $order->buyer_name }}
- **Email Comprador:** {{ $order->buyer_email }}
- **Total:** ${{ number_format($order->total, 0, ',', '.') }} {{ $order->currency ?? 'CLP' }}
- **Estado:** {{ ucfirst($order->status) }}
- **Método de Pago:** {{ ucfirst($order->payment_method) }}
@endcomponent

@component('mail::button', ['url' => config('app.url').'/admin'])
Ver en Panel Admin
@endcomponent

---

### 📊 Acciones sugeridas:
- Verificar que el participante tenga acceso al curso
- Revisar que los materiales estén disponibles
- Confirmar que la información de contacto sea correcta

---

Este es un correo automático generado por el sistema.

Saludos,<br>
{{ config('app.name') }}
@endcomponent
