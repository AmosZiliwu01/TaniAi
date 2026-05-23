<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    use HasFactory;
    protected $table = 'ai_chats';
    protected $guarded = ['id'];
    public function user() { return $this->belongsTo(User::class); }
}
