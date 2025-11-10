<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopMenu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'duration',
        'description',
        'requires_staff_assignment',
        'requires_cancellation_deadline',
        'cancellation_deadline_minutes',
        'requires_booking_deadline',
        'booking_deadline_minutes',
    ];

    /**
     * Get the shop that owns the ShopMenu.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * The staff that belong to the ShopMenu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(ShopStaff::class, 'shop_menu_staffs', 'shop_menu_id', 'shop_staff_id');
    }

    /**
     * The options that belong to the ShopMenu.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ShopOption::class, 'shop_menu_options', 'shop_menu_id', 'shop_option_id');
    }
}

