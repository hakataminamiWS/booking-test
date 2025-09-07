<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;

class ShopPolicy
{
    /**
     * Determine whether the user can view any shops.
     */
    public function viewAny(User $user): bool
    {
        // オーナーは自身の関連する店舗のみ閲覧可能
        return $user->shops()->exists();
    }

    /**
     * Determine whether the user can view the shop.
     */
    public function view(User $user, Shop $shop): bool
    {
        // オーナーは自身の店舗のみ閲覧可能
        return $user->isOwnerOf($shop);
    }

    /**
     * Determine whether the user can create shops.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // 管理者のみが作成可能
    }

    /**
     * Determine whether the user can update the shop.
     */
    public function update(User $user, Shop $shop): bool
    {
        // オーナーは自身の店舗のみ更新可能
        return $user->isOwnerOf($shop);
    }

    /**
     * Determine whether the user can delete the shop.
     */
    public function delete(User $user, Shop $shop): bool
    {
        // 管理者のみが削除可能
        return $user->isAdmin();
    }
}
