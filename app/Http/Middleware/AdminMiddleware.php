<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Banned users get force-logged out everywhere
        if ($user->role === 'banned') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah diblokir. Hubungi administrator.',
            ]);
        }

        if (!$user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
