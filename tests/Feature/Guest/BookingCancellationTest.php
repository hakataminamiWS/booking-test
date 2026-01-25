<?php

namespace Tests\Feature\Guest;

use App\Models\Booking;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\Shop\BookingCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function createBooking(): array
    {
        $shop = Shop::create([
            'owner_user_id' => User::factory()->create()->id,
            'name' => 'テスト美容室',
            'slug' => 'test-shop-cancel',
            'email' => 'shop@example.com',
            'timezone' => 'Asia/Tokyo',
            'cancellation_deadline_minutes' => 60, // 1時間前まで
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
        // 明日の10:00 (現在はキャンセル可能)
        $startAt = Carbon::create(2026, 1, 26, 10, 0, 0, 'Asia/Tokyo')->setTimezone('UTC');
        $endAt = Carbon::create(2026, 1, 26, 11, 0, 0, 'Asia/Tokyo')->setTimezone('UTC');

        $booking = $shop->bookings()->create([
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'confirmed',
            'booking_channel' => 'web',
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'timezone' => 'Asia/Tokyo',
        ]);

        return [$shop, $booking];
    }

    public function test_show_cancellation_page_with_valid_signature(): void
    {
        [$shop, $booking] = $this->createBooking();

        // 署名付きURL生成（期限内: now + 1hour）
        // Controller側で期限チェックをするのでURL自体はアクセスできればOK
        $url = URL::temporarySignedRoute(
            'guest.bookings.cancel.show',
            now()->addHour(),
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('guest.bookings.cancel');
        $response->assertViewHas('cancelUrl'); // URLが入っているか
    }

    public function test_perform_cancellation_with_valid_signature(): void
    {
        Notification::fake();

        [$shop, $booking] = $this->createBooking();

        // POST用URL
        $url = URL::temporarySignedRoute(
            'guest.bookings.cancel.perform',
            now()->addHour(),
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        $response = $this->post($url);

        $response->assertStatus(200);
        $response->assertViewIs('guest.bookings.cancelled');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        // Verify notifications were sent
        Notification::assertSentTo($booking->booker, BookingCancelledNotification::class);
        Notification::assertSentTo($shop->owner, BookingCancelledNotification::class);
    }

    public function test_fails_with_invalid_signature(): void
    {
        [$shop, $booking] = $this->createBooking();

        $url = URL::temporarySignedRoute(
            'guest.bookings.cancel.show',
            now()->addHour(),
            ['shop' => $shop->slug, 'booking' => $booking->id]
        ) . 'invalid';

        $response = $this->get($url);
        $response->assertStatus(403);
    }

    public function test_perform_fails_if_deadline_passed(): void
    {
        // 過去の日時で予約を作成
        $shop = Shop::create([
            'owner_user_id' => User::factory()->create()->id,
            'name' => 'テスト美容室',
            'slug' => 'test-shop-expired',
            'email' => 'shop@example.com',
            'timezone' => 'Asia/Tokyo',
            'cancellation_deadline_minutes' => 60,
        ]);
        
        $menu = $shop->menus()->create(['name' => 'menu', 'price' => 1000, 'duration' => 60]);
        $booker = $shop->bookers()->create(['name' => 'test', 'contact_email' => 't@e', 'contact_phone' => '000']);

        // 予約日時: 1時間後 (期限はちょうど今 = 期限切れ直前だが、少し進めると切れる)
        // いや、start_at を now() にすると、期限(60分前)は now()-60分 なので完全に期限切れ
        $startAt = now()->setTimezone('UTC'); 
        
        $booking = $shop->bookings()->create([
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addHour(),
            'status' => 'confirmed',
            'booking_channel' => 'web',
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'timezone' => 'Asia/Tokyo',
        ]);

        // URL自体は有効
        $url = URL::temporarySignedRoute(
            'guest.bookings.cancel.perform',
            now()->addHour(),
            ['shop' => $shop->slug, 'booking' => $booking->id]
        );

        $response = $this->post($url);

        $response->assertStatus(403);
        $response->assertSee('期限を過ぎています');
    }
}
