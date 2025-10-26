<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopStaff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopStaffPolicy
{
    use HandlesAuthorization;

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
    public function view(User $user, ShopStaff $shopStaff): bool
    {
        return $user->id === $shopStaff->shop->owner_user_id;
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
    public function update(User $user, ShopStaff $shopStaff): bool
    {
        return $user->id === $shopStaff->shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopStaff $shopStaff): bool
    {
        return $user->id === $shopStaff->shop->owner_user_id;
    }
}