<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Transbank\Webpay\WebpayPlus;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('services.transbank.env') === 'production') {
            WebpayPlus::configureForProduction(
                config('services.transbank.webpayplus.commerce_code'),
                config('services.transbank.webpayplus.api_key'),
            );
        } else {
            WebpayPlus::configureForTesting();
        }
    }
}
