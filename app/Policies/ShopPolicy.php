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
        return true; // Anyone can view shops for now
    }

    /**
     * Determine whether the user can view the shop.
     */
    public function view(User $user, Shop $shop): bool
    {
        return true; // Anyone can view a specific shop for now
    }

    /**
     * Determine whether the user can create shops.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Only admins can create shops
    }

    /**
     * Determine whether the user can update the shop.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $user->isAdmin() || $user->isOwnerOf($shop); // Admins or shop owners can update
    }

    /**
     * Determine whether the user can delete the shop.
     */
    public function delete(User $user, Shop $shop): bool
    {
        return $user->isAdmin() || $user->isOwnerOf($shop); // Admins or shop owners can delete
    }
}
