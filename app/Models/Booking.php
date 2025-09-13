<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'booker_id',
        'menu_id',
        'menu_name',
        'menu_price',
        'menu_duration',
        'requested_staff_id',
        'requested_staff_name',
        'assigned_staff_id',
        'assigned_staff_name',
        'start_at',
        'status',
        'name',
        'email',
        'tel',
        'memo',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function booker()
    {
        return $this->belongsTo(User::class, 'booker_id');
    }

    public function requestedStaff()
    {
        return $this->belongsTo(User::class, 'requested_staff_id');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'booking_option');
    }
}