<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'is_guest',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'public_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_guest' => 'boolean',
        ];
    }

    /**
     * Get the staff schedules for the user.
     */
    public function staffSchedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class, 'staff_user_id');
    }

    /**
     * Get the OAuth identities for the user.
     */
    public function oauthIdentities(): HasMany
    {
        return $this->hasMany(UserOAuthIdentity::class);
    }

    /**
     * Get the shops owned by the user.
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'owner_user_id');
    }

    /**
     * Get the shops where the user is a staff member.
     */
    public function staffShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_staff');
    }

    /**
     * Get the user shop profiles for the user.
     */
    public function userShopProfiles(): HasMany
    {
        return $this->hasMany(UserShopProfile::class);
    }

    /**
     * Get the contract for the user.
     */
    public function contract(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contract::class);
    }

    /**
     * Get the booker identifiers for the user.
     */
    public function bookers(): HasMany
    {
        return $this->hasMany(Booker::class);
    }

    public function bookingsAsRequestedStaff(): HasMany
    {
        return $this->hasMany(Booking::class, 'requested_staff_id');
    }

    public function bookingsAsAssignedStaff(): HasMany
    {
        return $this->hasMany(Booking::class, 'assigned_staff_id');
    }

    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    public function isOwnerOf(Shop $shop): bool
    {
        return $this->id === $shop->owner_user_id;
    }

    public function isStaffOf(Shop $shop): bool
    {
        return $this->staffShops()->where('shop_id', $shop->id)->exists();
    }

    // Adminモデルへのリレーションを追加
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    // Ownerモデルへのリレーションを追加
    public function owner()
    {
        return $this->hasOne(Owner::class);
    }
}
