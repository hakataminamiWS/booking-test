<?php

namespace Tests\Feature\Guest;

use App\Models\Booking;
use App\Models\ProvisionalBooking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use App\Models\User;
use App\Notifications\Shop\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createProvisionalBooking(): array
    {
        $shop = Shop::create([
            'owner_user_id' => User::factory()->create()->id,
            'name' => 'テスト美容室',
            'slug' => 'test-shop',
            'email' => 'shop@example.com',
            'timezone' => 'Asia/Tokyo',
        ]);

        $menu = $shop->menus()->create([
            'name' => 'カット',
            'price' => 3000,
            'duration' => 60,
        ]);

        $booker = $shop->bookers()->create([
            'name' => 'テスト予約者',
            'contact_email' => 'booker@example.com',
            'contact_phone' => '090-0000-0000',
        ]);

        // JST日時作成 -> UTC保存
        $startAt = Carbon::create(2026, 1, 25, 10, 0, 0, 'Asia/Tokyo')->setTimezone('UTC');
        $endAt = Carbon::create(2026, 1, 25, 11, 0, 0, 'Asia/Tokyo')->setTimezone('UTC');

        $booking = $shop->bookings()->create([
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'pending',
            'booking_channel' => 'web',
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'timezone' => 'Asia/Tokyo',
        ]);

        // 仮予約期限 (未来) -> UTC保存
        $expiresAt = Carbon::create(2026, 1, 25, 10, 0, 0, 'Asia/Tokyo')->subHours(6)->setTimezone('UTC');

        $provisional = ProvisionalBooking::create([
            'booking_id' => $booking->id,
            'shop_id' => $shop->id,
            'expires_at' => $expiresAt,
        ]);

        return [$shop, $booking, $provisional];
    }

    public function test_verify_provisional_booking_success(): void
    {
        Notification::fake();

        [$shop, $booking, $provisional] = $this->createProvisionalBooking();

        // 署名付きURL生成（期限内）
        // URL expiration should match provisional expiry
        $url = URL::temporarySignedRoute(
            'guest.bookings.verify',
            $provisional->expires_at,
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        // アクセス
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('guest.bookings.verified');

        // DB確認
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseMissing('provisional_bookings', [
            'booking_id' => $booking->id,
        ]);

        // Verify notifications were sent
        Notification::assertSentTo($booking->booker, BookingConfirmedNotification::class);
        Notification::assertSentTo($shop->owner, BookingConfirmedNotification::class);
    }

    public function test_verify_fails_with_invalid_signature(): void
    {
        [$shop, $booking, $provisional] = $this->createProvisionalBooking();

        // 正しいURLだが署名を改ざん
        $url = URL::temporarySignedRoute(
            'guest.bookings.verify',
            $provisional->expires_at,
            ['shop' => $shop->slug, 'booking' => $booking->id]
        ) . 'invalid';

        $response = $this->get($url);

        $response->assertStatus(403);
    }

    public function test_verify_fails_if_expired_in_db(): void
    {
        [$shop, $booking, $provisional] = $this->createProvisionalBooking();

        // DB上の期限を過去にする
        $provisional->update(['expires_at' => now()->subMinute()]);

        // URL自体は有効な署名で作る（システム的にはクリックはできたとする）
        // ただし temporarySignedRoute は有効期限を埋め込むので、
        // コントローラー内のDBチェックを通るケースをテストするには、URL期限は未来だがDB期限は過去という状況を作る必要がある。
        // -> provisional->update でDBだけ過去にする。
        
        $url = URL::temporarySignedRoute(
            'guest.bookings.verify',
            now()->addHour(), // URLはまだ有効
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        $response = $this->get($url);

        // コントローラー到達前にミドルウェア(ExpirePendingBookings)が期限切れを検知して削除するため、
        // コントローラー内では「予約が見つからない(Provisionalがない)」として404になるのが正しい挙動
        $response->assertStatus(404);
        // $response->assertSee('予約の有効期限が切れています。'); // 404の場合はメッセージが異なる(abort 404 default page or defined message)
    }

    public function test_verify_redirects_if_already_confirmed(): void
    {
        [$shop, $booking, $provisional] = $this->createProvisionalBooking();

        // 既に確定済みにする
        $booking->update(['status' => 'confirmed']);

        $url = URL::temporarySignedRoute(
            'guest.bookings.verify',
            $provisional->expires_at,
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('guest.bookings.verified');
    }
}
