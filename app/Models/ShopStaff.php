<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopStaff extends Model
{
    use HasFactory;

    protected $table = 'shop_staffs';

    protected $fillable = [
        'shop_id',
        'user_id',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ShopStaffProfile::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ShopStaffSchedule::class);
    }
}
