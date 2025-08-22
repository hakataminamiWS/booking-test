<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Booker\BookingsController;

// booker
// 予約フォーム
Route::prefix('booker/bookings')->name('booker.bookings.')->group(function () {
    Route::get('/create', [BookingsController::class, 'create'])->name('create');
    Route::post('/preview', [BookingsController::class, 'preview'])->name('preview');
    Route::get('/confirm', [BookingsController::class, 'confirm'])->name('confirm');
    Route::post('/', [BookingsController::class, 'store'])->name('store');
    Route::get('/complete', [BookingsController::class, 'complete'])->name('complete');
});

// API
Route::prefix('api')->name('api.')->group(function () {
    Route::prefix('booker/bookings')->name('booker.bookings.')->group(function () {
        Route::get('/availability', [BookingsController::class, 'getAvailability'])->name('availability');
    });
});
