<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $this->authorize('viewAny', [Booking::class, $shop]);

        // フィルタ用のデータを取得
        $menus = $shop->menus()->select(['id', 'name'])->get();
        $staffs = $shop->staffs()->with('profile:shop_staff_id,nickname')->get()->map(function ($staff) {
            return ['id' => $staff->id, 'name' => $staff->profile->nickname ?? ''];
        });

        return view('owner.bookings.index', [
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
        $this->authorize('create', [Booking::class, $shop]);

        // 必要なリレーションをまとめてロード
        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);

        // フォームの選択肢として使用するデータを取得
        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get();
        $bookers = $shop->bookers()->get();
        
        // 未来の予約のみを取得して渡す
        $bookings = $shop->bookings()->where('start_at', '>=', now())->get();

        return view('owner.bookings.create', [
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
        $validated = $request->validated();

        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = $menu->options()->whereIn('id', $validated['option_ids'] ?? [])->get();
        $staff = isset($validated['assigned_staff_id']) ? ShopStaff::with('profile')->find($validated['assigned_staff_id']) : null;

        // 合計時間を計算
        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->additional_duration;
        }

        $endAt = new \DateTime($validated['start_at']);
        $endAt->add(new \DateInterval("PT{$totalDuration}M"));

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $endAt) {
            // ----------------------------------------------------------------
            // 予約者 (ShopBooker) の準備
            // ----------------------------------------------------------------
            if (empty($validated['shop_booker_id'])) {
                // 新規予約者の場合は登録
                $booker = $shop->bookers()->create([
                    'nickname' => $validated['booker_name'],
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                    'shop_memo' => $validated['shop_memo'],
                ]);
                $validated['shop_booker_id'] = $booker->id;
            } else {
                $booker = ShopBooker::find($validated['shop_booker_id']);
                // 既存予約者の情報を更新することも可能 (今回は実施しない)
            }

            // ----------------------------------------------------------------
            // 予約 (Booking) の作成
            // ----------------------------------------------------------------
            $booking = $shop->bookings()->create([
                'shop_booker_id' => $validated['shop_booker_id'],
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $validated['start_at'],
                'end_at' => $endAt,
                'status' => 'confirmed', // 手動登録は即時確定
                'booking_channel' => 'manual',
                // --- スナップショット情報 ---
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

            // ----------------------------------------------------------------
            // 予約オプション (BookingOption) の保存
            // ----------------------------------------------------------------
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

        return redirect()->route('owner.shops.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を登録しました。');
    }


    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
