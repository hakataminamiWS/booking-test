<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'menu_id',
        'staff_id',
        'start_at',
        'name',
        'email',
        'tel',
        'gender_preference',
        'booker_id',
        'status',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function booker()
    {
        return $this->belongsTo(User::class, 'booker_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
