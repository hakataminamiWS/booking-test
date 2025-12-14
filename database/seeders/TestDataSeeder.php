<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Contract;
use App\Models\ContractApplication;
use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use App\Models\ShopStaff;
use App\Models\ShopBooker;
use App\Models\ShopBookerCrm;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. 予約システム管理者 (ID: 1)
        $adminUser = User::factory()->create(['id' => 1]);
        Admin::create(['user_id' => $adminUser->id, 'name' => '管理者']);
        $this->command->info("Admin User Created: ID={$adminUser->id}");

        // 2. オーナー (ID: 2) と店舗 (ID: 1)
        $ownerUser = User::factory()->create(['id' => 2]);
        $contractApplication = ContractApplication::create([
            'id' => 1,
            'user_id' => $ownerUser->id,
            'customer_name' => 'テストオーナー(id=2)',
            'email' => 'test-2@example.com',
            'status' => 'approved',
        ]);
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
        $shop = Shop::create([
            'id' => 1,
            'owner_user_id' => $ownerUser->id,
            'slug' => 'test-shop',
            'name' => 'テストショップ',
            'email' => 'test-shop@example.com',
        ]);
        $this->command->info("Owner User (ID:{$ownerUser->id}) and Shop (ID:{$shop->id}) Created.");

        // 2-2. 営業時間設定 (月〜金 9:00-18:00)
        // 0=Sun, 1=Mon, ..., 6=Sat
        $days = [1, 2, 3, 4, 5];
        foreach ($days as $day) {
            $shop->businessHoursRegular()->create([
                'day_of_week' => $day,
                'is_open' => true,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
            ]);
        }
        // 土日は休み
        $closedDays = [0, 6];
        foreach ($closedDays as $day) {
            $shop->businessHoursRegular()->create([
                'day_of_week' => $day,
                'is_open' => false,
                'start_time' => null,
                'end_time' => null,
            ]);
        }
        $this->command->info("  > Regular Business Hours (Mon-Fri 09:00-18:00) Set.");


        // 3. 店舗に紐づくデータ (スタッフ, メニュー, オプション)
        if ($shop) {
            // スタッフ1 (ID: 3)
            $staffUser1 = User::factory()->create(['id' => 3]);
            $staff1 = $shop->staffs()->create(['user_id' => $staffUser1->id]);
            $staff1->profile()->create(['nickname' => 'テスト スタッフ1']);
            $this->command->info("  > Staff '{$staff1->profile->nickname}' (User ID: 3) Created.");

            // スタッフ2 (ID: 4)
            $staffUser2 = User::factory()->create(['id' => 4]);
            $staff2 = $shop->staffs()->create(['user_id' => $staffUser2->id]);
            $staff2->profile()->create(['nickname' => 'テスト スタッフ2']);
            $this->command->info("  > Staff '{$staff2->profile->nickname}' (User ID: 4) Created.");

            // メニュー
            $menu1 = $shop->menus()->create([
                'name' => 'カット',
                'price' => 4500,
                'duration' => 60
            ]);
            $this->command->info("  > Menu '{$menu1->name}' Created.");
            $menu2 = $shop->menus()->create([
                'name' => 'カット & カラー',
                'price' => 12000,
                'duration' => 120,
                'requires_staff_assignment' => true, // スタッフ指名必須
            ]);
            $this->command->info("  > Menu '{$menu2->name}' Created.");

            // メニューにスタッフを紐付け
            $menu2->staffs()->attach([$staff2->id]);
            $this->command->info("  > Attached staff2 to menu '{$menu2->name}'.");

            // オプション
            $option1 = $shop->options()->create(['name' => 'トリートメント', 'price' => 2000, 'additional_duration' => 15]);
            $this->command->info("  > Option '{$option1->name}' Created.");
            $option2 = $shop->options()->create(['name' => 'ヘッドスパ', 'price' => 3000, 'additional_duration' => 20]);
            $this->command->info("  > Option '{$option2->name}' Created.");

            // メニューにオプションを紐付け
            $menu2->options()->attach([$option1->id, $option2->id]);
            $this->command->info("  > Attached 2 options to menu '{$menu2->name}'.");
        }

        // 4. 予約者 (ID: 5、予約者ID: 6)
        $bookerUser1 = User::factory()->create(['id' => 5]);
        $booker = $shop->bookers()->create([
            'user_id' => $bookerUser1->id,
            'number' => 1,
            'name' => 'テスト1予約者',
            'contact_email' => 'booker-5@example.com',
            'contact_phone' => '09012345678',
        ]);
        $booker->crm()->create([
            'name_kana' => 'テストいちよやくしゃ',
        ]);
        $this->command->info("Booker User Created: User ID={$bookerUser1->id}, Booker ID={$booker->id}");

        $bookerUser2 = User::factory()->create(['id' => 6]);
        $booker = $shop->bookers()->create([
            'user_id' => $bookerUser2->id,
            'number' => 2,
            'name' => 'テスト2予約者',
            'contact_email' => 'booker-6@example.com',
            'contact_phone' => '08098765432',
        ]);
        $booker->crm()->create([
            'name_kana' => 'テストによやくしゃ',
        ]);
        $this->command->info("Booker User Created: User ID={$bookerUser2->id}, Booker ID={$booker->id}");


        // 5. その他のダミーユーザー (ID: 7以降)
        User::factory()->count(5)->create();
        $this->command->info('Created 5 additional dummy users for testing.');
    }
}
