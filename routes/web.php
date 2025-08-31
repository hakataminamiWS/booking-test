<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Booker（予約者向け）
Route::prefix('shops/{shop_id}/bookings')->name('booker.bookings.')->group(function () {
    Route::get('/', [App\Http\Controllers\Booker\BookingsController::class, 'index'])->name('index');
    Route::get('/new', [App\Http\Controllers\Booker\BookingsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Booker\BookingsController::class, 'store'])->name('store');
    Route::get('/complete', [App\Http\Controllers\Booker\BookingsController::class, 'complete'])->name('complete');
    Route::get('/{booking_id}', [App\Http\Controllers\Booker\BookingsController::class, 'show'])->name('show');
    Route::get('/{booking_id}/edit', [App\Http\Controllers\Booker\BookingsController::class, 'edit'])->name('edit');
    Route::put('/{booking_id}', [App\Http\Controllers\Booker\BookingsController::class, 'update'])->name('update');
});

// 2. Staff（店舗スタッフ向け）
Route::prefix('shops/{shop_id}/staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
    Route::get('bookings', [App\Http\Controllers\Staff\BookingsController::class, 'index'])->name('bookings.index');
    Route::get('bookings/new', [App\Http\Controllers\Staff\BookingsController::class, 'create'])->name('bookings.create'); // 新規予約作成フォーム
    Route::post('bookings', [App\Http\Controllers\Staff\BookingsController::class, 'store'])->name('bookings.store'); // 新規予約保存
    Route::get('bookings/confirm', [App\Http\Controllers\Staff\BookingsController::class, 'confirm'])->name('bookings.confirm'); // 予約確認画面
    Route::get('schedules', [App\Http\Controllers\Staff\SchedulesController::class, 'index'])->name('schedules.index'); // 予約可能枠管理
    Route::post('schedules', [App\Http\Controllers\Staff\SchedulesController::class, 'store'])->name('schedules.store'); // 予約可能枠保存
});

// 3. Owner（店舗オーナー向け）
Route::prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Owner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/shops', [App\Http\Controllers\Owner\ShopsController::class, 'index'])->name('shops.index');
    Route::get('/shops/{shop_id}', [App\Http\Controllers\Owner\ShopsController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop_id}/staff', [App\Http\Controllers\Owner\StaffController::class, 'index'])->name('shops.staff.index');
    Route::get('/shops/{shop_id}/staff/{staff_id}/edit', [App\Http\Controllers\Owner\StaffController::class, 'edit'])->name('shops.staff.edit');
    Route::get('/contracts', [App\Http\Controllers\Owner\ContractsController::class, 'index'])->name('contracts.index');
});

// 4. Admin（全体管理者向け）
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('owners', [App\Http\Controllers\Admin\OwnersController::class, 'index'])->name('owners.index');
    Route::get('owners/{owner_id}', [App\Http\Controllers\Admin\OwnersController::class, 'show'])->name('owners.show');
    Route::get('contracts', [App\Http\Controllers\Admin\ContractsController::class, 'index'])->name('contracts.index');
    Route::get('contracts/{contract_id}', [App\Http\Controllers\Admin\ContractsController::class, 'show'])->name('contracts.show');
});


// API Routes
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/shops/{shop_id}/availability', [App\Http\Controllers\Booker\BookingsController::class, 'getAvailability'])->name('booker.availability');
});