<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Transbank\Webpay\WebpayPlus;
use Transbank\Webpay\WebpayPlus\Transaction;

class CloseStaleWebpayOrders extends Command
{
    protected $signature = 'orders:webpay:expire {--ttl=30}';
    protected $description = 'Marca como fallidas las órdenes Webpay en processing que excedieron el TTL y reconcilia estado si aplica.';

    public function handle(): int
    {
        // Configura Webpay (igual que en tu controlador)
        if (config('services.transbank.env') === 'production') {
            WebpayPlus::configureForProduction(
                config('services.transbank.webpayplus.commerce_code'),
                config('services.transbank.webpayplus.api_key'),
            );
        } else {
            WebpayPlus::configureForTesting();
        }

        $ttl = (int) $this->option('ttl');
        $cut = now()->subMinutes($ttl);

        $orders = Order::where('status', 'processing')
            ->where('created_at', '<', $cut)
            ->whereNotNull('meta->webpay_token')
            ->get();

        $this->info("Revisando {$orders->count()} órdenes…");

        foreach ($orders as $order) {
            $token = data_get($order->meta, 'webpay_token');
            $shouldFail = true;

            if ($token) {
                try {
                    // (Opcional) Consulta status. Normalmente sin commit NO estará AUTHORIZED.
                    $tx  = new Transaction();
                    $res = $tx->status($token);
                    $st  = $res->getStatus(); // AUTHORIZED | FAILED | INITIALIZED | etc.

                    Log::info('[cron] webpay status', ['order' => $order->id, 'status' => $st]);

                    if ($st === 'AUTHORIZED') {
                        // Seguridad: si llegara a estar autorizado, tratamos como pagado y corremos postproceso
                        $order->update(['status' => 'paid', 'meta->webpay_commit.cron_reconciled' => true]);
                        app(\App\Services\EnrollmentService::class)->enrollFromOrder($order);
                        $shouldFail = false;
                    }
                } catch (\Throwable $e) {
                    Log::warning('[cron] webpay status error', ['order' => $order->id, 'msg' => $e->getMessage()]);
                }
            }

            if ($shouldFail) {
                $order->update(['status' => 'failed']);
                Log::info('[cron] orden expirada → failed', ['order' => $order->id]);
            }
        }

        return self::SUCCESS;
    }
}
