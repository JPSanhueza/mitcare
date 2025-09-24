<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\EnrollmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus;
use Transbank\Webpay\WebpayPlus\Transaction;

class ReconcileWebpayOrder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return 'reconcile-order-'.$this->orderId;
    }

    // intenta varias veces con backoff (ej: 10, 20, 30 min)
    public $tries = 3;
    public function backoff(): array { return [600, 1200, 1800]; }

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (! $order || $order->status !== 'processing') {
            return; // ya no aplica
        }

        // Config Transbank (igual que en tu controller)
        if (config('services.transbank.env') === 'production') {
            WebpayPlus::configureForProduction(
                config('services.transbank.webpayplus.commerce_code'),
                config('services.transbank.webpayplus.api_key'),
            );
        } else {
            WebpayPlus::configureForTesting();
        }

        $token = data_get($order->meta, 'webpay_token');
        if (! $token) {
            // sin token: marca como failed
            $order->update(['status' => 'failed']);
            return;
        }

        try {
            $tx  = new Transaction();
            $res = $tx->status($token); // ojo: normalmente sin commit no vendrá AUTHORIZED
            $st  = $res->getStatus();   // INITIALIZED | AUTHORIZED | FAILED | etc.

            Log::info('[reconcile] status', ['order' => $order->id, 'st' => $st]);

            // TTL de “espera” total (ej: 30 min desde creación)
            $ttlMinutes = 30;
            $isExpired = $order->created_at->lt(now()->subMinutes($ttlMinutes));

            if ($st === 'FAILED') {
                $order->update(['status' => 'failed']);
                return;
            }

            if ($st === 'AUTHORIZED') {
                // Caso raro sin commit; tratamos como pagado y disparamos post-proceso
                $order->update(['status' => 'paid', 'meta->webpay_commit.reconciled' => true]);
                app(EnrollmentService::class)->enrollFromOrder($order);
                return;
            }

            if ($st === 'INITIALIZED' && $isExpired) {
                // Nunca volvió del banco → expira
                $order->update(['status' => 'failed']);
                return;
            }

            // Si aún no expira y sigue INITIALIZED, dejamos que el retry lo intente después.
            $this->release($this->backoff()[0] ?? 600);
        } catch (\Throwable $e) {
            Log::warning('[reconcile] error', ['order' => $order->id, 'msg' => $e->getMessage()]);
            // dejar que los retries actúen
            throw $e;
        }
    }
}
