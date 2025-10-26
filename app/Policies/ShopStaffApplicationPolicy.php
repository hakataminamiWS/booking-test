<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopStaffApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopStaffApplicationPolicy
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
    public function view(User $user, ShopStaffApplication $shopStaffApplication): bool
    {
        return $user->id === $shopStaffApplication->shop->owner_user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // オーナーは申し込みを作成しない
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShopStaffApplication $shopStaffApplication): bool
    {
        return $user->id === $shopStaffApplication->shop->owner_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopStaffApplication $shopStaffApplication): bool
    {
        return $user->id === $shopStaffApplication->shop->owner_user_id;
    }
}
