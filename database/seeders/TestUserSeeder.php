<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Contract; // Contract モデルを追加
use App\Models\ContractApplication;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 予約システム管理者ユーザーの作成
        // id: 1
        $adminUser = User::factory()->create(['id' => 1]);
        Admin::create(['user_id' => $adminUser->id, 'name' => '管理者']);
        $this->command->info("Admin User Created: ID={$adminUser->id}");

        // オーナーの作成
        // id: 2
        $ownerUser = User::factory()->create(['id' => 2]);
        // 申し込み情報の作成
        $contractApplication = ContractApplication::create([
            'id' => 1,
            'user_id' => $ownerUser->id,
            'customer_name' => 'テストオーナー(id=2)',
            'email' => 'test-2@example.com',
            'status' => 'approved',
        ]);
        // 契約の作成
        Contract::create([
            'id' => 1,
            'user_id' => $ownerUser->id,
            'name' => 'テストオーナー(id=2)契約',
            'application_id' => $contractApplication->id,
            'max_shops' => 1,
            'status' => 'active',
            'start_date' => now()->subYear(),
            'end_date' => now()->addYear(),
        ]);
        // 店舗の作成
        Shop::create([
            'id' => 1,
            'owner_user_id' => $ownerUser->id,
            'slug' => 'test-shop',
            'name' => 'テストショップ',
            'email' => 'test-shop@example.com',
        ]);
        // 営業時間の作成
        // メニューの作成
        // オプションの作成
        $this->command->info("Owner User Created: ID={$ownerUser->id}");

        // 店舗スタッフの作成
        // id: 3
        $staffUser = User::factory()->create(['id' => 3]);
        // 店舗スタッフ申し込み情報の作成
        // 店舗スタッフとして登録
        // 店舗スタッフプロファイルの作成
        // 店舗スタッフのシフト作成
        $this->command->info("Staff User Created: ID={$staffUser->id}");

        // 予約者ユーザーの作成
        // id: 4
        $bookerUser = User::factory()->create(['id' => 4]);
        // 予約者プロファイルの作成
        // 予約者の予約履歴の作成
        $this->command->info("Booker User Created: ID={$bookerUser->id}");

        // --- 動作確認用のダミーユーザーを5人作成 ---
        // id: 5 から 9 まで
        User::factory()->count(5)->create();
        $this->command->info('Created 5 dummy users for testing.');
    }
}
