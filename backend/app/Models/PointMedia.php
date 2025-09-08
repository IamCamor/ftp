<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_id',
        'url',
    ];

    public function point()
    {
        return $this->belongsTo(Point::class);
    }
}

