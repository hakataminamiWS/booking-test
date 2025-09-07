<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shop = \App\Models\Shop::where('name', 'Gemini Hair Salon')->first();

        if ($shop) {
            $owner = \App\Models\User::factory()->create([
                'name' => 'Owner',
                'email' => 'owner@gemini.com',
            ]);

            $staff = \App\Models\User::factory()->create([
                'name' => 'Staff',
                'email' => 'staff@gemini.com',
            ]);

            $shop->users()->attach($owner->id, ['role' => 'owner']);
            $shop->users()->attach($staff->id, ['role' => 'staff']);
        }
    }
}
