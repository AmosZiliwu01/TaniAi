<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Diagnosis;
use App\Models\AiChat;
use App\Models\CommunityPost;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'diagnoses' => Diagnosis::count(),
            'ai_calls' => AiChat::count(),
            'posts' => CommunityPost::count(),
        ];
        $recentUsers = User::latest()->limit(8)->get();
        $recentDiagnoses = Diagnosis::with('user')->latest()->limit(8)->get();
        return view('admin.dashboard', compact('stats','recentUsers','recentDiagnoses'));
    }

    public function users()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function toggleRole(User $user)
    {
        $user->update(['role' => $user->role === 'admin' ? 'user' : 'admin']);
        return back();
    }

    public function destroyUser(User $user)
    {
        if ($user->isAdmin()) return back()->withErrors(['user'=>'Tidak dapat menghapus admin lain.']);
        $user->delete();
        return back();
    }

    public function aiAnalytics()
    {
        $totals = [
            'chat' => AiChat::count(),
            'diagnoses' => Diagnosis::count(),
        ];
        $byDay = AiChat::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->orderBy('d')->limit(14)->get();
        return view('admin.ai-analytics', compact('totals','byDay'));
    }

    public function diagnosisLogs()
    {
        $logs = Diagnosis::with('user')->latest()->paginate(20);
        return view('admin.diagnosis-logs', compact('logs'));
    }

    public function moderation()
    {
        $posts = CommunityPost::with('user')->latest()->paginate(15);
        return view('admin.moderation', compact('posts'));
    }

    public function deletePost(CommunityPost $post)
    {
        $post->delete();
        return back();
    }
}
