<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| このファイルは、ブラウザからアクセスされる全てのルートを定義します。
| ルートは以下のセクションに分かれており、セッションや認証が必要なルートは
| 'auth'ミドルウェアグループ内で定義されています。
|
*/

// ==============================================================================
// Public Routes (No Authentication Required)
// ==============================================================================

// --- Public Web Pages ---
Route::get('/', function () {
    return view('welcome');
});

// ==============================================================================
// Authenticated Routes (Login Required)
// ==============================================================================

Route::middleware('auth')->group(function () {

    // --- Admin Routes ---
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // Web
        Route::resource('contract-applications', App\Http\Controllers\Admin\ContractApplicationsController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('contracts', App\Http\Controllers\Admin\ContractsController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        // API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/contract-applications', [App\Http\Controllers\Api\Admin\ContractApplicationsController::class, 'index'])->name('contract-applications.index');
            Route::get('/contracts', [App\Http\Controllers\Api\Admin\ContractsController::class, 'index'])->name('contracts.index');
        });
    });

    // --- Owner Routes ---
    Route::get('/contract-applications/create', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'create'])->name('contract.application.create');
    Route::post('/contract-applications', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'store'])->name('contract.application.store');

    Route::prefix('owner')->name('owner.')->middleware('owner')->group(function () {
        // Web
        Route::get('/shops', [App\Http\Controllers\Owner\ShopsController::class, 'index'])->name('shops.index');
        Route::get('/shops/create', [App\Http\Controllers\Owner\ShopsController::class, 'create'])->name('shops.create');
        Route::post('/shops', [App\Http\Controllers\Owner\ShopsController::class, 'store'])->name('shops.store');
        Route::get('/shops/{shop:slug}', [App\Http\Controllers\Owner\ShopsController::class, 'show'])->name('shops.show');
        Route::get('/shops/{shop:slug}/edit', [App\Http\Controllers\Owner\ShopsController::class, 'edit'])->name('shops.edit');
        Route::put('/shops/{shop:slug}', [App\Http\Controllers\Owner\ShopsController::class, 'update'])->name('shops.update');
        Route::delete('/shops/{shop:slug}', [App\Http\Controllers\Owner\ShopsController::class, 'destroy'])->name('shops.destroy');
        Route::get('/shops/{shop:slug}/business-hours', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'index'])->name('shops.business-hours.index');
        Route::get('/shops/{shop:slug}/business-hours/regular/edit', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'edit'])->name('shops.business-hours.regular.edit');
        Route::put('/shops/{shop:slug}/business-hours/regular', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'update'])->name('shops.business-hours.regular.update');
        Route::get('/shops/{shop:slug}/business-hours/special-open-days/create', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'create'])->name('shops.business-hours.special-open-days.create');
        Route::post('/shops/{shop:slug}/business-hours/special-open-days', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'store'])->name('shops.business-hours.special-open-days.store');
        Route::get('/shops/{shop:slug}/business-hours/special-open-days/{special_open_day}/edit', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'edit'])->name('shops.business-hours.special-open-days.edit');
        Route::put('/shops/{shop:slug}/business-hours/special-open-days/{special_open_day}', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'update'])->name('shops.business-hours.special-open-days.update');
        Route::get('/shops/{shop:slug}/business-hours/special-closed-days/create', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'create'])->name('shops.business-hours.special-closed-days.create');
        Route::post('/shops/{shop:slug}/business-hours/special-closed-days', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'store'])->name('shops.business-hours.special-closed-days.store');
        Route::get('/shops/{shop:slug}/business-hours/special-closed-days/{special_closed_day}/edit', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'edit'])->name('shops.business-hours.special-closed-days.edit');
        Route::put('/shops/{shop:slug}/business-hours/special-closed-days/{special_closed_day}', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'update'])->name('shops.business-hours.special-closed-days.update');

        // API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/shops', [App\Http\Controllers\Api\Owner\ShopsController::class, 'index'])->name('shops.index');
            Route::get('/shops/validate-slug', [App\Http\Controllers\Api\Owner\ShopsController::class, 'validateSlug'])->name('shops.validate-slug');
        });
    });

    // --- Staff Routes ---
    Route::prefix('shops/{shop}/staff')->name('staff.')->group(function () { // TODO: Add middleware('staff') later
        // Web

        // API (Placeholder)
        // Route::prefix('api')->name('api.')->group(function () {
        //     // Future staff APIs go here
        // });
    });

});

// ==============================================================================
// Debug Routes
// ==============================================================================
if (app()->environment(['local', 'staging'])) {
    Route::get('/login-as/{user}', [\App\Http\Controllers\DebugController::class, 'loginAs'])->name('debug.login-as');
}
