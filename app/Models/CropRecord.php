<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropRecord extends Model
{
    use HasFactory;
    protected $table = 'crop_records';
    protected $guarded = ['id'];
    public function user() { return $this->belongsTo(User::class); }
}
