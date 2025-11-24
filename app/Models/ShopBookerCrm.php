<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopBookerCrm extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_bookers_crm';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_booker_id',
        'name_kana',
        'shop_memo',
        'last_booking_at',
        'booking_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_booking_at' => 'datetime',
    ];

    /**
     * Get the shop booker that owns the crm record.
     */
    public function shopBooker(): BelongsTo
    {
        return $this->belongsTo(ShopBooker::class);
    }
}
