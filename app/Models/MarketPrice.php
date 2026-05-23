<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;
    protected $table = 'market_prices';
    protected $guarded = ['id'];
}
