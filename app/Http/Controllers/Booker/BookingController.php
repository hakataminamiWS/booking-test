<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booker\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the booker's bookings.
     */
    public function index(Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);

        return view('booker.bookings.index', [
            'shop' => $shop,
            'booker' => $booker,
        ]);
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);

        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);

        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get();
        
        $bookings = $shop->bookings()
            ->where('start_at', '>=', now())
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('booker.bookings.create', [
            'shop' => $shop,
            'booker' => $booker,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request, Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);
        $validated = $request->validated();

        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = $menu->options()->whereIn('shop_options.id', $validated['option_ids'] ?? [])->get();
        $staff = isset($validated['assigned_staff_id']) ? ShopStaff::with('profile')->find($validated['assigned_staff_id']) : null;

        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->additional_duration;
        }

        $startAt = Carbon::parse($validated['start_at'], $shop->timezone)->setTimezone(config('app.timezone'));
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $booker, $startAt, $endAt) {
            $booking = $shop->bookings()->create([
                'shop_booker_id' => $booker->id,
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => 'confirmed',
                'booking_channel' => 'web',
                'menu_name' => $menu->name,
                'menu_price' => $menu->price,
                'menu_duration' => $menu->duration,
                'assigned_staff_name' => $staff->profile->nickname ?? null,
                'booker_name' => $booker->name,
                'contact_email' => $booker->contact_email,
                'contact_phone' => $booker->contact_phone,
                'note_from_booker' => $validated['note_from_booker'] ?? null,
                'timezone' => $shop->timezone,
            ]);

            if ($options->isNotEmpty()) {
                $bookingOptions = $options->map(function ($opt) {
                    return [
                        'option_id' => $opt->id,
                        'option_name' => $opt->name,
                        'option_price' => $opt->price,
                        'option_duration' => $opt->additional_duration,
                    ];
                });
                $booking->bookingOptions()->createMany($bookingOptions->all());
            }
        });

        return redirect()->route('booker.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を登録しました。');
    }

    /**
     * Display the specified booking.
     */
    public function show(Shop $shop, Booking $booking)
    {
        $booker = $this->getAuthenticatedBooker($shop);

        // Ensure the booking belongs to this booker
        if ($booking->shop_booker_id !== $booker->id) {
            abort(403);
        }

        $booking->load(['bookingOptions', 'staff.profile', 'menu']);

        // Calculate cancellation deadline minutes for frontend
        $deadlineMinutes = $shop->cancellation_deadline_minutes ?? 1440;
        if ($booking->menu && $booking->menu->requires_cancellation_deadline) {
            $deadlineMinutes = $booking->menu->cancellation_deadline_minutes ?? $deadlineMinutes;
        }

        return view('booker.bookings.show', [
            'shop' => $shop,
            'booker' => $booker,
            'booking' => $booking,
            'cancellationDeadlineMinutes' => $deadlineMinutes,
        ]);
    }

    /**
     * Cancel the specified booking.
     */
    public function destroy(Shop $shop, Booking $booking)
    {
        $booker = $this->getAuthenticatedBooker($shop);

        // Ensure the booking belongs to this booker
        if ($booking->shop_booker_id !== $booker->id) {
            abort(403);
        }

        // Check if cancellation is allowed
        // 優先度: メニュー個別設定 > 店舗全体設定
        $deadlineMinutes = $shop->cancellation_deadline_minutes ?? 1440; // Default fallback

        if ($booking->menu && $booking->menu->requires_cancellation_deadline) {
            $deadlineMinutes = $booking->menu->cancellation_deadline_minutes ?? $deadlineMinutes;
        }

        $cancellationDeadline = Carbon::parse($booking->start_at)->subMinutes($deadlineMinutes);

        if (now()->greaterThan($cancellationDeadline)) {
            return redirect()->route('booker.bookings.show', ['shop' => $shop->slug, 'booking' => $booking->id])
                ->with('error', 'キャンセル期限を過ぎているため、キャンセルできません。店舗に直接お問い合わせください。');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('booker.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約をキャンセルしました。');
    }

    private function getAuthenticatedBooker(Shop $shop): ShopBooker
    {
        $booker = ShopBooker::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $booker;
    }
}
