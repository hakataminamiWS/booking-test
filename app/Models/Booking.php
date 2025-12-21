<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BookingOption;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shop_booker_id',
        'status',
        'menu_id',
        'menu_name',
        'menu_price',
        'menu_duration',
        'assigned_staff_id',
        'assigned_staff_name',
        'timezone',
        'start_at',
        'end_at',
        'booker_name',
        'contact_email',
        'contact_phone',
        'note_from_booker',
        'shop_memo',
        'booking_channel',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(ShopBooker::class, 'shop_booker_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(ShopStaff::class, 'assigned_staff_id');
    }
    
    public function bookingOptions(): HasMany
    {
        return $this->hasMany(BookingOption::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(ShopMenu::class, 'menu_id');
    }
}
