<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopStaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_staff_id',
        'workable_start_at',
        'workable_end_at',
    ];

    protected $casts = [
        'workable_start_at' => 'datetime',
        'workable_end_at' => 'datetime',
    ];

    public function shopStaff(): BelongsTo
    {
        return $this->belongsTo(ShopStaff::class);
    }
}
