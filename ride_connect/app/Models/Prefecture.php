<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefecture extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * 都道府県に属する市区町村
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * 都道府県に属する車両
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
