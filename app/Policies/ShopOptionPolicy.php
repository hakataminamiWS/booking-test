<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopOption;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopOptionPolicy
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
    public function view(User $user, ShopOption $shopOption): bool
    {
        return $user->id === $shopOption->shop->owner_user_id;
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
    public function update(User $user, ShopOption $shopOption): bool
    {
        return $user->id === $shopOption->shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopOption $shopOption): bool
    {
        return $user->id === $shopOption->shop->owner_user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShopOption $shopOption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShopOption $shopOption): bool
    {
        return false;
    }
}
