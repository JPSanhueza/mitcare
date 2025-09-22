<?php

use App\Http\Controllers\WebpayController;
use App\Http\Controllers\CartHttpController;
use App\Livewire\CartPage;
use App\Livewire\CheckoutPage;
use App\Livewire\Courses\ShowCourse;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/cursos/{course:slug}', ShowCourse::class)->name('courses.show');

Route::get('/carrito', CartPage::class)->name('cart.index');

Route::get('/checkout', CheckoutPage::class)->name('checkout.index');

Route::delete('/carrito/item/{key}', [CartHttpController::class, 'destroy'])
    ->name('cart.item.destroy');

Route::post('/carrito/clear', [CartHttpController::class, 'clear'])
    ->name('cart.clear');

Route::get('/checkout/pagar/{order}', [WebpayController::class, 'start'])
    ->name('webpay.start');

Route::post('/checkout/webpay/retorno', [WebpayController::class, 'callback'])
    ->name('webpay.callback');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');

//     Route::get('settings/profile', Profile::class)->name('settings.profile');
//     Route::get('settings/password', Password::class)->name('settings.password');
//     Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
// });

// require __DIR__.'/auth.php';
