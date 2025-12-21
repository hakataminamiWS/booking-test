<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * Get the shops owned by the user.
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class, 'owner_user_id');
    }

    /**
     * Get the contract for the user.
     */
    public function contract(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contract::class);
    }

    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    public function isOwnerOf(Shop $shop): bool
    {
        return $this->id === $shop->owner_user_id;
    }

    // Adminモデルへのリレーションを追加
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the shop bookers associated with the user.
     */
    public function shopBookers(): HasMany
    {
        return $this->hasMany(ShopBooker::class);
    }
}
