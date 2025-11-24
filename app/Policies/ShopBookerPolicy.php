<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\User;

class ShopBookerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_user_id;
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
    public function update(User $user, ShopBooker $booker): bool
    {
        return $user->id === $booker->shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopBooker $booker): bool
    {
        return $user->id === $booker->shop->owner_user_id;
    }
}
