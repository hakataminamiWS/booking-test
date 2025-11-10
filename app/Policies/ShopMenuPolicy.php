<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopMenu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopMenuPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_user_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShopMenu $shopMenu): bool
    {
        return $user->id === $shopMenu->shop->owner_user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShopMenu $shopMenu): bool
    {
        return $user->id === $shopMenu->shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopMenu $shopMenu): bool
    {
        return $user->id === $shopMenu->shop->owner_user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShopMenu $shopMenu): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShopMenu $shopMenu): bool
    {
        return false;
    }
}