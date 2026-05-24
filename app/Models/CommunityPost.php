<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    use HasFactory;

    protected $table   = 'community_posts';
    protected $guarded = ['id'];
    protected $casts   = ['flagged' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->with('user');
    }

    public function topLevelComments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->with(['user', 'replies.user']);
    }
}
