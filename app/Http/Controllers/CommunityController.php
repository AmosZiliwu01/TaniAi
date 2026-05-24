<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'comments.user']);

        // Search / filter
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua') {
            $query->where('category', $request->category);
        }

        $posts = $query->latest()->paginate(10)->withQueryString();

        // Active users: users with session activity in last 30 minutes
        $activeUsers = User::whereHas('posts')
            ->latest()
            ->limit(5)
            ->get();

        return view('community.index', compact('posts', 'activeUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'nullable|string',
            'crop_tag' => 'nullable|string|max:100',
            'image'    => 'nullable|image|max:5120',
        ]);

        $data['user_id'] = Auth::id();
        $data['likes']   = 0;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('community', 'public');
        }

        if (!empty($data['crop_tag'])) {
            $data['category'] = $data['category'] ?? 'Diskusi';
        }

        unset($data['crop_tag']);

        CommunityPost::create($data);
        return back()->with('status', 'Postingan dibuat.');
    }

    public function comment(Request $request, CommunityPost $post)
    {
        $data = $request->validate([
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        Comment::create([
            'user_id'           => Auth::id(),
            'community_post_id' => $post->id,
            'content'           => $data['content'],
            'parent_id'         => $data['parent_id'] ?? null,
        ]);

        return back();
    }

    public function like(CommunityPost $post)
    {
        $post->increment('likes');
        return back();
    }
}
