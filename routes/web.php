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

    // --- Owner Application Routes ---
    Route::get('/contract-applications/create', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'create'])->name('contract.application.create');
    Route::post('/contract-applications', [\App\Http\Controllers\Owner\ContractApplicationController::class, 'store'])->name('contract.application.store');

    // --- Owner Routes ---
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

        Route::get('/shops/{shop:slug}/staff-applications', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'index'])->name('shops.staff-applications.index');
        Route::put('/shops/{shop:slug}/staff-applications/{staff_application}/approve', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'approve'])->name('shops.staff-applications.approve');
        Route::put('/shops/{shop:slug}/staff-applications/{staff_application}/reject', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'reject'])->name('shops.staff-applications.reject');

        Route::get('/shops/{shop:slug}/staffs', [App\Http\Controllers\Owner\ShopStaffController::class, 'index'])->name('shops.staffs.index');
        Route::get('/shops/{shop:slug}/staffs/{staff}/edit', [App\Http\Controllers\Owner\ShopStaffController::class, 'edit'])->name('shops.staffs.edit');
        Route::put('/shops/{shop:slug}/staffs/{staff}', [App\Http\Controllers\Owner\ShopStaffController::class, 'update'])->name('shops.staffs.update');
        Route::get('/shops/{shop:slug}/staffs/create', [App\Http\Controllers\Owner\ShopStaffController::class, 'create'])->name('shops.staffs.create');
        Route::post('/shops/{shop:slug}/staffs', [App\Http\Controllers\Owner\ShopStaffController::class, 'store'])->name('shops.staffs.store');

        // Shift Management
        Route::get('/shops/{shop:slug}/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'index'])->name('shops.shifts.index');
        Route::get('/shops/{shop:slug}/staffs/{staff}/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'edit'])->name('shops.staffs.shifts.edit');
        Route::put('/shops/{shop:slug}/staffs/{staff}/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'update'])->name('shops.staffs.shifts.update');

        // Menu Management
        Route::get('/shops/{shop:slug}/menus', [App\Http\Controllers\Owner\ShopMenuController::class, 'index'])->name('shops.menus.index');
        Route::get('/shops/{shop:slug}/menus/create', [App\Http\Controllers\Owner\ShopMenuController::class, 'create'])->name('shops.menus.create');
        Route::post('/shops/{shop:slug}/menus', [App\Http\Controllers\Owner\ShopMenuController::class, 'store'])->name('shops.menus.store');
        Route::get('/shops/{shop:slug}/menus/{menu}/edit', [App\Http\Controllers\Owner\ShopMenuController::class, 'edit'])->name('shops.menus.edit');
        Route::put('/shops/{shop:slug}/menus/{menu}', [App\Http\Controllers\Owner\ShopMenuController::class, 'update'])->name('shops.menus.update');
        Route::delete('/shops/{shop:slug}/menus/{menu}', [App\Http\Controllers\Owner\ShopMenuController::class, 'destroy'])->name('shops.menus.destroy');

        // Option Management
        Route::get('/shops/{shop:slug}/options', [App\Http\Controllers\Owner\ShopOptionController::class, 'index'])->name('shops.options.index');
        Route::get('/shops/{shop:slug}/options/create', [App\Http\Controllers\Owner\ShopOptionController::class, 'create'])->name('shops.options.create');
        Route::post('/shops/{shop:slug}/options', [App\Http\Controllers\Owner\ShopOptionController::class, 'store'])->name('shops.options.store');
        Route::get('/shops/{shop:slug}/options/{option}/edit', [App\Http\Controllers\Owner\ShopOptionController::class, 'edit'])->name('shops.options.edit');
        Route::put('/shops/{shop:slug}/options/{option}', [App\Http\Controllers\Owner\ShopOptionController::class, 'update'])->name('shops.options.update');
        Route::delete('/shops/{shop:slug}/options/{option}', [App\Http\Controllers\Owner\ShopOptionController::class, 'destroy'])->name('shops.options.destroy');

        // Booking Management
        Route::get('/shops/{shop:slug}/bookings', [App\Http\Controllers\Owner\BookingController::class, 'index'])->name('shops.bookings.index');
        Route::get('/shops/{shop:slug}/bookings/create', [App\Http\Controllers\Owner\BookingController::class, 'create'])->name('shops.bookings.create');
        Route::post('/shops/{shop:slug}/bookings', [App\Http\Controllers\Owner\BookingController::class, 'store'])->name('shops.bookings.store');
        Route::get('/shops/{shop:slug}/bookings/{booking}/edit', [App\Http\Controllers\Owner\BookingController::class, 'edit'])->name('shops.bookings.edit');
        Route::put('/shops/{shop:slug}/bookings/{booking}', [App\Http\Controllers\Owner\BookingController::class, 'update'])->name('shops.bookings.update');
        Route::delete('/shops/{shop:slug}/bookings/{booking}', [App\Http\Controllers\Owner\BookingController::class, 'destroy'])->name('shops.bookings.destroy');

        // Booker Management
        Route::get('/shops/{shop:slug}/bookers', [App\Http\Controllers\Owner\ShopBookerController::class, 'index'])->name('shops.bookers.index');
        Route::get('/shops/{shop:slug}/bookers/create', [App\Http\Controllers\Owner\ShopBookerController::class, 'create'])->name('shops.bookers.create');
        Route::post('/shops/{shop:slug}/bookers', [App\Http\Controllers\Owner\ShopBookerController::class, 'store'])->name('shops.bookers.store');
        Route::get('/shops/{shop:slug}/bookers/{booker}/edit', [App\Http\Controllers\Owner\ShopBookerController::class, 'edit'])->name('shops.bookers.edit');
        Route::put('/shops/{shop:slug}/bookers/{booker}', [App\Http\Controllers\Owner\ShopBookerController::class, 'update'])->name('shops.bookers.update');


        // API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/shops', [App\Http\Controllers\Api\Owner\ShopsController::class, 'index'])->name('shops.index');
            Route::get('/shops/validate-slug', [App\Http\Controllers\Api\Owner\ShopsController::class, 'validateSlug'])->name('shops.validate-slug');
            Route::get('/shops/{shop:slug}/staff-applications', [App\Http\Controllers\Api\Owner\ShopStaffApplicationController::class, 'index'])->name('api.shops.staff-applications.index');
            Route::get('/shops/{shop:slug}/staffs', [App\Http\Controllers\Api\Owner\ShopStaffController::class, 'index'])->name('api.shops.staffs.index');
            Route::get('/shops/{shop:slug}/menus', [App\Http\Controllers\Api\Owner\ShopMenuController::class, 'index'])->name('api.shops.menus.index');
            Route::get('/shops/{shop:slug}/menus/{menu}/staffs', [App\Http\Controllers\Api\Owner\ShopMenuController::class, 'staffs'])->name('api.shops.menus.staffs');
            Route::get('/shops/{shop:slug}/options', [App\Http\Controllers\Api\Owner\ShopOptionController::class, 'index'])->name('api.shops.options.index');
            Route::get('/shops/{shop:slug}/bookers', [App\Http\Controllers\Api\Owner\ShopBookerController::class, 'index'])->name('api.shops.bookers.index');
            Route::get('/shops/{shop:slug}/bookings/validate-staff', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateStaff'])->name('api.shops.bookings.validate-staff');
            Route::get('/shops/{shop:slug}/bookings/validate-shift', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateShift'])->name('api.shops.bookings.validate-shift');
            Route::get('/shops/{shop:slug}/bookings/validate-conflict', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateConflict'])->name('api.shops.bookings.validate-conflict');
            Route::get('/shops/{shop:slug}/staffs/{staff}/working-days', [App\Http\Controllers\Api\Owner\BookingController::class, 'getWorkingDays'])->name('api.shops.staffs.working-days');
            Route::get('/shops/{shop:slug}/bookings', [App\Http\Controllers\Api\Owner\BookingController::class, 'index'])->name('api.shops.bookings.index');
            Route::get('/shops/{shop:slug}/staffs/{staff}/timeslots', [App\Http\Controllers\Api\Owner\TimeSlotController::class, 'index'])->name('staffs.timeslots');
        });
    });

    // --- Staff Application Routes ---
    Route::get('/shops/{shop:slug}/staff/apply', [App\Http\Controllers\Staff\ApplicationController::class, 'create'])->name('staff.application.create');
    Route::post('/shops/{shop:slug}/staff/apply', [App\Http\Controllers\Staff\ApplicationController::class, 'store'])->name('staff.application.store');
    Route::get('/staff/apply/complete', [App\Http\Controllers\Staff\ApplicationController::class, 'complete'])->name('staff.application.complete');

    // --- Staff Routes ---
    Route::prefix('shops/{shop:slug}/staff')->name('staff.')->group(function () { // TODO: Add middleware('staff') later
        // Web
        Route::get('/profile', [App\Http\Controllers\Staff\ShopStaffController::class, 'edit'])->name('staffs.edit');
        Route::put('/profile', [App\Http\Controllers\Staff\ShopStaffController::class, 'update'])->name('staffs.update');
        // Shifts
        Route::get('/shifts', [App\Http\Controllers\Staff\ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/shifts/edit', [App\Http\Controllers\Staff\ShiftController::class, 'edit'])->name('shifts.edit');
        Route::put('/shifts/edit', [App\Http\Controllers\Staff\ShiftController::class, 'update'])->name('shifts.update');
        Route::get('/bookings', [App\Http\Controllers\Staff\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [App\Http\Controllers\Staff\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [App\Http\Controllers\Staff\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/edit', [App\Http\Controllers\Staff\BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [App\Http\Controllers\Staff\BookingController::class, 'update'])->name('bookings.update');
        Route::delete('/bookings/{booking}', [App\Http\Controllers\Staff\BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::get('/bookers', [App\Http\Controllers\Staff\ShopBookerController::class, 'index'])->name('bookers.index');
        Route::get('/bookers/create', [App\Http\Controllers\Staff\ShopBookerController::class, 'create'])->name('bookers.create');
        Route::post('/bookers', [App\Http\Controllers\Staff\ShopBookerController::class, 'store'])->name('bookers.store');
        Route::get('/bookers/{booker}/edit', [App\Http\Controllers\Staff\ShopBookerController::class, 'edit'])->name('bookers.edit');
        Route::put('/bookers/{booker}', [App\Http\Controllers\Staff\ShopBookerController::class, 'update'])->name('bookers.update');
        Route::get('/staffs', [App\Http\Controllers\Staff\ShopStaffController::class, 'index'])->name('staffs.index');

        // Staff API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/bookings', [App\Http\Controllers\Api\Staff\BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/validate-staff', [App\Http\Controllers\Api\Staff\BookingController::class, 'validateStaff'])->name('bookings.validate-staff');
            Route::get('/bookings/validate-shift', [App\Http\Controllers\Api\Staff\BookingController::class, 'validateShift'])->name('bookings.validate-shift');
            Route::get('/bookings/validate-conflict', [App\Http\Controllers\Api\Staff\BookingController::class, 'validateConflict'])->name('bookings.validate-conflict');
            Route::get('/staffs/{staff}/working-days', [App\Http\Controllers\Api\Staff\BookingController::class, 'getWorkingDays'])->name('staffs.working-days');
            Route::get('/bookers', [App\Http\Controllers\Api\Staff\ShopBookerController::class, 'index'])->name('bookers.index');
            Route::get('/staffs', [App\Http\Controllers\Api\Staff\ShopStaffController::class, 'index'])->name('staffs.index');
            Route::get('/menus/{menu}/staffs', [App\Http\Controllers\Api\Staff\ShopMenuController::class, 'staffs'])->name('menus.staffs');
            Route::get('/staffs/{staff}/timeslots', [App\Http\Controllers\Api\Staff\TimeSlotController::class, 'index'])->name('staffs.timeslots');
        });
    });
});

// ==============================================================================
// Debug Routes
// ==============================================================================
if (app()->environment(['local', 'staging'])) {
    Route::get('/login-as/{user}', [\App\Http\Controllers\DebugController::class, 'loginAs'])->name('debug.login-as');
}
