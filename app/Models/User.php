<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','location','phone','avatar','farmer_type'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function isAdmin(): bool { return $this->role === 'admin'; }

    public function diagnoses() { return $this->hasMany(Diagnosis::class); }
    public function chats() { return $this->hasMany(AiChat::class); }
    public function records() { return $this->hasMany(CropRecord::class); }
    public function posts() { return $this->hasMany(CommunityPost::class); }
    public function notifications_log() { return $this->hasMany(NotificationLog::class); }
}
