<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropRecord extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'planting_date' => 'date',
        'area'          => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
