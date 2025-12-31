<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopBooker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
        'user_id',
        'number',
        'name',
        'contact_email',
        'contact_phone',
        'note_from_booker',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($shopBooker) {
            if (empty($shopBooker->number)) {
                $sqids = new \Sqids\Sqids(minLength: 6);
                // microtime(true) returns float like 1697701234.5678
                // Multiply by 1000 to get milliseconds: 1697701234567.8
                // Cast to int: 1697701234567
                $timestamp = (int) (microtime(true) * 1000);
                $shopBooker->number = $sqids->encode([$timestamp]);
            }
        });
    }

    /**
     * Get the shop that owns the booker.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the crm record associated with the booker.
     */
    public function crm(): HasOne
    {
        return $this->hasOne(ShopBookerCrm::class);
    }

    /**
     * Get the bookings for the booker.
     */
    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class, 'shop_booker_id');
    }
}
