<?php

namespace App\Models;

use App\Models\ReservationMessage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PRE_CHECK_COMPLETED = 'pre_check_completed';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDING_REQUESTED = 'ending_requested';
    public const STATUS_RETURN_CONFIRMED = 'return_confirmed';
    public const STATUS_POST_CHECK_COMPLETED = 'post_check_completed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';

    public const BLOCKING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_PRE_CHECK_COMPLETED,
        self::STATUS_ACTIVE,
        self::STATUS_ENDING_REQUESTED,
        self::STATUS_RETURN_CONFIRMED,
        self::STATUS_POST_CHECK_COMPLETED,
    ];

    public static function blockingStatuses(): array
    {
        return self::BLOCKING_STATUSES;
    }

    protected $fillable = [
        'vehicle_id',
        'renter_id',
        'owner_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'sharing_period',
        'approved_at',
        'rejected_at',
        'chat_enabled_at',
        'pre_check_completed_at',
        'pre_check_items',
        'ride_started_at',
        'ride_ended_at',
        'owner_return_status',
        'return_confirmed_at',
        'post_check_completed_at',
        'post_check_items',
        'completed_at',
        'canceled_at',
        'cancel_reason',
        'reject_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'chat_enabled_at' => 'datetime',
        'pre_check_completed_at' => 'datetime',
        'pre_check_items' => 'array',
        'ride_started_at' => 'datetime',
        'ride_ended_at' => 'datetime',
        'return_confirmed_at' => 'datetime',
        'post_check_completed_at' => 'datetime',
        'post_check_items' => 'array',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function messages()
    {
        return $this->hasMany(ReservationMessage::class);
    }

    public function isOwner(int $userId): bool
    {
        return $this->owner_id === $userId;
    }

    public function isRenter(int $userId): bool
    {
        return $this->renter_id === $userId;
    }

    public function isChatEnabled(): bool
    {
        return ! is_null($this->chat_enabled_at);
    }

    public function needsOwnerAction(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_PRE_CHECK_COMPLETED,
            self::STATUS_ENDING_REQUESTED,
            self::STATUS_RETURN_CONFIRMED,
            self::STATUS_POST_CHECK_COMPLETED,
        ], true) && ! $this->isCompleted();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
