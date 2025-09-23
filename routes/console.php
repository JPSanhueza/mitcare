<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:webpay:expire --ttl=30')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer()          // si tienes varios pods/instancias
    ->runInBackground();      // no bloquea el scheduler
    // ->environments(['production']) // opcional
