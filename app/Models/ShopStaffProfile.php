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
        'small_image_path',
        'large_image_path',
    ];

    protected $appends = [
        'small_image_url',
        'large_image_url',
    ];

    public function shopStaff(): BelongsTo
    {
        return $this->belongsTo(ShopStaff::class);
    }

    protected function smallImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->small_image_path ? Storage::url($this->small_image_path) : null,
        );
    }

    protected function largeImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->large_image_path ? Storage::url($this->large_image_path) : null,
        );
    }
}
