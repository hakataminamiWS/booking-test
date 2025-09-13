<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ロールを準備 (存在しない場合のみ作成)
        Role::firstOrCreate(['name' => 'owner'], ['description' => '店舗の所有者']);
        Role::firstOrCreate(['name' => 'staff'], ['description' => '店舗のスタッフ']);
    }
}
