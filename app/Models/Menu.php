<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shop_id',
        'name',
        'price',
        'description',
        'duration',
        'booking_deadline_minutes',
        'requires_staff_assignment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_staff_assignment' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'menu_option');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'menu_staff');
    }
}