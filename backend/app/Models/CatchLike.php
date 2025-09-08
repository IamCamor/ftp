<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'catch_id',
        'user_id',
    ];

    public function catchRecord()
    {
        return $this->belongsTo(CatchRecord::class, 'catch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

