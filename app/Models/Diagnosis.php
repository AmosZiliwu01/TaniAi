<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use HasFactory;
    protected $table = 'diagnoses';
    protected $guarded = ['id'];
    protected $casts = ['recommendations' => 'array', 'confidence' => 'float'];
    public function user() { return $this->belongsTo(User::class); }
}
