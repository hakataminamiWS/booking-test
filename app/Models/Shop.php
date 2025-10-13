<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'time_slot_interval',
        'cancellation_deadline_minutes',
        'booking_deadline_minutes',
        'booking_confirmation_type',
        'accepts_online_bookings',
        'status',
        'timezone',
    ];

    protected $casts = [
        // スキーマに合わせて不要な定義を削除
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_staff');
    }

    public function userShopProfiles(): HasMany
    {
        return $this->hasMany(UserShopProfile::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function shopRecurringHolidays(): HasMany
    {
        return $this->hasMany(ShopRecurringHoliday::class);
    }

    public function shopSpecificHolidays(): HasMany
    {
        return $this->hasMany(ShopSpecificHoliday::class);
    }

    public function staffSchedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }
}