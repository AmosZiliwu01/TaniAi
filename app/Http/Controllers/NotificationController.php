<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $items = NotificationLog::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);
        return view('notifications.index', compact('items'));
    }

    public function markRead()
    {
        NotificationLog::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return back()->with('status', 'Semua notifikasi ditandai dibaca.');
    }
}
