<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'option_id',
        'option_name',
        'option_price',
        'option_duration',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ShopOption::class, 'option_id');
    }
}
