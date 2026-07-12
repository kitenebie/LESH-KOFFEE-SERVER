<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Custom admin routes (outside Filament) go here
        // Route::get('/qr-code', [AdminController::class, 'qrCode'])->name('qrcode');
    });
