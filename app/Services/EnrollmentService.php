<?php

namespace App\Services;

use App\Mail\CourseEnrollmentMail;
use App\Models\Order;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnrollmentService
{
    /**
     * Marca asistentes como "enrolled" y envía correo por cada uno.
     * No crea usuarios. Solo deja constancia y notifica.
     */
    public function enrollFromOrder(Order $order): void
    {
        $order->load(['items.attendees']);

        foreach ($order->items as $item) {
            foreach ($item->attendees as $att) {
                if ($att->status === 'enrolled') {
                    continue; // idempotente
                }

                $att->update(['status' => 'enrolled']);

                try {
                    Mail::to($att->email)->queue(new CourseEnrollmentMail($order, $item, $att));
                    Log::info('Correo de inscripción ENVIADO (sync)', ['attendee_id' => $att->id, 'email' => $att->email]);
                } catch (\Throwable $e) {
                    Log::error('No se pudo enviar correo de inscripción', [
                        'attendee_id' => $att->id,
                        'email' => $att->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Si usas carrito de sesión:
        try {
            app(CartService::class)->clear();
        } catch (\Throwable $e) {
            Log::warning('No se pudo limpiar carrito post pago', ['msg' => $e->getMessage()]);
        }
    }
}
