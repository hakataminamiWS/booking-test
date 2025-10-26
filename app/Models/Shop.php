<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'email',
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

    public function staffs(): HasMany
    {
        return $this->hasMany(ShopStaff::class);
    }

    public function staffApplications(): HasMany
    {
        return $this->hasMany(ShopStaffApplication::class);
    }

    public function shopSpecialOpenDays(): HasMany
    {
        return $this->hasMany(ShopSpecialOpenDay::class);
    }

    public function shopSpecialClosedDays(): HasMany
    {
        return $this->hasMany(ShopSpecialClosedDay::class);
    }
}
