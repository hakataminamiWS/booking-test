<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Shop extends Model
{
    use HasFactory, Notifiable;

    /**
     * Route notifications for the mail channel.
     *
     * @return string|null
     */
    public function routeNotificationForMail($notification): ?string
    {
        return $this->email;
    }

    public const RESERVED_SLUGS = [
        'create',
        'edit',
    ];

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'email',
        'time_slot_interval',
        'cancellation_deadline_minutes',
        'booking_deadline_minutes',
        'booking_confirmation_type',
        'accepts_online_bookings',
        'status',
        'timezone',
    ];

    protected $casts = [
        // スキーマに合わせて不要な定義を削除
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(ShopStaff::class);
    }

    public function staffApplications(): HasMany
    {
        return $this->hasMany(ShopStaffApplication::class);
    }

    public function businessHoursRegular(): HasMany
    {
        return $this->hasMany(ShopBusinessHoursRegular::class);
    }

    public function shopSpecialOpenDays(): HasMany
    {
        return $this->hasMany(ShopSpecialOpenDay::class);
    }

    public function shopSpecialClosedDays(): HasMany
    {
        return $this->hasMany(ShopSpecialClosedDay::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(ShopMenu::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ShopOption::class);
    }

    public function bookers(): HasMany
    {
        return $this->hasMany(ShopBooker::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function shifts(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ShopStaffSchedule::class, ShopStaff::class);
    }

    // ======================================================================
    // Relationship Aliases for Route Model Binding (scopeBindings)
    // ======================================================================

    /**
     * Alias for staffs() to support route parameter {staff}
     */
    public function staff(): HasMany
    {
        return $this->staffs();
    }

    /**
     * Alias for menus() to support route parameter {menu}
     */
    public function menu(): HasMany
    {
        return $this->menus();
    }

    /**
     * Alias for options() to support route parameter {option}
     */
    public function option(): HasMany
    {
        return $this->options();
    }

    /**
     * Alias for bookings() to support route parameter {booking}
     */
    public function booking(): HasMany
    {
        return $this->bookings();
    }

    /**
     * Alias for bookers() to support route parameter {booker}
     */
    public function booker(): HasMany
    {
        return $this->bookers();
    }

    /**
     * Alias for staffApplications() to support route parameter {staff_application}
     */
    public function staffApplication(): HasMany
    {
        return $this->staffApplications();
    }

    /**
     * Alias for shopSpecialOpenDays() to support route parameter {special_open_day}
     */
    public function shopSpecialOpenDay(): HasMany
    {
        return $this->shopSpecialOpenDays();
    }

    /**
     * Alias for shopSpecialClosedDays() to support route parameter {special_closed_day}
     */
    public function shopSpecialClosedDay(): HasMany
    {
        return $this->shopSpecialClosedDays();
    }

    /**
     * Alias for shopSpecialOpenDays() to support route parameter {special_open_day} (inferred plural)
     */
    public function specialOpenDays(): HasMany
    {
        return $this->shopSpecialOpenDays();
    }

    /**
     * Alias for shopSpecialClosedDays() to support route parameter {special_closed_day} (inferred plural)
     */
    public function specialClosedDays(): HasMany
    {
        return $this->shopSpecialClosedDays();
    }
}
