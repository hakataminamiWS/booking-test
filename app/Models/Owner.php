<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contract::class, 'user_id', 'user_id');
    }

    /**
     * Get all of the contracts for the owner through the user.
     */
    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Contract::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    /**
     * Get all of the shops for the owner.
     */
    public function shops(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Shop::class, 'owner_user_id', 'user_id');
    }
}
