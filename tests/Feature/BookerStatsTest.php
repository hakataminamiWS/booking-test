<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopMenu;
use App\Models\ShopStaff;
use App\Models\User;
use App\Models\ShopBookerCrm;
use App\Services\ShopBookerCrmService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookerStatsTest extends TestCase
{
    use RefreshDatabase;

    private function createShop() {
        $owner = User::factory()->create();
        return Shop::create([
            'owner_user_id' => $owner->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop-' . uniqid(),
            'timezone' => 'Asia/Tokyo',
        ]);
    }

    private function createStaff($shop) {
        $user = User::factory()->create();
        return ShopStaff::create([
            'shop_id' => $shop->id,
            'user_id' => $user->id,
            'role' => 'staff',
            'status' => 'active',
        ]);
    }

    private function createMenu($shop) {
        return ShopMenu::create([
            'shop_id' => $shop->id,
            'name' => 'Test Menu',
            'price' => 1000,
            'duration' => 60,
        ]);
    }

    private function createBooker($shop) {
        return ShopBooker::create([
            'shop_id' => $shop->id,
            'name' => 'Test Booker',
            'contact_email' => 'test@example.com',
            'contact_phone' => '09000000000',
        ]);
    }

    public function test_update_stats_calculates_correctly()
    {
        $shop = $this->createShop();
        $booker = $this->createBooker($shop);
        $staff = $this->createStaff($shop);
        $menu = $this->createMenu($shop);

        // Create 2 bookings in the past
        Booking::create([
            'shop_id' => $shop->id,
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'assigned_staff_id' => $staff->id,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'status' => 'confirmed',
            'start_at' => now()->subDays(10),
            'end_at' => now()->subDays(10)->addMinutes(60),
            'timezone' => 'Asia/Tokyo',
            'booking_channel' => 'web',
        ]);
        Booking::create([
            'shop_id' => $shop->id,
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'assigned_staff_id' => $staff->id,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'status' => 'confirmed',
            'start_at' => now()->subDays(5),
            'end_at' => now()->subDays(5)->addMinutes(60),
            'timezone' => 'Asia/Tokyo',
            'booking_channel' => 'web',
        ]);

        // Create 1 cancelled booking
        Booking::create([
            'shop_id' => $shop->id,
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'assigned_staff_id' => $staff->id,
            'booker_name' => $booker->name,
            'contact_email' => $booker->contact_email,
            'contact_phone' => $booker->contact_phone,
            'menu_name' => $menu->name,
            'menu_price' => $menu->price,
            'menu_duration' => $menu->duration,
            'status' => 'cancelled',
            'start_at' => now(), // Latest but cancelled
            'end_at' => now()->addMinutes(60),
            'timezone' => 'Asia/Tokyo',
            'booking_channel' => 'web',
        ]);

        $service = new ShopBookerCrmService();
        $service->updateStats($booker);

        $booker->refresh();
        $crm = $booker->crm;

        $this->assertNotNull($crm);
        $this->assertEquals(2, $crm->booking_count); // 2 confirmed
        // Last booking should be the one 5 days ago, not the cancelled one today
        $this->assertEquals(now()->subDays(5)->format('Y-m-d H:i:00'), Carbon::parse($crm->last_booking_at)->format('Y-m-d H:i:00'));
    }

    public function test_controller_integration_updates_stats()
    {
        $shop = $this->createShop();
        $owner = User::factory()->create();
        $shop->owners()->attach($owner, ['role' => 'owner']);
        
        $staff = $this->createStaff($shop);
        $menu = $this->createMenu($shop);
        
        // Existing booker
        $booker = $this->createBooker($shop);
        
        $this->actingAs($owner);

        $response = $this->post(route('owner.shops.bookings.store', $shop), [
            'shop_booker_id' => $booker->id,
            'menu_id' => $menu->id,
            'option_ids' => [],
            'start_at' => now()->addDay()->format('Y-m-d H:i'),
            'assigned_staff_id' => $staff->id,
            'booker_name' => $booker->name,
            'contact_email' => 'test@example.com',
            'contact_phone' => '09012345678',
            'booker_name_kana' => 'テストブッカー',
            'note_from_booker' => 'Note',
            'shop_memo' => 'Memo',
        ]);

        $response->assertRedirect();
        
        $booker->refresh();
        $this->assertNotNull($booker->crm);
        $this->assertEquals(1, $booker->crm->booking_count);
        $this->assertNotNull($booker->crm->last_booking_at);
    }
}

