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
