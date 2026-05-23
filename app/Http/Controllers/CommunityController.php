<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index()
    {
        $posts = CommunityPost::with('user','comments.user')->latest()->paginate(10);
        return view('community.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
        ]);
        $data['user_id'] = Auth::id();
        $data['likes'] = 0;
        CommunityPost::create($data);
        return back()->with('status','Postingan dibuat.');
    }

    public function comment(Request $request, CommunityPost $post)
    {
        $data = $request->validate(['content' => 'required|string|max:1000']);
        Comment::create([
            'user_id' => Auth::id(),
            'community_post_id' => $post->id,
            'content' => $data['content'],
        ]);
        return back();
    }

    public function like(CommunityPost $post)
    {
        $post->increment('likes');
        return back();
    }
}
