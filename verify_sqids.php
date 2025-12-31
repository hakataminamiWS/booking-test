<?php

use App\Models\ShopBooker;
use App\Models\Shop;
use App\Models\User;

// 1. Check existing seeded bookers
$seededBooker = ShopBooker::first();
echo "Seeded Booker Number: " . $seededBooker->number . " (Type: " . gettype($seededBooker->number) . ")\n";

// 2. Create a new booker to test Sqids generation
$shop = Shop::first();
$newBooker = new ShopBooker();
$newBooker->shop_id = $shop->id;
$newBooker->user_id = User::factory()->create()->id; // Create a dummy user
$newBooker->name = 'Verification User';
$newBooker->contact_email = 'verify@example.com';
$newBooker->contact_phone = '0000000000';
$newBooker->save();

echo "New Booker Number: " . $newBooker->number . " (Type: " . gettype($newBooker->number) . ")\n";

// Check if it looks like a Sqid (alphanumeric)
if (!preg_match('/^[0-9]+$/', $newBooker->number)) {
    echo "VERIFICATION PASSED: Number contains non-numeric characters or is treated as string, likely a Sqid.\n";
} else {
     // Sqids can be numeric sometimes? No, default alphabet is alphanumeric.
     // But if it's very short it might happen?
     // Actually default Sqids alphabet is shuffled, so it should include letters.
    echo "VERIFICATION REQUIRED: Number looks numeric. Sqids usually have letters. Check if Sqids is working or if it accidentally generated numbers only.\n";
}

// 3. Test filtering/sorting via Model (simulating Controller logic)
// Sorting
$sorted = ShopBooker::where('shop_id', $shop->id)->orderBy('number')->get();
echo "Sorted count: " . $sorted->count() . "\n";

