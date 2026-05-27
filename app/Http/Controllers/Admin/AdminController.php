<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Diagnosis;
use App\Models\AiChat;
use App\Models\CommunityPost;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'     => User::count(),
            'diagnoses' => Diagnosis::count(),
            'ai_calls'  => AiChat::count(),
            'posts'     => CommunityPost::count(),
        ];
        $recentUsers     = User::latest()->limit(8)->get();
        $recentDiagnoses = Diagnosis::with('user')->latest()->limit(8)->get();
        return view('admin.dashboard', compact('stats','recentUsers','recentDiagnoses'));
    }

    // ── Users ─────────────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $q = User::withCount(['posts','diagnoses']);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn($b)=>$b->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
        }
        $users = $q->latest()->paginate(15)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function toggleRole(User $user)
    {
        if ($user->id === Auth::id()) return back()->with('error','Tidak dapat mengubah role sendiri.');
        $user->update(['role' => $user->role === 'admin' ? 'user' : 'admin']);
        return back()->with('status','Role pengguna diperbarui.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === Auth::id()) return back()->with('error','Tidak dapat hapus akun sendiri.');
        if ($user->isAdmin()) return back()->with('error','Tidak dapat hapus admin lain.');
        $user->delete();
        return back()->with('status','Pengguna dihapus.');
    }

    /** Ban = role 'banned' → blocks login in AuthController & AdminMiddleware */
    public function banUser(User $user)
    {
        if ($user->id === Auth::id()) return back()->with('error','Tidak dapat ban diri sendiri.');
        if ($user->isAdmin()) return back()->with('error','Tidak dapat ban admin.');

        $isBanned = $user->role === 'banned';
        $user->update(['role' => $isBanned ? 'user' : 'banned']);

        try {
            NotificationLog::create([
                'user_id' => $user->id,
                'title'   => $isBanned ? 'Akun Diaktifkan' : 'Akun Diblokir',
                'message' => $isBanned
                    ? 'Akun Anda telah diaktifkan kembali oleh administrator.'
                    : 'Akun Anda diblokir oleh administrator. Hubungi admin untuk informasi lebih lanjut.',
                'type'    => 'system',
            ]);
        } catch (\Throwable $e) {}

        return back()->with('status', $isBanned ? 'Blokir dicabut.' : 'Pengguna diblokir dari login.');
    }

    // ── Moderation ────────────────────────────────────────────────────────────
    public function moderation(Request $request)
    {
        $q = CommunityPost::with(['user','comments']);

        // Only flagged posts when filter=flagged
        if ($request->filter === 'flagged') {
            $q->where('flagged', true);
        }

        $posts         = $q->latest()->paginate(15)->withQueryString();
        $reportedCount = CommunityPost::where('flagged', true)->count();

        return view('admin.moderation', compact('posts','reportedCount'));
    }

    public function deletePost(CommunityPost $post)
    {
        // Notify owner (avoid dup: check last 5 min)
        $alreadyNotified = NotificationLog::where('user_id', $post->user_id)
            ->where('type','moderation')
            ->where('created_at','>',now()->subMinutes(5))
            ->exists();

        if (!$alreadyNotified) {
            try {
                NotificationLog::create([
                    'user_id' => $post->user_id,
                    'title'   => 'Postingan Dihapus Admin',
                    'message' => '"'.\Str::limit($post->title,60).'" dihapus karena melanggar pedoman komunitas.',
                    'type'    => 'moderation',
                ]);
            } catch (\Throwable $e) {}
        }

        $post->delete();
        return back()->with('status','Postingan dihapus.');
    }

    public function warnPost(CommunityPost $post)
    {
        // Dedup: only 1 warning per post per hour
        $alreadyWarned = NotificationLog::where('user_id', $post->user_id)
            ->where('type','warning')
            ->where('created_at','>',now()->subHour())
            ->exists();

        try { $post->update(['flagged'=>true]); } catch (\Throwable $e) {}

        if (!$alreadyWarned) {
            try {
                NotificationLog::create([
                    'user_id' => $post->user_id,
                    'title'   => 'Peringatan Konten',
                    'message' => 'Postingan Anda "'.\Str::limit($post->title,60).'" mendapat peringatan moderator. Harap patuhi pedoman komunitas.',
                    'type'    => 'warning',
                ]);
            } catch (\Throwable $e) {}
        }

        return back()->with('status','Peringatan dikirim.');
    }

    // ── Analytics ─────────────────────────────────────────────────────────────
    public function aiAnalytics()
    {
        $totals = ['chat' => AiChat::count(), 'diagnoses' => Diagnosis::count()];
        $byDay  = AiChat::selectRaw('DATE(created_at) as d, COUNT(*) as c')
                    ->groupBy('d')->orderBy('d')->limit(14)->get();
        return view('admin.ai-analytics', compact('totals','byDay'));
    }

    public function diagnosisLogs()
    {
        $logs = Diagnosis::with('user')->latest()->paginate(20);
        return view('admin.diagnosis-logs', compact('logs'));
    }

    // ── Admin profile / settings ──────────────────────────────────────────────
    public function profilePage()
    {
        $u = Auth::user();
        return view('admin.profile', compact('u'));
    }

    public function settingsPage()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'location' => 'nullable|string|max:255',
            'phone'    => 'nullable|string|max:50',
        ]);
        $user->update($data);
        return back()->with('status','Profil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);
        $user = Auth::user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('status','Password diperbarui.');
    }
}
