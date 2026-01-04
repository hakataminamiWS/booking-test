<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreBookingRequest;
use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use App\Models\ShopBooker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function create(Shop $shop)
    {
        if (!$shop->accepts_online_bookings) {
            abort(403, '現在、オンライン予約の受付を停止しています。');
        }

        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);

        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get()->map(function ($staff) {
            $imageUrl = null;
            if ($staff->profile && $staff->profile->small_image_url) {
                $imageUrl = Storage::disk('public')->url($staff->profile->small_image_url);
            }
            // StaffモデルのtoArray()結果に、変換済みのURLを上書きする
            $staff->profile->small_image_url = $imageUrl;
            
            return $staff; 
        });
        
        // 空き状況確認用に既存予約を取得（プライバシーに配慮し必要なデータのみ）
        $bookings = $shop->bookings()
            ->where('start_at', '>=', now())
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('guest.bookings.create', [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookings' => $bookings,
        ]);
    }

    public function store(StoreBookingRequest $request, Shop $shop, \App\Services\ShopBookerCrmService $crmService)
    {
        if (!$shop->accepts_online_bookings) {
            abort(403, '現在、オンライン予約の受付を停止しています。');
        }

        $validated = $request->validated();

        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = $menu->options()->whereIn('shop_options.id', $validated['option_ids'] ?? [])->get();
        $staff = ShopStaff::with('profile')->findOrFail($validated['assigned_staff_id']);

        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->additional_duration;
        }

        $startAt = Carbon::parse($validated['start_at'], $shop->timezone)->setTimezone('UTC');
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        $booking = DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $startAt, $endAt, $crmService) {
            // 1. Create a new ShopBooker for the guest
            // Guests always create a new record in this design
            $booker = $shop->bookers()->create([
                'user_id' => null, // Guest
                'name' => $validated['booker_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
            ]);

            // CRM entry creation (minimal)
            $booker->crm()->create([
                // Guests don't provide shop_memo or name_kana initially in this form
            ]);

            // 2. Create Booking
            $booking = $shop->bookings()->create([
                'shop_booker_id' => $booker->id,
                'assigned_staff_id' => $staff->id,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => 'confirmed',
                'booking_channel' => 'web', // Consistent with logged-in users
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

            // 3. Create Booking Options
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

            // 統計情報の更新
            $crmService->updateStats($booker);

            return $booking;
        });

        return redirect()->route('guest.bookings.complete', ['shop' => $shop->slug, 'booking' => $booking->id]);
    }

    public function complete(Shop $shop, $bookingId)
    {
        // Simple security check: Ensure booking belongs to this shop
        // Ideally we might want a signed URL or session flash to prevent ID enumeration/viewing others,
        // but for now we'll just check shop association.
        // A robust solution would be to use a signed route for completion or check session.
        $booking = $shop->bookings()
            ->with(['menu', 'staff.profile', 'bookingOptions'])
            ->findOrFail($bookingId);

        return view('guest.bookings.complete', [
            'shop' => $shop,
            'booking' => $booking,
        ]);
    }
}
