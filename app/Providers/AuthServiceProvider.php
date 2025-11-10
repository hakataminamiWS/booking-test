<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Shop;
use App\Models\ShopOption;
use App\Models\ShopStaffApplication;
use App\Models\User;
use App\Policies\BookingPolicy;
use App\Policies\ShopOptionPolicy;
use App\Policies\ShopPolicy;
use App\Policies\ShopStaffApplicationPolicy;
use App\Models\ShopStaff;
use App\Models\ShopMenu;
use App\Policies\ShopStaffPolicy;
use App\Policies\ShopMenuPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Shop::class => ShopPolicy::class,
        Booking::class => BookingPolicy::class,
        ShopStaffApplication::class => ShopStaffApplicationPolicy::class,
        ShopStaff::class => ShopStaffPolicy::class,
        ShopMenu::class => ShopMenuPolicy::class,
        ShopOption::class => ShopOptionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin-only-operation', function (User $user) {
            return $user->isAdmin();
        });
    }
}
