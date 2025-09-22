<?php

namespace App\Services\Payments;

use App\Models\Order;

class TbkCancelHandler
{
    public function handle(array $tbk): ?Order
    {
        $tbkToken  = $tbk['TBK_TOKEN']        ?? null;
        $buyOrder  = $tbk['TBK_ORDEN_COMPRA'] ?? null;
        $sessionId = $tbk['TBK_ID_SESION']    ?? null;

        $order = $this->findOrderFromTbk($tbkToken, $buyOrder, $sessionId);

        if ($order) {
            $order->update([
                'status' => 'failed',
                'meta'   => $this->mergeMeta($order, ['webpay_cancel' => $tbk]),
            ]);
        }

        return $order;
    }

    private function findOrderFromTbk(?string $tbkToken, ?string $buyOrder, ?string $sessionId): ?Order
    {
        if ($buyOrder && ctype_digit($buyOrder)) {
            if ($order = Order::find((int) $buyOrder)) {
                return $order;
            }
        }
        if ($tbkToken) {
            if ($order = Order::where('meta->webpay_token', $tbkToken)->first()) {
                return $order;
            }
        }
        if ($sessionId) {
            return Order::where('meta->webpay_session_id', $sessionId)->first();
        }
        return null;
    }

    private function mergeMeta(Order $order, array $extra): array
    {
        return array_replace_recursive($order->meta ?? [], $extra);
    }
}
