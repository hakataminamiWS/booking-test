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
                // 同じshop_id内での最大値+1を設定
                $maxNumber = static::where('shop_id', $shopBooker->shop_id)->max('number') ?? 0;
                $shopBooker->number = $maxNumber + 1;
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
}
