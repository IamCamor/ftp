<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'catch_id',
        'user_id',
        'body',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
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

