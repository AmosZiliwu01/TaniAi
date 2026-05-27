<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show');
    }

    public function settings()
    {
        return view('profile.settings');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,'.$user->id,
            'location'    => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'farmer_type' => 'nullable|string|max:100',
        ]);
        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Profil diperbarui.']);
        }
        return back()->with('status', 'Profil berhasil diperbarui.');
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
        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
