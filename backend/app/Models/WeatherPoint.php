<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'lat',
        'lng',
        'city',
        'country'
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

