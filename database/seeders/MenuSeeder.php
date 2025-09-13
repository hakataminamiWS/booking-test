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
        $shop = \App\Models\Shop::where('slug', 'gemini-hair-salon')->first();

        if ($shop) {
            $shop->menus()->createMany([
                ['name' => 'カット', 'price' => 5000, 'duration' => 60],
                ['name' => 'カラー', 'price' => 7000, 'duration' => 90],
                ['name' => 'パーマ', 'price' => 8000, 'duration' => 120],
                ['name' => 'トリートメント', 'price' => 4000, 'duration' => 30],
            ]);
        }
    }
}