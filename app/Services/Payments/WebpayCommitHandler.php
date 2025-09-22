<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayCommitHandler
{
    public function handle(string $token): array
    {
        $tx  = new Transaction();
        $res = $tx->commit($token);

        $status          = $res->getStatus();
        $responseCode    = (int) $res->getResponseCode();
        $amount          = (int) $res->getAmount();
        $buyOrder        = $res->getBuyOrder();
        $sessionId       = $res->getSessionId();
        $authorization   = $res->getAuthorizationCode();
        $paymentTypeCode = $res->getPaymentTypeCode();
        $installments    = $res->getInstallmentsNumber();
        $transactionDate = $res->getTransactionDate();
        $cardDetail      = method_exists($res, 'getCardDetail') ? $res->getCardDetail() : null;

        $order = Order::find($buyOrder);
        if (! $order) {
            throw new \RuntimeException("Orden no encontrada para buyOrder={$buyOrder}");
        }

        if ($amount !== (int) $order->total) {
            $order->update(['status' => 'failed']);
            throw new \RuntimeException("Monto inválido: esperado {$order->total}, recibido {$amount}");
        }

        // Guardar commit en meta
        $order->update([
            'meta' => $this->mergeMeta($order, [
                'webpay_commit' => [
                    'status'           => $status,
                    'response_code'    => $responseCode,
                    'amount'           => $amount,
                    'buy_order'        => $buyOrder,
                    'session_id'       => $sessionId,
                    'authorization'    => $authorization,
                    'payment_type'     => $paymentTypeCode,
                    'installments'     => $installments,
                    'transaction_date' => $transactionDate,
                    'card_detail'      => $cardDetail,
                ],
            ]),
        ]);

        $approved = ($status === 'AUTHORIZED' && $responseCode === 0);

        return ['approved' => $approved, 'order' => $order];
    }

    private function mergeMeta(Order $order, array $extra): array
    {
        return array_replace_recursive($order->meta ?? [], $extra);
    }
}
