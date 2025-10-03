<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nickname',
        'region',          // 都道府県
        'city',            // ← 新規追加（市町村）
        'gender',
        'age',
        'license_type',    // JSON形式で複数免許保存
        'license_years',
        'license_years_meta',
        'driving_frequency', 
        'driving_frequency_car',
        'driving_frequency_bike',
        'height',
        'weight',
        'bio',
        'owned_vehicle', 
        'owned_vehicles',
        'onboarding_completed',
    ];

    /**
     * license_type を配列として扱えるようにする
     */
    protected $casts = [
        'license_type' => 'array',
        'license_years_meta' => 'array',
        'owned_vehicles' => 'array',
        'onboarding_completed' => 'boolean',
        'age' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
