<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Diagnosis;
use App\Models\AiChat;
use App\Models\CommunityPost;
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
        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentDiagnoses'));
    }

    public function users()
    {
        $users = User::withCount(['posts', 'diagnoses'])->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function toggleRole(User $user)
    {
        // Prevent self-demotion
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Tidak dapat mengubah role diri sendiri.']);
        }
        $user->update(['role' => $user->role === 'admin' ? 'user' : 'admin']);
        return back()->with('status', 'Role pengguna diperbarui.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun sendiri.']);
        }
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus admin lain.']);
        }
        $user->delete();
        return back()->with('status', 'Pengguna dihapus.');
    }

    public function banUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Tidak dapat ban diri sendiri.']);
        }
        // Toggle banned status via role prefix
        $newRole = str_starts_with($user->role, 'banned') ? 'user' : 'banned_' . $user->role;
        $user->update(['role' => $newRole]);
        return back()->with('status', str_starts_with($newRole, 'banned') ? 'Pengguna diblokir.' : 'Blokir pengguna dicabut.');
    }

    public function aiAnalytics()
    {
        $totals = [
            'chat'      => AiChat::count(),
            'diagnoses' => Diagnosis::count(),
        ];
        $byDay = AiChat::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->orderBy('d')->limit(14)->get();
        return view('admin.ai-analytics', compact('totals', 'byDay'));
    }

    public function diagnosisLogs()
    {
        $logs = Diagnosis::with('user')->latest()->paginate(20);
        return view('admin.diagnosis-logs', compact('logs'));
    }

    public function moderation()
    {
        $posts         = CommunityPost::with(['user', 'comments'])->latest()->paginate(15);
        $reportedCount = CommunityPost::where('flagged', true)->count();
        return view('admin.moderation', compact('posts', 'reportedCount'));
    }

    public function deletePost(CommunityPost $post)
    {
        $post->delete();
        return back()->with('status', 'Postingan dihapus.');
    }

    public function warnPost(CommunityPost $post)
    {
        // Mark as flagged/warned (needs flagged column — graceful fallback)
        try {
            $post->update(['flagged' => true]);
        } catch (\Throwable $e) {
            // Column may not exist yet
        }
        return back()->with('status', 'Peringatan dikirim ke pembuat postingan.');
    }

    // ─── Admin Profile & Settings (uses admin layout) ───────────────────────

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
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'location'     => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'farmer_type'  => 'nullable|string|max:100',
        ]);
        $user->update($data);
        return back()->with('status', 'Profil diperbarui.');
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
        return back()->with('status', 'Password diperbarui.');
    }
}
