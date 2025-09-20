<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserShopProfile;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- ユーザーの作成 ---
        $adminUser = User::factory()->create(['public_id' => 'public-id-admin']);
        Admin::create(['user_id' => $adminUser->id, 'name' => '管理者']);
        $this->command->info("Admin User Created: ID={$adminUser->id}, PublicID={$adminUser->public_id}");

        $ownerUser = User::factory()->create(['public_id' => 'public-id-owner']);
        $this->command->info("Owner User Created: ID={$ownerUser->id}, PublicID={$ownerUser->public_id}");

        $staffUser = User::factory()->create(['public_id' => 'public-id-staff']);
        $this->command->info("Staff User Created: ID={$staffUser->id}, PublicID={$staffUser->public_id}");

        $bookerUser = User::factory()->create(['public_id' => 'public-id-booker']);
        $this->command->info("Booker User Created: ID={$bookerUser->id}, PublicID={$bookerUser->public_id}");


        // --- 店舗とプロファイルの作成 ---
        $testShop = Shop::factory()->create([
            'owner_user_id' => $ownerUser->id, // 作成したオーナーを店舗に紐付け
            'name' => 'テスト店舗',
        ]);
        $this->command->info("Test Shop Created: ID={$testShop->id}, Name={$testShop->name}, OwnerID={$ownerUser->id}");

        // オーナーのプロフィールを作成
        UserShopProfile::factory()->create([
            'user_id' => $ownerUser->id,
            'shop_id' => $testShop->id,
            'nickname' => 'オーナー',
            'contact_email' => 'owner@example.com',
        ]);

        // スタッフのプロフィールを作成し、店舗にスタッフとして紐付け
        UserShopProfile::factory()->create([
            'user_id' => $staffUser->id,
            'shop_id' => $testShop->id,
            'nickname' => 'スタッフ',
            'contact_email' => 'staff@example.com',
        ]);
        $staffUser->staffShops()->attach($testShop->id);

        // --- 動作確認用のダミーユーザーを60人作成 ---
        User::factory()->count(60)->create();
        $this->command->info("Created 60 dummy users for testing.");
    }
}
