<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Menu;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database with necessary data for tests
        $this->artisan('db:seed');
    }

    /** @test */
    public function a_booking_can_be_created_successfully()
    {
        $shop = Shop::first();
        $menu = Menu::first();
        $staff = User::whereHas('shops', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)
                  ->where('shop_user.role', 'staff');
        })->first();



        $bookingData = [
            'menu_id' => $menu->id,
            'staff_id' => $staff->id,
            'start_at' => Carbon::now()->addDays(1)->setTime(10, 0)->toDateTimeString(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'tel' => $this->faker->phoneNumber,
            'gender_preference' => 'male',
        ];

        $response = $this->postJson(route('booker.bookings.store', ['shop' => $shop->id]), $bookingData);

        $response->assertStatus(302); // Redirect to complete page
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'shop_id' => $shop->id,
            'menu_id' => $menu->id,
            'staff_id' => $staff->id,
            'start_at' => $bookingData['start_at'],
            'name' => $bookingData['name'],
            'email' => $bookingData['email'],
            'tel' => $bookingData['tel'],
            'gender_preference' => $bookingData['gender_preference'],
        ]);
    }

    /** @test */
    public function booking_requires_valid_data()
    {
        $shop = Shop::first();

        $response = $this->postJson(route('booker.bookings.store', ['shop' => $shop->id]), []);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['menu_id', 'start_at', 'name', 'email', 'tel']);
    }

    /** @test */
    public function booking_cannot_be_created_if_slot_is_taken()
    {
        $shop = Shop::first();
        $menu = Menu::first();
        $staff = User::whereHas('shops', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->where('shop_user.role', 'staff');
        })->first();

        $takenTime = Carbon::now()->addDays(2)->setTime(11, 0);

        // Create a booking that takes the slot
        Booking::create([
            'shop_id' => $shop->id,
            'menu_id' => $menu->id,
            'staff_id' => $staff->id,
            'start_at' => $takenTime->toDateTimeString(),
            'name' => 'Existing Booker',
            'email' => 'existing@example.com',
            'tel' => '1234567890',
            'gender_preference' => 'male',
        ]);

        // Attempt to create another booking for the same slot
        $bookingData = [
            'menu_id' => $menu->id,
            'staff_id' => $staff->id,
            'start_at' => $takenTime->toDateTimeString(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'tel' => $this->faker->phoneNumber,
            'gender_preference' => 'female',
        ];

        $response = $this->postJson(route('booker.bookings.store', ['shop' => $shop->id]), $bookingData);

        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors('availability');
        $this->assertDatabaseCount('bookings', 1); // Only the first booking should exist
    }
}
