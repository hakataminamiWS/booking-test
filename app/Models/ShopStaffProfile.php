<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ShopStaffProfile extends Model
{
    use HasFactory;

    protected $table = 'shop_staff_profiles';

    protected $fillable = [
        'shop_staff_id',
        'nickname',
        'small_image_url',
        'large_image_url',
    ];

    public function shopStaff(): BelongsTo
    {
        return $this->belongsTo(ShopStaff::class);
    }
}
