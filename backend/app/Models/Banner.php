<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot',
        'image_url',
        'click_url',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_at')
                          ->orWhere('start_at', '<=', \Carbon\Carbon::now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_at')
                          ->orWhere('end_at', '>=', \Carbon\Carbon::now());
                    });
    }

    public function scopeForSlot($query, $slot)
    {
        return $query->where('slot', $slot);
    }
}
