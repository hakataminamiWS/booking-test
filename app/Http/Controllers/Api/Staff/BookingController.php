<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Staff\IndexBookingsRequest;
use App\Models\Booking;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\Request;
use App\Models\ShopStaff;
use App\Services\TimeSlotService;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        return ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function validateStaff(Request $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

        $request->validate([
            'menu_id' => ['required', 'integer'],
            'assigned_staff_id' => ['required', 'integer'],
        ]);

        $menuId = $request->input('menu_id');
        $staffId = $request->input('assigned_staff_id');

        $exists = \Illuminate\Support\Facades\DB::table('shop_menu_staffs')
            ->where('shop_menu_id', $menuId)
            ->where('shop_staff_id', $staffId)
            ->exists();

        return response()->json(['valid' => $exists]);
    }

    public function validateShift(Request $request, Shop $shop, TimeSlotService $timeSlotService): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

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

        $startOfDayUtc = $date->copy()->startOfDay()->setTimezone(config('app.timezone'));
        $endOfDayUtc = $date->copy()->endOfDay()->setTimezone(config('app.timezone'));

        $shifts = $staff->schedules()
            ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
            ->get()
            ->map(fn($schedule) => (object)[
                'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i')
            ])
            ->all();

        $valid = $timeSlotService->isWithinShift($startAt, $endAt, $shifts);

        return response()->json(['valid' => $valid]);
    }

    public function validateConflict(Request $request, Shop $shop, TimeSlotService $timeSlotService): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

        $request->validate([
            'assigned_staff_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
        ]);

        $date = Carbon::parse($request->input('start_at'));
        $startAt = $date->copy();
        
        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $options = ShopOption::find($request->input('option_ids') ?? []);
        $totalDuration = $menu->duration + $options->sum('additional_duration');
        
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        $staff = ShopStaff::findOrFail($request->input('assigned_staff_id'));
        
        $existingBookings = $staff->bookings()
            ->whereDate('start_at', $startAt->toDateString())
            ->get()
            ->map(fn($booking) => (object)[
                'start' => Carbon::parse($booking->start_at)->format('H:i'),
                'end' => Carbon::parse($booking->end_at)->format('H:i')
            ])
            ->all();
        
        $hasConflict = $timeSlotService->hasConflict($startAt, $endAt, $existingBookings);

        return response()->json(['valid' => !$hasConflict]);
    }

    public function getWorkingDays(Request $request, Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

        $request->validate([
            'year_month' => ['required', 'date_format:Y-m'],
        ]);

        $yearMonth = $request->input('year_month');
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $timezone = $shop->timezone; 
        
        $startUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endUtc = $endOfMonth->copy()->setTimezone('UTC');

        $scheduledDays = $staff->schedules()
            ->whereBetween('workable_start_at', [$startUtc, $endUtc])
            ->get()
            ->map(function ($schedule) use ($timezone) {
                return $schedule->workable_start_at->setTimezone($timezone)->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->all();

        return response()->json($scheduledDays);
    }

    public function index(IndexBookingsRequest $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

        $query = $shop->bookings();

        $query->with(['bookingOptions', 'booker']);

        if ($request->filled('start_at_from')) {
            $date = Carbon::parse($request->input('start_at_from'), $shop->timezone)->startOfDay();
            $query->where('start_at', '>=', $date->setTimezone(config('app.timezone')));
        }
        if ($request->filled('start_at_to')) {
            $date = Carbon::parse($request->input('start_at_to'), $shop->timezone)->endOfDay();
            $query->where('start_at', '<=', $date->setTimezone(config('app.timezone')));
        }
        if ($request->filled('booker_number')) {
            $query->whereHas('booker', function (Builder $q) use ($request) {
                $q->where('number', $request->input('booker_number'));
            });
        }
        if ($request->filled('booker_name')) {
            $query->where('booker_name', 'like', '%' . $request->input('booker_name') . '%');
        }
        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->input('menu_id'));
        }
        if ($request->filled('assigned_staff_id')) {
            $query->where('assigned_staff_id', $request->input('assigned_staff_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('booking_channel')) {
            $query->where('booking_channel', $request->input('booking_channel'));
        }

        $sortBy = $request->input('sort_by', 'start_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'booker_number') {
            $query->join('shop_bookers', 'bookings.shop_booker_id', '=', 'shop_bookers.id')
                ->select('bookings.*')
                ->orderBy('shop_bookers.number', $sortOrder);
        } elseif ($sortBy === 'total_price') {
            $query->withSum('bookingOptions as options_total_price', 'option_price');
            $query->orderByRaw('(menu_price + COALESCE(booking_options_sum_option_price, 0)) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->input('per_page', 20);
        $bookings = $query->paginate($perPage);

        $bookings->getCollection()->transform(function ($booking) {
            $optionsTotal = $booking->bookingOptions->sum('option_price');
            $booking->total_price = $booking->menu_price + $optionsTotal;
            $booking->booker_number = $booking->booker?->number;
            return $booking;
        });

        return response()->json($bookings);
    }
}
