<?php

namespace App\Http\Controllers\Api\Booker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Booker\IndexBookingsRequest;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ShopStaff;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use App\Services\TimeSlotService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->timeSlotService = $timeSlotService;
    }
    /**
     * Get a paginated list of bookings for the authenticated booker.
     */
    public function index(IndexBookingsRequest $request, Shop $shop): JsonResponse
    {
        $booker = $this->getAuthenticatedBooker($shop);

        $query = Booking::where('shop_id', $shop->id)
            ->where('shop_booker_id', $booker->id)
            ->with(['staff.profile']);

        // Filtering by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtering by date range
        if ($request->filled('start_at_from')) {
            $query->where('start_at', '>=', \Illuminate\Support\Carbon::parse($request->start_at_from, $shop->timezone)->startOfDay()->setTimezone(config('app.timezone')));
        }
        if ($request->filled('start_at_to')) {
            $query->where('start_at', '<=', \Illuminate\Support\Carbon::parse($request->start_at_to, $shop->timezone)->endOfDay()->setTimezone(config('app.timezone')));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'start_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate($request->input('per_page', 20));

        return response()->json($bookings);
    }

    public function validateStaff(Request $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedBooker($shop);

        $request->validate([
            'menu_id' => ['required', 'integer'],
            'assigned_staff_id' => ['required', 'integer'],
        ]);

        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $staffId = $request->input('assigned_staff_id');

        if (!$menu->requires_staff_assignment) {
            // スタッフ指名が不要なメニューの場合、スタッフが店舗に所属していればOK
            $exists = $shop->staffs()->where('id', $staffId)->exists();
        } else {
            // スタッフ指名が必要なメニューの場合、紐付けを確認
            $exists = DB::table('shop_menu_staffs')
                ->where('shop_menu_id', $menu->id)
                ->where('shop_staff_id', $staffId)
                ->exists();
        }

        return response()->json(['valid' => $exists]);
    }

    public function validateShift(Request $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedBooker($shop);

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

        $valid = $this->timeSlotService->isWithinShift($startAt, $endAt, $shifts);

        return response()->json(['valid' => $valid]);
    }

    public function validateConflict(Request $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedBooker($shop);

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

        $existingBookings = $staff->bookings()
            ->whereBetween('start_at', [$startOfDayUtc, $endOfDayUtc])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->map(fn($booking) => (object)[
                'start' => Carbon::parse($booking->start_at)->setTimezone($timezone)->format('H:i'),
                'end' => Carbon::parse($booking->end_at)->setTimezone($timezone)->format('H:i')
            ])
            ->all();
        
        $hasConflict = $this->timeSlotService->hasConflict($startAt, $endAt, $existingBookings);

        return response()->json(['valid' => !$hasConflict]);
    }

    private function getAuthenticatedBooker(Shop $shop): ShopBooker
    {
        $booker = ShopBooker::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $booker;
    }
}
