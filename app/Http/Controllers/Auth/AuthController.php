<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }
    public function showForgot() { return view('auth.forgot'); }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $remember = $request->boolean('remember');
        if (Auth::attempt($data, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(Auth::user()->isAdmin() ? '/admin' : '/dashboard');
        }
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'location' => 'nullable|string|max:255',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'user';
        $user = User::create($data);
        Auth::login($user);
        return redirect('/dashboard');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        // In real app: Password::sendResetLink(...) - here just flash a success.
        return back()->with('status', 'Jika email terdaftar, link reset password telah dikirim.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
