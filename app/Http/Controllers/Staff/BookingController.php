<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $this->getAuthenticatedStaff($shop); // Validate staff access

        // フィルタ用のデータを取得
        $menus = $shop->menus()->select(['id', 'name'])->get();
        $staffs = $shop->staffs()->with('profile:shop_staff_id,nickname')->get()->map(function ($staff) {
            return ['id' => $staff->id, 'name' => $staff->profile->nickname ?? ''];
        });

        return view('staff.bookings.index', [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->getAuthenticatedStaff($shop);

        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);

        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get();
        $bookers = $shop->bookers()->with('crm')->get();
        
        $bookings = $shop->bookings()
            ->where('start_at', '>=', now())
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('staff.bookings.create', [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request, Shop $shop)
    {
        $this->getAuthenticatedStaff($shop);
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

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $startAt, $endAt) {
            if (empty($validated['shop_booker_id'])) {
                $booker = $shop->bookers()->create([
                    'name' => $validated['booker_name'],
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                
                $booker->crm()->create([
                    'shop_memo' => $validated['shop_memo'] ?? null,
                    'name_kana' => $validated['booker_name_kana'] ?? null,
                ]);
                
                $validated['shop_booker_id'] = $booker->id;
            } else {
                $booker = ShopBooker::find($validated['shop_booker_id']);
                
                $booker->update([
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                
                if ($booker->crm) {
                    $booker->crm->update([
                        'shop_memo' => $validated['shop_memo'] ?? null,
                    ]);
                } else {
                    $booker->crm()->create([
                        'shop_memo' => $validated['shop_memo'] ?? null,
                        'name_kana' => null,
                    ]);
                }
            }

            $booking = $shop->bookings()->create([
                'shop_booker_id' => $validated['shop_booker_id'],
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => 'confirmed',
                'booking_channel' => 'manual',
                'menu_name' => $menu->name,
                'menu_price' => $menu->price,
                'menu_duration' => $menu->duration,
                'assigned_staff_name' => $staff->profile->nickname ?? null,
                'booker_name' => $validated['booker_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'note_from_booker' => $validated['note_from_booker'],
                'shop_memo' => $validated['shop_memo'],
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

        return redirect()->route('staff.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, Booking $booking)
    {
        $this->getAuthenticatedStaff($shop);

        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);
        $booking->load(['bookingOptions', 'booker.crm']);

        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get();
        $bookers = $shop->bookers()->with('crm')->get();
        
        $otherBookings = $shop->bookings()
            ->where('id', '!=', $booking->id)
            ->where('start_at', '>=', now()->subMonths(1))
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('staff.bookings.edit', [
            'shop' => $shop,
            'booking' => $booking,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'bookings' => $otherBookings,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBookingRequest $request, Shop $shop, Booking $booking)
    {
        $this->getAuthenticatedStaff($shop);
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

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $booking, $startAt, $endAt) {
            if ($booking->booker) {
                $booking->booker->update([
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                if ($booking->booker->crm) {
                    $booking->booker->crm->update(['shop_memo' => $validated['shop_memo'] ?? null]);
                }
            }
            
            $booking->update([
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'menu_name' => $menu->name,
                'menu_price' => $menu->price,
                'menu_duration' => $menu->duration,
                'assigned_staff_name' => $staff->profile->nickname ?? null,
                'booker_name' => $validated['booker_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'note_from_booker' => $validated['note_from_booker'],
                'shop_memo' => $validated['shop_memo'],
            ]);

            $booking->bookingOptions()->delete();
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

        return redirect()->route('staff.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, Booking $booking)
    {
        $this->getAuthenticatedStaff($shop);

        $booking->delete();

        return redirect()->route('staff.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を削除しました。');
    }

    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        $staff = ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $staff;
    }
}
