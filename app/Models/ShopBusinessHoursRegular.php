<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopBusinessHoursRegular extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'day_of_week',
        'is_open',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
