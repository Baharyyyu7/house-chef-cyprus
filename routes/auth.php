<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    // İstek sınırı rotada değil, kayıt işleminin kendisinde
    // (pages.auth.register::register, IP başına saatte 5 deneme).
    //
    // Rotadaki throttle:registration yalnız SAYFAYI GÖRÜNTÜLEMEYİ sayıyordu:
    // form Livewire üzerinden gönderildiği için hiçbir kayıt denemesini
    // korumuyor, bir saatte altıncı kez sayfayı açan ziyaretçiye 429
    // veriyordu. 14 Eylül 2026'da art arda yayınların duman testi de aynı
    // sayfayı açtığı için iki yayın geri alındı.
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function (Logout $logout) {
        $logout();

        return redirect()->route('home');
    })->name('logout');

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
