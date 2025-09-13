<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOAuthIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_sub',
    ];

    /**
     * Get the user that owns the OAuth identity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
