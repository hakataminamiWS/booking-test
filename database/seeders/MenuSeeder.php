<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shop = \App\Models\Shop::where('name', 'Gemini Hair Salon')->first();

        if ($shop) {
            $shop->menus()->createMany([
                ['name' => 'カット', 'duration' => 60],
                ['name' => 'カラー', 'duration' => 90],
                ['name' => 'パーマ', 'duration' => 120],
                ['name' => 'トリートメント', 'duration' => 30],
            ]);
        }
    }
}
