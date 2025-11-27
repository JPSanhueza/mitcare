<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\CartServiceProvider::class,
    App\Providers\PaymentServiceProvider::class,
    SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
];
