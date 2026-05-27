<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'location', 'phone', 'farmer_type',
        'avatar', 'avatar_color',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return $this->role === 'banned';
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function posts()
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function records()
    {
        return $this->hasMany(CropRecord::class);
    }

    /**
     * Get avatar color — deterministic from user name so it's always the same.
     */
    public function getAvatarGradientAttribute(): string
    {
        $gradients = [
            'from-violet-500 to-purple-600',
            'from-blue-500 to-cyan-600',
            'from-emerald-500 to-teal-600',
            'from-rose-500 to-pink-600',
            'from-amber-500 to-orange-600',
            'from-sky-500 to-blue-600',
            'from-lime-500 to-green-600',
            'from-fuchsia-500 to-violet-600',
        ];
        $idx = abs(crc32($this->name ?? '?')) % count($gradients);
        return $gradients[$idx];
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }
}
