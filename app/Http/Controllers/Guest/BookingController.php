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
            if ($staff->profile && $staff->profile->image_url) {
                $imageUrl = Storage::disk('public')->url($staff->profile->image_url);
            }
            // StaffモデルのtoArray()結果に、変換済みのURLを上書きする
            $staff->profile->image_url = $imageUrl;
            
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
                'status' => 'pending', // 変更: confirmed -> pending
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
            
            // 4. Create ProvisionalBooking
            \App\Models\ProvisionalBooking::create([
                'booking_id' => $booking->id,
                'shop_id' => $shop->id,
                'expires_at' => now()->addMinutes(10), // 10分後
            ]);

            // 統計情報の更新
            $crmService->updateStats($booker);

            return $booking;
        });

        // セッションに予約IDを保存 (セキュリティ対策)
        session(['guest_booking_id' => $booking->id]);

        // 仮予約通知送信
        $booking->booker->notify(new \App\Notifications\Shop\ProvisionalBookingNotification($booking));

        // 変更: complete -> provisional
        return redirect()->route('guest.bookings.provisional', ['shop' => $shop->slug, 'booking' => $booking->id]);
    }

    public function provisional(Shop $shop, $bookingId)
    {
        // Simple security check: Ensure booking belongs to this shop
        // 必要なのはIDとステータスチェックだけなので、リレーションロードは不要
        $booking = $shop->bookings()->findOrFail($bookingId);
            
        // セキュリティチェック: セッションに保存されたIDと一致するか
        if (session('guest_booking_id') != $bookingId) {
             abort(403);
        }

        // pending以外ならcompleteへリダイレクト（誤ってアクセスした場合など）
        if ($booking->status !== 'pending') {
             return redirect()->route('guest.bookings.complete', ['shop' => $shop->slug, 'booking' => $booking->id]);
        }

        // ViewにはIDのみを渡す (個人情報保護)
        return view('guest.bookings.provisional', [
            'shop' => $shop,
            'booking' => ['id' => $booking->id],
        ]);
    }

    public function complete(Shop $shop, $bookingId)
    {
        // ... (省略: 今回修正範囲外だが、念のため同様の思想が望ましい。しかしinstructionはcompleteには触れない方針)
        // 今回の指示は provisional と verify
        // completeメソッドは修正対象外とする
        
        $booking = $shop->bookings()
            ->with(['menu', 'staff.profile', 'bookingOptions'])
            ->findOrFail($bookingId);

        return view('guest.bookings.complete', [
            'shop' => $shop,
            'booking' => $booking,
        ]);
    }

    /**
     * 署名付きURLによる予約確定アクション
     */
    public function verify(Shop $shop, \App\Models\Booking $booking, DB $db = null) // DB facade injected for testing or use global
    {
        // 1. ステータスチェック (既に確定済みなら完了画面へ)
        if ($booking->status === 'confirmed') {
             $booking->load(['menu', 'staff.profile', 'bookingOptions']);
             // 必要な情報のみを許可 (ホワイトリスト)
             $booking->setVisible([
                'id', 'start_at', 'end_at', 
                'menu_name', 'menu_price', 'menu_duration', 
                'assigned_staff_name', 
                'note_from_booker',
                'bookingOptions', 
                'menu', 
                'staff'
             ]);
             return view('guest.bookings.verified', ['shop' => $shop, 'booking' => $booking]);
        }

        // 2. 仮予約存在チェック
        // 仮予約レコードがない、またはstatusがpendingでない場合はエラー
        if ($booking->status !== 'pending' || !$booking->provisionalBooking) {
            abort(404, '予約が見つからないか、既に無効になっています。');
        }

        // 3. 有効期限チェック (Double Check)
        // URL署名の期限とは別に、DB上の有効期限も確認する
        if ($booking->provisionalBooking->expires_at->isPast()) {
            abort(403, '予約の有効期限が切れています。');
        }

        // 4. 確定処理
        DB::transaction(function () use ($booking) {
            // ステータス更新
            $booking->update(['status' => 'confirmed']);
            
            // 仮予約レコード削除
            $booking->provisionalBooking()->delete();
        });

        // 5. 確定メール送信 (Booker)
        $booking->booker->notify(new \App\Notifications\Shop\BookingConfirmedNotification($booking));

        // 6. 確定メール送信 (Shop)
        if ($shop->email) {
            $booking->shop->notify(new \App\Notifications\Shop\BookingConfirmedNotification($booking));
        }

        // 7. 完了画面へ
        $booking->load(['menu', 'staff.profile', 'bookingOptions']);
        // 必要な情報のみを許可 (ホワイトリスト)
        $booking->setVisible([
            'id', 'start_at', 'end_at', 
            'menu_name', 'menu_price', 'menu_duration', 
            'assigned_staff_name', 
            'note_from_booker',
            'bookingOptions', 
            'menu', 
            'staff'
        ]);

        return view('guest.bookings.verified', [
            'shop' => $shop,
            'booking' => $booking,
        ]);
    }
}
