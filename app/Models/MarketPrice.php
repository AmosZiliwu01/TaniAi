<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price'          => 'float',
        'change_percent' => 'float',
        'recorded_at'    => 'datetime',
    ];
}
