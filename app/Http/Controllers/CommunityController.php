<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Comment;
use App\Models\User;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'comments' => function ($q) {
            $q->whereNull('parent_id')
              ->with(['user', 'replies.user'])
              ->orderBy('id');
        }]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($b) => $b->where('title','like',"%{$s}%")->orWhere('content','like',"%{$s}%"));
        }
        if ($request->filled('category') && $request->category !== 'Semua') {
            $query->where('category', $request->category);
        }

        $posts       = $query->latest()->paginate(10)->withQueryString();
        $activeUsers = User::whereHas('posts')->withCount('posts')->orderByDesc('posts_count')->limit(5)->get();

        return view('community.index', compact('posts','activeUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string|max:5000',
            'category' => 'nullable|string|max:50',
            'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        CommunityPost::create([
            'user_id'    => Auth::id(),
            'title'      => $data['title'],
            'content'    => $data['content'],
            'category'   => $data['category'] ?? 'Diskusi',
            'likes'      => 0,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('community', 'public')
                : null,
        ]);

        return back()->with('status', 'Postingan berhasil dibuat.');
    }

    public function update(Request $request, CommunityPost $post)
    {
        abort_unless($post->user_id === Auth::id(), 403);
        $data = $request->validate(['title'=>'required|string|max:255','content'=>'required|string|max:5000']);
        $post->update($data);
        return back()->with('status', 'Postingan diperbarui.');
    }

    public function destroy(CommunityPost $post)
    {
        abort_unless($post->user_id === Auth::id() || Auth::user()->isAdmin(), 403);
        $post->delete();
        if (request()->ajax()) return response()->json(['ok'=>true]);
        return back()->with('status', 'Postingan dihapus.');
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

        if ($post->user_id !== Auth::id()) {
            $this->notify($post->user_id, 'Komentar Baru',
                Auth::user()->name.' berkomentar: "'. \Str::limit($post->title,50).'"', 'community');
        }

        if (request()->ajax()) {
            return response()->json(['ok'=>true, 'count' => $post->comments()->count()]);
        }
        return back();
    }

    /** Toggle like — 1 user 1 like, AJAX JSON */
    public function like(CommunityPost $post)
    {
        $uid     = Auth::id();
        $key     = "like_{$post->id}_u{$uid}";
        $liked   = Cache::has($key);

        if ($liked) {
            $post->decrement('likes');
            Cache::forget($key);
            $liked = false;
        } else {
            $post->increment('likes');
            Cache::put($key, 1, now()->addDays(30));
            $liked = true;
            if ($post->user_id !== $uid) {
                $this->notify($post->user_id, 'Postingan Disukai',
                    Auth::user()->name.' menyukai postingan Anda.', 'community');
            }
        }

        $count = $post->fresh()->likes;
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['liked'=>$liked, 'count'=>$count]);
        }
        return back();
    }

    public function report(Request $request, CommunityPost $post)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        try { $post->update(['flagged'=>true, 'flag_reason'=>$data['reason']]); }
        catch (\Throwable $e) { $post->update(['flagged'=>true]); }
        return back()->with('status', 'Laporan berhasil dikirim. Tim moderator akan meninjau.');
    }

    private function notify(int $userId, string $title, string $msg, string $type='system'): void
    {
        try {
            NotificationLog::create(['user_id'=>$userId,'title'=>$title,'message'=>$msg,'type'=>$type]);
        } catch (\Throwable $e) {}
    }
}
