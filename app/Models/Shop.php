<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

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
