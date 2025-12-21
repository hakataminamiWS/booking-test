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
use Carbon\Carbon;

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

        return view('owner.shops.bookings.index', [
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
        $bookers = $shop->bookers()->with('crm')->get();
        
        // 未来の予約のみを取得して渡す
        $bookings = $shop->bookings()
            ->where('start_at', '>=', now())
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('owner.shops.bookings.create', [
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
        $options = $menu->options()->whereIn('shop_options.id', $validated['option_ids'] ?? [])->get();
        $staff = isset($validated['assigned_staff_id']) ? ShopStaff::with('profile')->find($validated['assigned_staff_id']) : null;

        // 合計時間を計算
        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->additional_duration;
        }

        // Parse start_at using shop's timezone and convert to UTC for storage
        $startAt = Carbon::parse($validated['start_at'], $shop->timezone)->setTimezone(config('app.timezone'));
        
        // Calculate end_at based on duration
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $startAt, $endAt) {
            // ----------------------------------------------------------------
            // 予約者 (ShopBooker) の準備
            // ----------------------------------------------------------------
                if (empty($validated['shop_booker_id'])) {
                // 新規予約者の場合は登録
                $booker = $shop->bookers()->create([
                    'name' => $validated['booker_name'],
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                
                // CRM情報を作成
                $booker->crm()->create([
                    'shop_memo' => $validated['shop_memo'] ?? null,
                    'name_kana' => $validated['booker_name_kana'] ?? null,
                ]);
                
                $validated['shop_booker_id'] = $booker->id;
            } else {
                // 既存予約者の場合
                $booker = ShopBooker::find($validated['shop_booker_id']);
                
                // 連絡先情報を更新（編集可能なフィールド）
                // 注意: name, name_kana は意図的に更新しない（個人識別情報の保護）
                // フロントエンドから送られてきても、ここで明示的に指定したフィールドのみ更新される
                $booker->update([
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                
                // CRM情報を更新（編集可能なフィールド）
                if ($booker->crm) {
                    $booker->crm->update([
                        'shop_memo' => $validated['shop_memo'] ?? null,
                    ]);
                } else {
                    // CRMレコードが存在しない場合は作成
                    $booker->crm()->create([
                        'shop_memo' => $validated['shop_memo'] ?? null,
                        'name_kana' => null,
                    ]);
                }
            }

            // ----------------------------------------------------------------
            // 予約 (Booking) の作成
            // ----------------------------------------------------------------
            $booking = $shop->bookings()->create([
                'shop_booker_id' => $validated['shop_booker_id'],
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
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
    public function edit(Shop $shop, Booking $booking)
    {
        $this->authorize('update', $booking);

        // 必要なリレーションをまとめてロード
        $shop->load(['businessHoursRegular', 'shopSpecialOpenDays', 'shopSpecialClosedDays']);
        $booking->load(['bookingOptions', 'booker.crm']);

        // フォームの選択肢として使用するデータを取得
        $menus = $shop->menus()->with(['options', 'staffs.profile'])->get();
        $staffs = $shop->staffs()->with(['profile', 'schedules'])->get();
        $bookers = $shop->bookers()->with('crm')->get();
        
        // 他の予約情報をカレンダー表示用に取得
        $otherBookings = $shop->bookings()
            ->where('id', '!=', $booking->id)
            ->where('start_at', '>=', now()->subMonths(1)) // 少し過去のデータも表示
            ->select(['id', 'start_at', 'end_at', 'assigned_staff_id'])
            ->get();

        return view('owner.shops.bookings.edit', [
            'shop' => $shop,
            'booking' => $booking,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'bookings' => $otherBookings, // カレンダー表示用
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBookingRequest $request, Shop $shop, Booking $booking)
    {
        $this->authorize('update', $booking);
        $validated = $request->validated();

        $menu = ShopMenu::findOrFail($validated['menu_id']);
        $options = $menu->options()->whereIn('shop_options.id', $validated['option_ids'] ?? [])->get();
        $staff = isset($validated['assigned_staff_id']) ? ShopStaff::with('profile')->find($validated['assigned_staff_id']) : null;

        // 合計時間を計算
        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->additional_duration;
        }

        // Parse start_at using shop's timezone
        $startAt = Carbon::parse($validated['start_at'], $shop->timezone)->setTimezone(config('app.timezone'));
        
        // Calculate end_at based on duration
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        DB::transaction(function () use ($validated, $shop, $menu, $options, $staff, $booking, $startAt, $endAt) {
            // ----------------------------------------------------------------
            // 予約者 (ShopBooker) の更新
            // ----------------------------------------------------------------
            // bookingに紐づくbookerを更新する
            if ($booking->booker) {
                // name, name_kana は意図的に更新しない（個人識別情報の保護のため）
                $booking->booker->update([
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);
                if ($booking->booker->crm) {
                    $booking->booker->crm->update(['shop_memo' => $validated['shop_memo'] ?? null]);
                }
            }
            
            // ----------------------------------------------------------------
            // 予約 (Booking) の更新
            // ----------------------------------------------------------------
            $booking->update([
                'assigned_staff_id' => $validated['assigned_staff_id'] ?? null,
                'menu_id' => $menu->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
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
            ]);

            // ----------------------------------------------------------------
            // 予約オプション (BookingOption) の再作成
            // ----------------------------------------------------------------
            $booking->bookingOptions()->delete(); // 既存のオプションを削除
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
            ->with('success', '予約を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, Booking $booking)
    {
        $this->authorize('delete', $booking);

        $booking->delete();

        return redirect()->route('owner.shops.bookings.index', ['shop' => $shop->slug])
            ->with('success', '予約を削除しました。');
    }
}
