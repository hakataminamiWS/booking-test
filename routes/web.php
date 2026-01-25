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

// --- Shop Entry Route ---
    Route::get('/shops/{shop:slug}', [App\Http\Controllers\ShopEntryController::class, 'show'])->name('shop.entry');

    // --- Guest Booking Routes ---
    Route::prefix('shops/{shop:slug}/guest')->name('guest.')->scopeBindings()->group(function () {
    Route::get('/bookings/create', [App\Http\Controllers\Guest\BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [App\Http\Controllers\Guest\BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/provisional', [App\Http\Controllers\Guest\BookingController::class, 'provisional'])->name('bookings.provisional');

    // 予約確定 (Signed URL)
    Route::get('/bookings/{booking}/verify', [App\Http\Controllers\Guest\BookingController::class, 'verify'])
        ->name('bookings.verify')
        ->middleware('signed');

    // 予約キャンセル (Signed URL)
    Route::get('/bookings/{booking}/cancel', [App\Http\Controllers\Guest\BookingCancellationController::class, 'show'])
        ->name('bookings.cancel.show')
        ->middleware('signed');
    Route::post('/bookings/{booking}/cancel', [App\Http\Controllers\Guest\BookingCancellationController::class, 'perform'])
        ->name('bookings.cancel.perform')
        ->middleware('signed');

    // Guest API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/available-slots', [App\Http\Controllers\Api\Booker\AvailableSlotController::class, 'index'])->name('available-slots.index');
        Route::get('/menus/{menu}/staffs', [App\Http\Controllers\Api\Booker\ShopMenuController::class, 'staffs'])->name('menus.staffs');
        Route::get('/staffs/{staff}/working-days', [App\Http\Controllers\Api\Booker\WorkingDayController::class, 'index'])->name('staffs.working-days');
        
        Route::get('/bookings/validate-staff', [App\Http\Controllers\Api\Guest\BookingController::class, 'validateStaff'])->name('bookings.validate-staff');
        Route::get('/bookings/validate-shift', [App\Http\Controllers\Api\Guest\BookingController::class, 'validateShift'])->name('bookings.validate-shift');
        Route::get('/bookings/validate-conflict', [App\Http\Controllers\Api\Guest\BookingController::class, 'validateConflict'])->name('bookings.validate-conflict');
        Route::get('/bookings/cancellation-deadline', [App\Http\Controllers\Api\Guest\BookingController::class, 'getCancellationDeadline'])->name('bookings.cancellation-deadline');
    });
});

// ==============================================================================
// Authenticated Routes (Login Required)
// ==============================================================================

Route::middleware('auth')->group(function () {

    // --- Logout Route ---
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        $redirectTo = $request->input('redirect_to', '/');
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect($redirectTo);
    })->name('logout');

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

        // Shop Specific Routes (Authorized)
        Route::prefix('shops/{shop:slug}')->middleware('can:view,shop')->scopeBindings()->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Owner\ShopDashboardController::class, 'index'])->name('shops.dashboard');
            Route::get('/', [App\Http\Controllers\Owner\ShopsController::class, 'show'])->name('shops.show');
            Route::get('/edit', [App\Http\Controllers\Owner\ShopsController::class, 'edit'])->name('shops.edit');
            Route::put('/', [App\Http\Controllers\Owner\ShopsController::class, 'update'])->name('shops.update');
            Route::delete('/', [App\Http\Controllers\Owner\ShopsController::class, 'destroy'])->name('shops.destroy');
            Route::get('/business-hours', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'index'])->name('shops.business-hours.index');
            Route::get('/business-hours/regular/edit', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'edit'])->name('shops.business-hours.regular.edit');
            Route::put('/business-hours/regular', [App\Http\Controllers\Owner\ShopBusinessHoursController::class, 'update'])->name('shops.business-hours.regular.update');
            Route::get('/business-hours/special-open-days/create', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'create'])->name('shops.business-hours.special-open-days.create');
            Route::post('/business-hours/special-open-days', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'store'])->name('shops.business-hours.special-open-days.store');
            Route::get('/business-hours/special-open-days/{special_open_day}/edit', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'edit'])->name('shops.business-hours.special-open-days.edit');
            Route::put('/business-hours/special-open-days/{special_open_day}', [App\Http\Controllers\Owner\ShopSpecialOpenDaysController::class, 'update'])->name('shops.business-hours.special-open-days.update');
            Route::get('/business-hours/special-closed-days/create', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'create'])->name('shops.business-hours.special-closed-days.create');
            Route::post('/business-hours/special-closed-days', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'store'])->name('shops.business-hours.special-closed-days.store');
            Route::get('/business-hours/special-closed-days/{special_closed_day}/edit', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'edit'])->name('shops.business-hours.special-closed-days.edit');
            Route::put('/business-hours/special-closed-days/{special_closed_day}', [App\Http\Controllers\Owner\ShopSpecialClosedDaysController::class, 'update'])->name('shops.business-hours.special-closed-days.update');

            Route::get('/staff-applications', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'index'])->name('shops.staff-applications.index');
            Route::get('/staff-applications/share', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'share'])->name('shops.staff-applications.share');
            Route::put('/staff-applications/{staff_application}/approve', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'approve'])->name('shops.staff-applications.approve');
            Route::put('/staff-applications/{staff_application}/reject', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'reject'])->name('shops.staff-applications.reject');
            Route::delete('/staff-applications/{staff_application}', [App\Http\Controllers\Owner\ShopStaffApplicationController::class, 'destroy'])->name('shops.staff-applications.destroy');

            Route::get('/staffs', [App\Http\Controllers\Owner\ShopStaffController::class, 'index'])->name('shops.staffs.index');
            Route::get('/staffs/{staff}/edit', [App\Http\Controllers\Owner\ShopStaffController::class, 'edit'])->name('shops.staffs.edit');
            Route::put('/staffs/{staff}', [App\Http\Controllers\Owner\ShopStaffController::class, 'update'])->name('shops.staffs.update');
            Route::get('/staffs/create', [App\Http\Controllers\Owner\ShopStaffController::class, 'create'])->name('shops.staffs.create');
            Route::post('/staffs', [App\Http\Controllers\Owner\ShopStaffController::class, 'store'])->name('shops.staffs.store');
            Route::delete('/staffs/{staff}', [App\Http\Controllers\Owner\ShopStaffController::class, 'destroy'])->name('shops.staffs.destroy');

            // Shift Management
            Route::get('/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'index'])->name('shops.shifts.index');
            Route::get('/staffs/{staff}/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'edit'])->name('shops.staffs.shifts.edit');
            Route::put('/staffs/{staff}/shifts', [App\Http\Controllers\Owner\ShopStaffScheduleController::class, 'update'])->name('shops.staffs.shifts.update');

            // Menu Management
            Route::get('/menus', [App\Http\Controllers\Owner\ShopMenuController::class, 'index'])->name('shops.menus.index');
            Route::get('/menus/create', [App\Http\Controllers\Owner\ShopMenuController::class, 'create'])->name('shops.menus.create');
            Route::post('/menus', [App\Http\Controllers\Owner\ShopMenuController::class, 'store'])->name('shops.menus.store');
            Route::get('/menus/{menu}/edit', [App\Http\Controllers\Owner\ShopMenuController::class, 'edit'])->name('shops.menus.edit');
            Route::put('/menus/{menu}', [App\Http\Controllers\Owner\ShopMenuController::class, 'update'])->name('shops.menus.update');
            Route::delete('/menus/{menu}', [App\Http\Controllers\Owner\ShopMenuController::class, 'destroy'])->name('shops.menus.destroy');

            // Option Management
            Route::get('/options', [App\Http\Controllers\Owner\ShopOptionController::class, 'index'])->name('shops.options.index');
            Route::get('/options/create', [App\Http\Controllers\Owner\ShopOptionController::class, 'create'])->name('shops.options.create');
            Route::post('/options', [App\Http\Controllers\Owner\ShopOptionController::class, 'store'])->name('shops.options.store');
            Route::get('/options/{option}/edit', [App\Http\Controllers\Owner\ShopOptionController::class, 'edit'])->name('shops.options.edit');
            Route::put('/options/{option}', [App\Http\Controllers\Owner\ShopOptionController::class, 'update'])->name('shops.options.update');
            Route::delete('/options/{option}', [App\Http\Controllers\Owner\ShopOptionController::class, 'destroy'])->name('shops.options.destroy');

            // Booking Management
            Route::get('/bookings', [App\Http\Controllers\Owner\BookingController::class, 'index'])->name('shops.bookings.index');
            Route::get('/bookings/create', [App\Http\Controllers\Owner\BookingController::class, 'create'])->name('shops.bookings.create');
            Route::post('/bookings', [App\Http\Controllers\Owner\BookingController::class, 'store'])->name('shops.bookings.store');
            Route::get('/bookings/{booking}/edit', [App\Http\Controllers\Owner\BookingController::class, 'edit'])->name('shops.bookings.edit');
            Route::put('/bookings/{booking}', [App\Http\Controllers\Owner\BookingController::class, 'update'])->name('shops.bookings.update');
            Route::delete('/bookings/{booking}', [App\Http\Controllers\Owner\BookingController::class, 'destroy'])->name('shops.bookings.destroy');

            // Booker Management
            Route::get('/bookers', [App\Http\Controllers\Owner\ShopBookerController::class, 'index'])->name('shops.bookers.index');
            Route::get('/bookers/create', [App\Http\Controllers\Owner\ShopBookerController::class, 'create'])->name('shops.bookers.create');
            Route::post('/bookers', [App\Http\Controllers\Owner\ShopBookerController::class, 'store'])->name('shops.bookers.store');
            Route::get('/bookers/{booker}/edit', [App\Http\Controllers\Owner\ShopBookerController::class, 'edit'])->name('shops.bookers.edit');
            Route::put('/bookers/{booker}', [App\Http\Controllers\Owner\ShopBookerController::class, 'update'])->name('shops.bookers.update');
        });

        // API
        Route::prefix('api/shops/{shop:slug}')->name('api.shops.')->middleware('can:view,shop')->group(function () {
             // API routes authorized
             Route::get('/staff-applications', [App\Http\Controllers\Api\Owner\ShopStaffApplicationController::class, 'index'])->name('staff-applications.index');
             Route::get('/staffs', [App\Http\Controllers\Api\Owner\ShopStaffController::class, 'index'])->name('staffs.index');
             Route::get('/menus', [App\Http\Controllers\Api\Owner\ShopMenuController::class, 'index'])->name('menus.index');
             Route::get('/menus/{menu}/staffs', [App\Http\Controllers\Api\Owner\ShopMenuController::class, 'staffs'])->name('menus.staffs');
             Route::get('/options', [App\Http\Controllers\Api\Owner\ShopOptionController::class, 'index'])->name('options.index');
             Route::get('/bookers', [App\Http\Controllers\Api\Owner\ShopBookerController::class, 'index'])->name('bookers.index');
             Route::get('/bookers/{booker}/history', [App\Http\Controllers\Api\Owner\ShopBookerController::class, 'history'])->name('bookers.history');
             Route::get('/bookings/validate-staff', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateStaff'])->name('bookings.validate-staff');
             Route::get('/bookings/validate-shift', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateShift'])->name('bookings.validate-shift');
             Route::get('/bookings/validate-conflict', [App\Http\Controllers\Api\Owner\BookingController::class, 'validateConflict'])->name('bookings.validate-conflict');
             Route::get('/staffs/{staff}/working-days', [App\Http\Controllers\Api\Owner\BookingController::class, 'getWorkingDays'])->name('staffs.working-days');
             Route::get('/bookings', [App\Http\Controllers\Api\Owner\BookingController::class, 'index'])->name('bookings.index');
             Route::get('/staffs/{staff}/timeslots', [App\Http\Controllers\Api\Owner\TimeSlotController::class, 'index'])->name('staffs.timeslots.index'); // Fixed name locally collision if any
             Route::get('/staffs/{staff}/schedule', [App\Http\Controllers\Api\Owner\ShopStaffController::class, 'getSchedule'])->name('staffs.schedule');
             Route::post('/test-email', [App\Http\Controllers\Api\Owner\ShopsController::class, 'testEmail'])->name('test-email');
        });
        
        // General Wrapper API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/shops', [App\Http\Controllers\Api\Owner\ShopsController::class, 'index'])->name('shops.index');
            Route::get('/shops/validate-slug', [App\Http\Controllers\Api\Owner\ShopsController::class, 'validateSlug'])->name('shops.validate-slug');
             // other generic api routes
        });
    });

    // --- Staff Application Routes ---
    Route::get('/shops/{shop:slug}/staff/apply', [App\Http\Controllers\Staff\ApplicationController::class, 'create'])->name('staff.application.create');
    Route::post('/shops/{shop:slug}/staff/apply', [App\Http\Controllers\Staff\ApplicationController::class, 'store'])->name('staff.application.store');
    Route::get('/shops/{shop:slug}/staff/apply/complete', [App\Http\Controllers\Staff\ApplicationController::class, 'complete'])->name('staff.application.complete');

    // --- Staff Routes ---
    // --- Staff Routes ---
    Route::prefix('shops/{shop:slug}/staff')->name('staff.')->middleware(['can:viewAsStaff,shop'])->scopeBindings()->group(function () {
        // Web
        Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
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
            Route::get('/bookers/{booker}/history', [App\Http\Controllers\Api\Staff\ShopBookerController::class, 'history'])->name('bookers.history');
            Route::get('/staffs', [App\Http\Controllers\Api\Staff\ShopStaffController::class, 'index'])->name('staffs.index');
            Route::get('/menus/{menu}/staffs', [App\Http\Controllers\Api\Staff\ShopMenuController::class, 'staffs'])->name('menus.staffs');
            Route::get('/staffs/{staff}/timeslots', [App\Http\Controllers\Api\Staff\TimeSlotController::class, 'index'])->name('staffs.timeslots');
            Route::get('/staffs/{staff}/schedule', [App\Http\Controllers\Api\Staff\ShopStaffController::class, 'getSchedule'])->name('staffs.schedule');
        });
    });

    // --- Booker Routes ---
    Route::prefix('booker')->name('booker.')->group(function () {
        // Web
        Route::get('/shops', [App\Http\Controllers\Booker\ShopsController::class, 'index'])->name('shops.index');

        // API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/shops', [App\Http\Controllers\Api\Booker\ShopsController::class, 'index'])->name('shops.index');
        });
    });


    
    // Booker Registration Routes (No 'can:viewAsBooker' check yet)
    Route::prefix('shops/{shop:slug}/booker')->name('booker.')->group(function () {
        Route::get('/profile/create', [App\Http\Controllers\Booker\ProfileController::class, 'create'])->name('profile.create');
        Route::post('/profile', [App\Http\Controllers\Booker\ProfileController::class, 'store'])->name('profile.store');
    });

    // Booker Authorized Routes
    // Booker Authorized Routes
    Route::prefix('shops/{shop:slug}/booker')->name('booker.')->middleware(['can:viewAsBooker,shop'])->scopeBindings()->group(function () {
        Route::get('/', [App\Http\Controllers\Booker\ShopController::class, 'show'])->name('shop.show');
        Route::get('/profile/edit', [App\Http\Controllers\Booker\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Booker\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\Booker\ProfileController::class, 'destroy'])->name('profile.destroy');

        // Bookings
        Route::get('/bookings', [App\Http\Controllers\Booker\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [App\Http\Controllers\Booker\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [App\Http\Controllers\Booker\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/provisional', [App\Http\Controllers\Booker\BookingController::class, 'provisional'])->name('bookings.provisional');
        Route::get('/bookings/{booking}', [App\Http\Controllers\Booker\BookingController::class, 'show'])->name('bookings.show');
        Route::delete('/bookings/{booking}', [App\Http\Controllers\Booker\BookingController::class, 'destroy'])->name('bookings.destroy');

        // API
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/bookings', [App\Http\Controllers\Api\Booker\BookingController::class, 'index'])->name('bookings.index');
            Route::get('/menus/{menu}/staffs', [App\Http\Controllers\Api\Booker\ShopMenuController::class, 'staffs'])->name('menus.staffs');
            Route::get('/bookings/validate-staff', [App\Http\Controllers\Api\Booker\BookingController::class, 'validateStaff'])->name('bookings.validate-staff');
            Route::get('/bookings/validate-shift', [App\Http\Controllers\Api\Booker\BookingController::class, 'validateShift'])->name('bookings.validate-shift');
            Route::get('/bookings/validate-conflict', [App\Http\Controllers\Api\Booker\BookingController::class, 'validateConflict'])->name('bookings.validate-conflict');
            Route::get('/bookings/cancellation-deadline', [App\Http\Controllers\Api\Booker\BookingController::class, 'getCancellationDeadline'])->name('bookings.cancellation-deadline');
            Route::get('/available-slots', [App\Http\Controllers\Api\Booker\AvailableSlotController::class, 'index'])->name('available-slots.index');
            Route::get('/staffs/{staff}/working-days', [App\Http\Controllers\Api\Booker\WorkingDayController::class, 'index'])->name('staffs.working-days');
        });
    });
});

// ==============================================================================
// Debug Routes
// ==============================================================================
if (app()->environment(['local', 'staging'])) {
    Route::get('/login-as-unregistered/{shop:slug}', [\App\Http\Controllers\DebugController::class, 'loginAsUnregistered'])->name('debug.login-as-unregistered');
    Route::get('/login-as/{user}', [\App\Http\Controllers\DebugController::class, 'loginAs'])->name('debug.login-as');
}
