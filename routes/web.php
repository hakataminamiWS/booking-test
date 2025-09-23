<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('app');
});

// 1. Booker（予約者向け）
Route::prefix('shops/{shop}/bookings')->name('booker.bookings.')->group(function () {
    Route::get('/', [App\Http\Controllers\Booker\BookingsController::class, 'index'])->name('index');
    Route::get('/new', [App\Http\Controllers\Booker\BookingsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Booker\BookingsController::class, 'store'])->name('store');
    Route::get('/complete', [App\Http\Controllers\Booker\BookingsController::class, 'complete'])->name('complete');
    Route::get('/{booking_id}', [App\Http\Controllers\Booker\BookingsController::class, 'show'])->name('show');
    Route::get('/{booking_id}/edit', [App\Http\Controllers\Booker\BookingsController::class, 'edit'])->name('edit');
    Route::put('/{booking_id}', [App\Http\Controllers\Booker\BookingsController::class, 'update'])->name('update');
});

// 2. Staff（店舗スタッフ向け）
Route::prefix('shops/{shop}/staff')->name('staff.')->group(function () {
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
    Route::get('/shops/{shop}', [App\Http\Controllers\Owner\ShopsController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop}/edit', [App\Http\Controllers\Owner\ShopsController::class, 'edit'])->name('shops.edit');
    Route::put('/shops/{shop}', [App\Http\Controllers\Owner\ShopsController::class, 'update'])->name('shops.update');
    Route::get('/shops/{shop_id}/staff', [App\Http\Controllers\Owner\StaffController::class, 'index'])->name('shops.staff.index');
    Route::get('/shops/{shop_id}/staff/{staff_id}/edit', [App\Http\Controllers\Owner\StaffController::class, 'edit'])->name('shops.staff.edit');
    Route::get('/contracts', [App\Http\Controllers\Owner\ContractsController::class, 'index'])->name('contracts.index');
});

// 4. Admin（全体管理者向け）
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UsersController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('contract-applications', App\Http\Controllers\Admin\ContractApplicationController::class)->only(['index']);

    Route::resource('contracts', App\Http\Controllers\Admin\ContractsController::class)->except([]);

    // 店舗管理のルート
    Route::get('shops', function () {
        return view('admin.shops');
    })->name('shops.index');

    Route::resource('shops', App\Http\Controllers\Admin\ShopsController::class)->except(['index', 'create', 'show', 'edit']);
    Route::get('shops/create', function () {
        return view('admin.shops');
    })->name('shops.create');
    Route::get('shops/{shop}', function () {
        return view('admin.shops');
    })->name('shops.show');
    Route::get('shops/{shop}/edit', function () {
        return view('admin.shops');
    })->name('shops.edit');

    Route::delete('shops/{shop}/force-delete', [App\Http\Controllers\Admin\ShopsController::class, 'forceDelete'])->name('shops.forceDelete');
});


// API Routes
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/shops/{shop}/availability', [App\Http\Controllers\Booker\BookingsController::class, 'getAvailability'])->name('booker.availability');
    Route::get('/shops/{shop}/menus', [App\Http\Controllers\Api\MenuController::class, 'index'])->name('shops.menus.index');
    Route::get('/shops/{shop}/staff', [App\Http\Controllers\Api\StaffController::class, 'index'])->name('shops.staff.index');
    Route::get('/shops/{shop}/available-slots', [App\Http\Controllers\Api\AvailabilityController::class, 'index'])->name('shops.available-slots.index');
    Route::get('/admin/users', [App\Http\Controllers\Api\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/contract-applications', [App\Http\Controllers\Api\Admin\ContractApplicationController::class, 'index'])->name('admin.contract-applications.index');
});

// Debug routes
if (app()->environment(['local', 'staging'])) {
    Route::get('/login-as/{user}', [\App\Http\Controllers\DebugController::class, 'loginAs'])->name('debug.login-as');
}

Route::middleware('auth')->group(function () {
    Route::get('/apply-contract', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'create'])->name('contract.application.create');
    Route::post('/apply-contract', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'store'])->name('contract.application.store');
});