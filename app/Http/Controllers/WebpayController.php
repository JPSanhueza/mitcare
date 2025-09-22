<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus;
use Transbank\Webpay\WebpayPlus\Transaction;

class WebpayController extends Controller
{
    public function __construct()
    {
        if (config('services.transbank.env') === 'production') {
            WebpayPlus::configureForProduction(
                config('services.transbank.webpayplus.commerce_code'),
                config('services.transbank.webpayplus.api_key'),
            );
        } else {
            WebpayPlus::configureForTesting(); // sandbox
        }
    }

    /**
     * Inicia transacción en Webpay (crea token y redirige).
     */
    public function start(Order $order)
    {
        // Aceptamos reintentos solo si sigue pendiente/procesando
        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return redirect()->route('checkout.index')->with('error', 'Orden no disponible para pago.');
        }

        // Reglas Webpay:
        // - buyOrder máx 26 chars
        // - sessionId máx 61 chars
        // - amount entero (CLP)
        $buyOrder  = (string) $order->id;                          // corto y único
        $sessionId = substr(session()->getId() ?: ('sess_'.uniqid()), 0, 61);
        $amount    = (int) $order->total;
        $returnUrl = route('webpay.callback');

        try {
            $tx = new Transaction();
            $res = $tx->create($buyOrder, $sessionId, $amount, $returnUrl);

            $token = $res->getToken();
            $url   = $res->getUrl();

            // Guarda token y session en meta + pasa a processing
            $meta = $order->meta ?? [];
            $meta['webpay_token'] = $token;
            $meta['webpay_session_id'] = $sessionId;

            $order->update([
                'status' => 'processing',
                'meta'   => $meta,
            ]);

            return redirect()->away("{$url}?token_ws={$token}");
        } catch (\Throwable $e) {
            Log::error('Webpay start error', [
                'order_id' => $order->id,
                'msg'      => $e->getMessage(),
            ]);
            $order->update(['status' => 'failed']);
            return redirect()->route('checkout.index')->with('error', 'No se pudo iniciar el pago.');
        }
    }

    /**
     * Retorno de Webpay (commit). Debe ser POST con token_ws.
     * Maneja también cancelaciones (TBK_*).
     */
    public function callback(Request $request)
    {
        // Caso cancelación desde Webpay (flujo TBK)
        if ($request->filled('TBK_TOKEN')) {
            $tbkToken   = $request->input('TBK_TOKEN');
            $buyOrder   = $request->input('TBK_ORDEN_COMPRA');
            $sessionId  = $request->input('TBK_ID_SESION');

            $order = $this->findOrderFromTbk($tbkToken, $buyOrder, $sessionId);

            if ($order) {
                $order->update(['status' => 'failed', 'meta' => $this->mergeMeta($order, [
                    'webpay_cancel' => Arr::only($request->all(), ['TBK_TOKEN','TBK_ORDEN_COMPRA','TBK_ID_SESION']),
                ])]);
            }

            return redirect()->route('checkout.index')->with('error', 'Pago cancelado por el usuario.');
        }

        // Flujo normal con token_ws
        $token = $request->input('token_ws');
        if (! $token) {
            return redirect()->route('checkout.index')->with('error', 'Token inválido.');
        }

        try {
            $tx  = new Transaction();
            $res = $tx->commit($token);

            // Datos de respuesta
            $status          = $res->getStatus();          // AUTHORIZED | FAILED | etc.
            $responseCode    = $res->getResponseCode();    // 0 = ok
            $amount          = (int) $res->getAmount();
            $buyOrder        = $res->getBuyOrder();
            $sessionId       = $res->getSessionId();
            $authorization   = $res->getAuthorizationCode();
            $paymentTypeCode = $res->getPaymentTypeCode();
            $installments    = $res->getInstallmentsNumber();
            $transactionDate = $res->getTransactionDate();
            $cardDetail      = method_exists($res, 'getCardDetail') ? $res->getCardDetail() : null;

            $order = Order::find($buyOrder); // usamos id como buyOrder
            if (! $order) {
                Log::error('Webpay callback: orden no encontrada', ['buyOrder' => $buyOrder]);
                return redirect()->route('checkout.index')->with('error', 'Orden no encontrada.');
            }

            // Validaciones cruzadas
            if ($amount !== (int) $order->total) {
                Log::warning('Webpay amount mismatch', ['order_id' => $order->id, 'expected' => $order->total, 'got' => $amount]);
                $order->update(['status' => 'failed']);
                return redirect()->route('checkout.index')->with('error', 'Monto inválido en confirmación.');
            }

            // Éxito real: status AUTHORIZED y responseCode 0
            $isApproved = ($status === 'AUTHORIZED' && (int) $responseCode === 0);

            // Guarda todo en meta
            $order->update(['meta' => $this->mergeMeta($order, [
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
            ])]);

            if (! $isApproved) {
                $order->update(['status' => 'failed']);
                return redirect()->route('checkout.index')->with('error', 'Pago rechazado.');
            }

            // Marcar pagada y disparar post-proceso (matricular, mails, limpiar carrito, etc.)
            $order->update(['status' => 'paid']);

            try {
                // TODO: aquí llama a tu servicio de matrícula/envíos
                // app(EnrollmentService::class)->enrollFromOrder($order);
                // app(CartService::class)->clear();

                return redirect()->route('checkout.success', ['order' => $order->id]);
            } catch (\Throwable $e) {
                Log::error('Post pago error', ['order_id' => $order->id, 'msg' => $e->getMessage()]);
                // La orden ya está pagada; muestra página de éxito con aviso
                return redirect()->route('checkout.success', ['order' => $order->id])
                    ->with('warning', 'Pago aprobado, hubo un problema al finalizar. Nuestro equipo lo revisará.');
            }
        } catch (\Throwable $e) {
            Log::error('Webpay commit error', ['msg' => $e->getMessage()]);
            return redirect()->route('checkout.index')->with('error', 'Error al confirmar el pago.');
        }
    }

    private function findOrderFromTbk(?string $tbkToken, ?string $buyOrder, ?string $sessionId): ?Order
    {
        // Preferimos buyOrder numérico (id)
        if ($buyOrder && ctype_digit($buyOrder)) {
            $order = Order::find((int) $buyOrder);
            if ($order) return $order;
        }

        // Si no, busca por token en meta
        if ($tbkToken) {
            return Order::where('meta->webpay_token', $tbkToken)->first();
        }

        // Como último recurso, por session_id en meta
        if ($sessionId) {
            return Order::where('meta->webpay_session_id', $sessionId)->first();
        }

        return null;
    }

    private function mergeMeta(Order $order, array $extra): array
    {
        $meta = $order->meta ?? [];
        return array_replace_recursive($meta, $extra);
    }
}
