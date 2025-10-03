<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected static function booted()
{
    static::created(function ($user) {
        $user->profile()->create([
            'nickname' => $user->name,
            'region' => '',
            'city' => '',
            'license_type' => [],
            'license_years' => null,
            'license_years_meta' => [],
            'driving_frequency' => null,
            'driving_frequency_car' => null,
            'driving_frequency_bike' => null,
            'owned_vehicle' => '',
            'owned_vehicles' => [
                'cars' => [],
                'bikes' => [],
            ],
            'height' => null,
            'weight' => null,
            'bio' => '',
            'onboarding_completed' => false,
        ]);
    });
}

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteVehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'favorites')->withTimestamps();
    }

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
}
