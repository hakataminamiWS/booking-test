<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'opening_time',
        'closing_time',
        'regular_holidays',
        'reservation_acceptance_settings',
    ];

    protected $casts = [
        'regular_holidays' => 'array',
        'reservation_acceptance_settings' => 'array',
        'opening_time' => 'datetime', // Carbonインスタンスとして扱う
        'closing_time' => 'datetime', // Carbonインスタンスとして扱う
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'shop_user')->withPivot('role');
    }
}