<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Determine whether the user can view any shops.
     */
    public function viewAny(User $user): bool
    {
        // ユーザーがオーナーであるか
        // つまり、有効な契約を持っているか
        return $user->contract()->where('status', 'active')->exists();
    }

    /**
     * Determine whether the user can view the shop.
     */
    public function view(User $user, Shop $shop): bool
    {
        // ユーザーがその店舗のオーナーであるか
        return $user->id === $shop->owner_user_id;
    }

    /**
     * Determine whether the user can create shops.
     */
    public function create(User $user): bool
    {
        // ユーザーが有効な契約を持っているか
        $contract = $user->contract;
        if (! $contract || $contract->status !== 'active') {
            return false;
        }

        // 店舗の作成上限に達していないか
        $ownedShopsCount = $user->shops()->count();

        return $ownedShopsCount < $contract->max_shops;
    }

    /**
     * Determine whether the user can update the shop.
     */
    public function update(User $user, Shop $shop): bool
    {
        // ユーザーがその店舗のオーナーであるか
        return $user->id === $shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the shop.
     */
    public function delete(User $user, Shop $shop): bool
    {
        // ユーザーがその店舗のオーナーであるか
        return $user->id === $shop->owner_user_id;
    }
}
