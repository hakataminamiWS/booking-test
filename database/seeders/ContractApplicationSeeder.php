<?php

namespace Database\Seeders;

use App\Models\ContractApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 'dummy-1' から 'dummy-60' までのpublic_idを持つユーザーを取得
        $users = User::where('public_id', 'like', 'dummy-%')->get();

        // 取得したユーザーの中からランダムに30人を選び、契約申し込みを作成
        $selectedUsers = $users->random(30);

        foreach ($selectedUsers as $user) {
            ContractApplication::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        $this->command->info("Created 30 contract applications for dummy users.");
    }
}