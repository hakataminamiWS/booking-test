<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_onboarding_workflow(): void
    {
        // データベースのシーディングを実行
        $this->seed();

        // TestUserSeederで作成された最後のユーザー（Booker）をオーナー候補として取得
        $ownerCandidate = User::orderBy('id', 'desc')->first();
        $this->assertNotNull($ownerCandidate);

        // --- ステップ1: 権限付与前の状態確認 ---
        $this->assertFalse(
            Gate::forUser($ownerCandidate)->allows('create', Shop::class),
            '契約前は店舗を作成できないこと'
        );

        // --- ステップ2: 管理者による契約作成 ---
        Contract::create([
            'user_id' => $ownerCandidate->id,
            'max_shops' => 1,
            'status' => 'active',
            'start_date' => today(),
            'end_date' => today()->addYear(),
        ]);

        // --- ステップ3: 権限付与後の状態確認 ---
        $ownerCandidate->refresh(); // ★ データベースから最新の状態を再読み込み
        $this->assertTrue(
            Gate::forUser($ownerCandidate)->allows('create', Shop::class),
            '契約後は店舗を作成できること'
        );
    }
}
