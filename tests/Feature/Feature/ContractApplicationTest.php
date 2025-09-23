<?php

namespace Tests\Feature\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ContractApplicationTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * A basic feature test example.
     */
    public function test_owner_can_apply_for_contract(): void
    {
        // 1. Arrange
        // オーナー候補となるユーザーを作成
        $user = User::factory()->create();

        // 2. Act
        // そのユーザーとしてログインし、契約申し込みをPOST
        $response = $this->actingAs($user)->post(route('contract.application.store'), [
            'customer_name' => 'Test Customer Name',
        ]);

        // 3. Assert
        // 申し込み後にダッシュボードにリダイレクトされることを確認
        $response->assertRedirect(route('owner.dashboard'));

        // データベースに申し込みが保存されていることを確認
        $this->assertDatabaseHas('contract_applications', [
            'user_id' => $user->id,
            'customer_name' => 'Test Customer Name',
        ]);
    }
}