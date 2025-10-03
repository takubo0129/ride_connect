<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',              // car / bike
        'prefecture_id',     // 都道府県
        'city_id',           // 市区町村
        'maker',
        'maker_custom',      // その他入力
        'model',
        'fuel_type',
        'fuel_type_other',
        'year',
        'body_type',         // JSON文字列（車・バイク共通）
        'displacement_categories', // バイク用複数排気量カテゴリ
        'transmission',      // AT / MT
        'drivetrain',        // 2WD / 4WD / チェーン等
        'seats',
        'weight',
        'seat_height',
        'price_per_day',
        'sharing_periods',   // 利用可能期間（JSON）
        'description',
        'caution_notes',
        'images',            // 画像パス（JSON）
    ];

    protected $casts = [
        'body_type' => 'array',
        'displacement_categories' => 'array',
        'sharing_periods' => 'array',
        'caution_notes' => 'array',
        'images' => 'array',
        'seats' => 'integer',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function getDisplayMakerAttribute(): ?string
    {
        return ($this->maker === 'その他' && $this->maker_custom)
            ? $this->maker_custom
            : $this->maker;
    }

    public function getDisplaySeatsAttribute(): ?string
    {
        if (is_null($this->seats)) {
            return null;
        }

        return $this->seats >= 9 ? '9人以上' : $this->seats . '人';
    }

    /**
     * 車両の所有者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 車両の都道府県
     */
    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }

    /**
     * 車両の市区町村
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
