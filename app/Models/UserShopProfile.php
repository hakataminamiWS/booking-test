<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShopProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'nickname',
        'contact_email',
        'contact_phone',
    ];

    /**
     * Get the user that owns the user shop profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop that owns the user shop profile.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
