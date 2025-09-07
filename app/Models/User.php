<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_user')->withPivot('role');
    }

    public function bookingsAsBooker()
    {
        return $this->hasMany(Booking::class, 'booker_id');
    }

    public function bookingsAsStaff()
    {
        return $this->hasMany(Booking::class, 'staff_id');
    }

    public function isAdmin(): bool
    {
        // For now, let's assume user with email 'admin@gemini.com' is admin
        return $this->email === 'admin@gemini.com';
    }

    public function isOwnerOf(Shop $shop): bool
    {
        return $this->shops()->where('shop_id', $shop->id)->wherePivot('role', 'owner')->exists();
    }

    public function isStaffOf(Shop $shop): bool
    {
        return $this->shops()->where('shop_id', $shop->id)->wherePivot('role', 'staff')->exists();
    }
}
