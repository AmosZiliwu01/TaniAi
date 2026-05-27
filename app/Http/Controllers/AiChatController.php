<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Services\LlamaAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    public function index()
    {
        $chats = AiChat::where('user_id', Auth::id())->orderBy('id')->get();
        $cropContext = session('chat_crop_context');
        return view('chat.index', compact('chats', 'cropContext'));
    }

    public function send(Request $request, LlamaAIService $ai)
    {
        $request->validate(['message' => 'required|string|max:2000']);
        $message = trim($request->message);

        AiChat::create(['user_id' => Auth::id(), 'role' => 'user', 'message' => $message]);

        // Full conversation history
        $history = AiChat::where('user_id', Auth::id())
            ->orderBy('id')
            ->get()
            ->map(fn($c) => [
                'role'    => $c->role === 'user' ? 'user' : 'assistant',
                'content' => $c->message,
            ])
            ->toArray();

        array_unshift($history, [
            'role'    => 'system',
            'content' => 'Anda adalah Asisten Tani AI, asisten pertanian Indonesia yang ahli dan ramah. '
                . 'ATURAN WAJIB: '
                . '1. JAWAB pertanyaan user secara LANGSUNG — JANGAN reset ke sapaan awal. '
                . '2. HANYA bahas pertanian, tanaman, pupuk, hama, cuaca pertanian, budidaya, harga hasil tani. '
                . '3. Jika di luar domain pertanian — tolak dengan sopan dan arahkan ke topik pertanian. '
                . '4. Format jawaban RAPI: gunakan **bold** untuk poin penting, - untuk daftar, angka untuk langkah. '
                . '5. JANGAN mengarang data yang tidak ada. '
                . '6. Bahasa Indonesia yang mudah dipahami petani awam. '
                . 'User location: ' . (Auth::user()->location ?? 'tidak diketahui'),
        ]);

        $reply = $ai->chat($history, 0.45);

        AiChat::create(['user_id' => Auth::id(), 'role' => 'assistant', 'message' => $reply]);

        return redirect()->route('chat.index');
    }

    public function clear()
    {
        AiChat::where('user_id', Auth::id())->delete();
        return back()->with('status', 'Riwayat chat dibersihkan.');
    }

    /** Called from cultivation page — prefills crop context */
    public function withCrop(Request $request)
    {
        $crop = $request->validate(['crop' => 'required|string|max:100'])['crop'];
        session(['chat_crop_context' => $crop]);
        return redirect()->route('chat.index');
    }
}
