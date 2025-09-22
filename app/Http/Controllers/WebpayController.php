<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Payments\WebpayStarter;
use App\Services\Payments\WebpayCommitHandler;
use App\Services\Payments\TbkCancelHandler;
use App\Services\Payments\PostPaymentActions;

class WebpayController extends Controller
{
    public function start(Order $order, WebpayStarter $starter)
    {
        try {
            $redirectUrl = $starter->start($order);
            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Webpay start error', ['order_id' => $order->id, 'msg' => $e->getMessage()]);
            $order->update(['status' => 'failed']);
            return redirect()->route('checkout.index')->with('error', 'No se pudo iniciar el pago.');
        }
    }

    public function callback(
        Request $request,
        TbkCancelHandler $cancelHandler,
        WebpayCommitHandler $commitHandler,
        PostPaymentActions $postActions
    ) {
        // Cancelación TBK
        if ($request->filled('TBK_TOKEN')) {
            $cancelHandler->handle($request->only(['TBK_TOKEN','TBK_ORDEN_COMPRA','TBK_ID_SESION']));
            return redirect()->route('checkout.failed')->with('error', 'Pago cancelado por el usuario.');
        }

        // Flujo normal
        $token = $request->input('token_ws');
        if (! $token) {
            return redirect()->route('checkout.failed')->with('error', 'Token inválido.');
        }

        try {
            $result = $commitHandler->handle($token);
            /** @var \App\Models\Order $order */
            $order = $result['order'];

            if (! $result['approved']) {
                $order->update(['status' => 'failed']);
                return redirect()->route('checkout.failed', ['order' => $order->id])->with('error', 'Pago rechazado.');
            }

            // Pago aprobado ⇒ acciones post pago
            try {
                $postActions->onApproved($order);
                return redirect()->route('checkout.success', ['order' => $order->id]);
            } catch (\Throwable $e) {
                Log::error('Post pago error', ['order_id' => $order->id, 'msg' => $e->getMessage()]);
                return redirect()->route('checkout.success', ['order' => $order->id])
                    ->with('warning', 'Pago aprobado, pero hubo un problema al finalizar. Revisaremos tu caso.');
            }
        } catch (\Throwable $e) {
            Log::error('Webpay commit error', ['msg' => $e->getMessage()]);
            return redirect()->route('checkout.failed')->with('error', 'Error al confirmar el pago.');
        }
    }
}
