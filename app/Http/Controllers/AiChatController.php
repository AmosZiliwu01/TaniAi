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

        // Save user message
        AiChat::create([
            'user_id' => Auth::id(),
            'role'    => 'user',
            'message' => $data['message'],
        ]);

        // Build full conversation history for context
        $history = AiChat::where('user_id', Auth::id())
            ->orderBy('id')
            ->get()
            ->map(fn($c) => [
                'role'    => $c->role === 'user' ? 'user' : 'assistant',
                'content' => $c->message,
            ])
            ->toArray();

        // System prompt at the top — agricultural expert, always answers the question
        array_unshift($history, [
            'role'    => 'system',
            'content' => 'Anda adalah TaniAI, asisten ahli pertanian Indonesia. '
                . 'SELALU jawab pertanyaan user secara langsung dan spesifik — JANGAN pernah kembali ke sapaan awal atau template default saat user sudah bertanya. '
                . 'Jika pertanyaan berkaitan pertanian (menanam, hama, pupuk, panen, cuaca, harga, dll), berikan jawaban praktis yang berguna. '
                . 'Jika pertanyaan di luar pertanian, jawab singkat lalu arahkan kembali ke topik pertanian. '
                . 'Gunakan Bahasa Indonesia yang ramah dan mudah dipahami petani.',
        ]);

        $reply = $ai->chat($history);

        // Save assistant reply
        AiChat::create([
            'user_id' => Auth::id(),
            'role'    => 'assistant',
            'message' => $reply,
        ]);

        return redirect()->route('chat.index');
    }

    public function clear()
    {
        AiChat::where('user_id', Auth::id())->delete();
        return back()->with('status', 'Riwayat chat dibersihkan.');
    }
}
