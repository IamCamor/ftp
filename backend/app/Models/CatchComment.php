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
        'moderation_status',
        'moderation_result',
        'moderated_at',
        'moderated_by',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'moderated_at' => 'datetime',
        'moderation_result' => 'array',
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

