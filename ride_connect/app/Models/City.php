<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['prefecture_id', 'name'];

    /**
     * 市区町村が属する都道府県
     */
    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }

    /**
     * 市区町村に属する車両
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
