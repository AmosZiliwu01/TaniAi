<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'confidence'      => 'integer',
        'recommendations' => 'array',
        'causes'          => 'array',
        'solutions'       => 'array',
        'prevention'      => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
