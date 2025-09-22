<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayStarter
{
    public function start(Order $order): string
    {
        // Validación de estado permitido
        if (! in_array($order->status, ['pending', 'processing'], true)) {
            throw new \RuntimeException('Orden no disponible para pago.');
        }

        // Reglas Webpay
        $buyOrder  = (string) $order->id; // <= 26 chars
        $sessionId = substr(session()->getId() ?: ('sess_'.uniqid()), 0, 61);
        $amount    = (int) $order->total;
        $returnUrl = route('webpay.callback');

        $tx  = new Transaction();
        $res = $tx->create($buyOrder, $sessionId, $amount, $returnUrl);

        $token = $res->getToken();
        $url   = $res->getUrl();

        // Actualiza orden a processing y guarda token/session en meta
        $meta = $order->meta ?? [];
        $meta['webpay_token']      = $token;
        $meta['webpay_session_id'] = $sessionId;

        $order->update([
            'status' => 'processing',
            'meta'   => $meta,
        ]);

        Log::info('Webpay token generado', ['order_id' => $order->id, 'token' => $token]);

        // Devuelve URL completa para redirigir
        return "{$url}?token_ws={$token}";
    }
}
