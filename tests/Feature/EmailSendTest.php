<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopStaff;
use App\Models\ShopMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailSendTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト用データを作成するヘルパー
     */
    private function createBookingTestData(): array
    {
        $toEmail = 'hakataminami.web.service@gmail.com';
        $replyToEmail = 'yoyakuowner1.netget@gmail.com';

        // Shop作成 (Ownerは適当なIDで誤魔化すか、最低限作る)
        // ここでは外部キー制約回避のため最低限のUserだけ作る
        $owner = User::factory()->create([
            // Userモデルにemailカラムがない場合でも、Mailableの宛先としてインスタンスを使うため、
            // 動的にプロパティを持たせるか、あるいはNotification経由でないなら単にアドレス文字列で送る
        ]);
        // オーナーへのメール送信のために、便宜上プロパティとして持たせておく（保存はされない）
        $owner->email = 'owner_test@example.com';

        $shop = Shop::create([
            'owner_user_id' => $owner->id,
            'name' => 'テスト美容室',
            'slug' => 'test-shop-' . time(),
            'email' => $replyToEmail,
            'time_slot_interval' => 30,
            'cancellation_deadline_minutes' => 1440,
            'booking_deadline_minutes' => 60,
            'booking_confirmation_type' => 'auto',
            'accepts_online_bookings' => true,
            'timezone' => 'Asia/Tokyo',
        ]);
        
        $booker = ShopBooker::create([
            'shop_id' => $shop->id,
            'name' => 'テスト予約者',
            'contact_email' => $toEmail,
            'contact_phone' => '090-0000-0000',
        ]);
        
        $booking = Booking::create([
            'shop_id' => $shop->id,
            'shop_booker_id' => $booker->id,
            'status' => 'confirmed',
            'menu_id' => null, 
            'menu_name' => 'カット',
            'menu_price' => 3000,
            'menu_duration' => 60,
            'assigned_staff_id' => null,
            'assigned_staff_name' => 'テストスタッフ',
            'timezone' => 'Asia/Tokyo',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
        ]);

        return [$shop, $booker, $booking, $owner];
    }

    public function test_send_provisional_mail(): void
    {
        config(['mail.default' => 'smtp']);
        [$shop, $booker, $booking, $owner] = $this->createBookingTestData();

        echo "\n=== [Provisional] Email Test Start ===\n";

        // 1. 予約者へ
        \Illuminate\Support\Facades\Mail::to($booker->contact_email)
            ->send(new \App\Mail\Shop\BookingProvisionalMail($booking, forOwner: false));
        echo "✅ Booker Mail Sent\n";

        // 2. オーナーへ
        \Illuminate\Support\Facades\Mail::to($owner->email)
            ->send(new \App\Mail\Shop\BookingProvisionalMail($booking, forOwner: true));
        echo "✅ Owner Mail Sent\n";

        $this->assertTrue(true);
    }

    public function test_send_confirmed_mail(): void
    {
        config(['mail.default' => 'smtp']);
        [$shop, $booker, $booking, $owner] = $this->createBookingTestData();

        echo "\n=== [Confirmed] Email Test Start ===\n";

        // 1. 予約者へ
        \Illuminate\Support\Facades\Mail::to($booker->contact_email)
            ->send(new \App\Mail\Shop\BookingConfirmedMail($booking, forOwner: false));
        echo "✅ Booker Mail Sent\n";

        // 2. オーナーへ
        \Illuminate\Support\Facades\Mail::to($owner->email)
            ->send(new \App\Mail\Shop\BookingConfirmedMail($booking, forOwner: true));
        echo "✅ Owner Mail Sent\n";

        $this->assertTrue(true);
    }

    public function test_send_canceled_mail(): void
    {
        config(['mail.default' => 'smtp']);
        [$shop, $booker, $booking, $owner] = $this->createBookingTestData();

        echo "\n=== [Canceled] Email Test Start ===\n";

        // 1. 予約者へ
        \Illuminate\Support\Facades\Mail::to($booker->contact_email)
            ->send(new \App\Mail\Shop\BookingCanceledMail($booking, forOwner: false));
        echo "✅ Booker Mail Sent\n";

        // 2. オーナーへ
        \Illuminate\Support\Facades\Mail::to($owner->email)
            ->send(new \App\Mail\Shop\BookingCanceledMail($booking, forOwner: true));
        echo "✅ Owner Mail Sent\n";

        $this->assertTrue(true);
    }

    public function test_send_provisional_expired_mail(): void
    {
        config(['mail.default' => 'smtp']);
        [$shop, $booker, $booking, $owner] = $this->createBookingTestData();

        echo "\n=== [Provisional Expired] Email Test Start ===\n";

        // 1. 予約者へ
        \Illuminate\Support\Facades\Mail::to($booker->contact_email)
            ->send(new \App\Mail\Shop\BookingProvisionalExpiredMail($booking, forOwner: false));
        echo "✅ Booker Mail Sent\n";

        // 2. オーナーへ
        \Illuminate\Support\Facades\Mail::to($owner->email)
            ->send(new \App\Mail\Shop\BookingProvisionalExpiredMail($booking, forOwner: true));
        echo "✅ Owner Mail Sent\n";

        $this->assertTrue(true);
    }
}
