<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'booker_id',
        'type',
        'menu_id',
        'menu_name',
        'menu_price',
        'menu_duration',
        'requested_staff_id',
        'requested_staff_name',
        'assigned_staff_id',
        'assigned_staff_name',
        'start_at',
        'name',
        'email',
        'tel',
        'memo',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(Booker::class, 'booker_id');
    }

    public function requestedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_staff_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'booking_option');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(BookingStatus::class);
    }

    public function latestStatus(): HasOne
    {
        return $this->hasOne(BookingStatus::class)->latestOfMany();
    }
}