<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use App\Models\ShopOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\TimeSlotService;
use App\Services\CancellationDeadlineService;

class BookingController extends Controller
{
    protected $timeSlotService;
    protected $cancellationDeadlineService;

    public function __construct(TimeSlotService $timeSlotService, CancellationDeadlineService $cancellationDeadlineService)
    {
        $this->timeSlotService = $timeSlotService;
        $this->cancellationDeadlineService = $cancellationDeadlineService;
    }

    public function validateStaff(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'menu_id' => ['required', 'integer'],
            'assigned_staff_id' => ['required', 'integer'],
        ]);

        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $staffId = $request->input('assigned_staff_id');

        if (!$menu->requires_staff_assignment) {
            $exists = $shop->staffs()->where('id', $staffId)->exists();
        } else {
            $exists = DB::table('shop_menu_staffs')
                ->where('shop_menu_id', $menu->id)
                ->where('shop_staff_id', $staffId)
                ->exists();
        }

        return response()->json(['valid' => $exists]);
    }

    public function validateShift(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'assigned_staff_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
        ]);

        $timezone = $shop->timezone;
        $date = Carbon::parse($request->input('start_at'), $timezone);
        $startAt = $date->copy();

        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $options = ShopOption::find($request->input('option_ids') ?? []);
        $totalDuration = $menu->duration + $options->sum('additional_duration');

        $endAt = $startAt->copy()->addMinutes($totalDuration);

        $staff = ShopStaff::findOrFail($request->input('assigned_staff_id'));

        $startOfDayUtc = $date->copy()->startOfDay()->setTimezone('UTC');
        $endOfDayUtc = $date->copy()->endOfDay()->setTimezone('UTC');

        $shifts = $staff->schedules()
            ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
            ->get()
            ->map(fn($schedule) => (object)[
                'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i')
            ])
            ->all();

        $valid = $this->timeSlotService->isWithinShift($startAt, $endAt, $shifts);

        return response()->json(['valid' => $valid]);
    }

    public function validateConflict(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'assigned_staff_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
        ]);

        $timezone = $shop->timezone;
        $date = Carbon::parse($request->input('start_at'), $timezone);
        $startAt = $date->copy();
        
        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $options = ShopOption::find($request->input('option_ids') ?? []);
        $totalDuration = $menu->duration + $options->sum('additional_duration');
        
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        $staff = ShopStaff::findOrFail($request->input('assigned_staff_id'));
        
        $existingBookings = $this->timeSlotService->getFormattedBookings($staff, $date, $timezone);
        
        $hasConflict = $this->timeSlotService->hasConflict($startAt, $endAt, $existingBookings);

        return response()->json(['valid' => !$hasConflict]);
    }

    public function getCancellationDeadline(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'menu_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
        ]);

        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $startAt = Carbon::parse($request->input('start_at'), $shop->timezone);

        $deadline = $this->cancellationDeadlineService->getFormattedDeadline($shop, $menu, $startAt);

        return response()->json([
            'cancellation_deadline' => $deadline,
        ]);
    }
}
