<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatchImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'catch_id',
        'url',
        'alt_text',
        'order',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the catch that owns the image.
     */
    public function catch()
    {
        return $this->belongsTo(CatchRecord::class, 'catch_id');
    }
}





