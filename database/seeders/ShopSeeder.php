<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ダミーのオーナーを作成
        $owner = User::factory()->create();

        \App\Models\Shop::create([
            'owner_user_id' => $owner->id,
            'name' => 'Gemini Hair Salon',
            'slug' => 'gemini-hair-salon',
        ]);
    }
}