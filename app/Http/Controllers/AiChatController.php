<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Services\GrokAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    public function index()
    {
        $chats = AiChat::where('user_id', Auth::id())->orderBy('id')->get();
        return view('chat.index', compact('chats'));
    }

    public function send(Request $request, GrokAIService $ai)
    {
        $data = $request->validate(['message' => 'required|string|max:2000']);

        AiChat::create([
            'user_id' => Auth::id(),
            'role' => 'user',
            'message' => $data['message'],
        ]);

        $history = AiChat::where('user_id', Auth::id())->orderBy('id')->get()
            ->map(fn($c) => ['role' => $c->role === 'user' ? 'user' : 'assistant', 'content' => $c->message])
            ->toArray();

        array_unshift($history, [
            'role' => 'system',
            'content' => 'Anda adalah TaniAI, asisten pintar untuk petani Indonesia. Jawab ringkas, praktis, dan ramah dalam Bahasa Indonesia.',
        ]);

        $reply = $ai->chat($history);

        AiChat::create([
            'user_id' => Auth::id(),
            'role' => 'assistant',
            'message' => $reply,
        ]);

        return redirect()->route('chat.index');
    }

    public function clear()
    {
        AiChat::where('user_id', Auth::id())->delete();
        return back();
    }
}
