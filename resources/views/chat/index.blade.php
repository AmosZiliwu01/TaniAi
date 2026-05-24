@extends('layouts.app')
@section('title','Penyuluh AI Chat')
@section('content')

<div class="mb-4 flex items-center justify-between">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Penyuluh AI Chat</h1>
        <p class="text-ink-500 mt-0.5 text-sm">Tanya apapun seputar pertanian — dijawab AI.</p>
    </div>
    <form method="POST" action="{{ route('chat.clear') }}">@csrf
        <button class="btn-outline text-sm" onclick="return confirm('Bersihkan semua riwayat chat?')">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Bersihkan</span>
        </button>
    </form>
</div>

<div class="card flex flex-col" style="height: calc(100vh - 210px); min-height: 500px;" x-data="{ sending: false }">

    {{-- Chat messages --}}
    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 scrollbar-thin" id="chatScroll">

        {{-- Empty state --}}
        @if($chats->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-center py-6">
                <div class="w-16 h-16 rounded-2xl bg-brand-gradient grid place-items-center text-white mx-auto shadow-glow mb-4">
                    <i data-lucide="bot" class="w-8 h-8"></i>
                </div>
                <div class="font-bold text-lg">Hai! Saya TaniAI 🌱</div>
                <div class="text-sm text-ink-500 mt-1 max-w-sm">Asisten pertanian cerdas siap membantu. Tanya soal penyakit tanaman, pupuk, cuaca, atau strategi panen.</div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2 w-full max-w-xl">
                    @foreach([
                        'Bagaimana cara mengatasi hawar daun pada padi?',
                        'Rekomendasi pupuk untuk cabai di musim hujan',
                        'Kapan waktu terbaik panen jagung?',
                        'Cara mengatasi serangan wereng coklat',
                        'Jenis pupuk apa yang cocok untuk tomat berbuah?',
                        'Cara menanam bawang merah yang benar',
                    ] as $s)
                        <form method="POST" action="{{ route('chat.send') }}">@csrf
                            <input type="hidden" name="message" value="{{ $s }}">
                            <button type="submit" class="text-left w-full p-3 rounded-xl border border-ink-200 bg-ink-50 hover:bg-brand-50 hover:border-brand-200 text-sm transition">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5 text-brand-600 inline mr-1"></i>
                                {{ $s }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Message list --}}
        @foreach($chats as $c)
            @if($c->role === 'user')
                <div class="flex justify-end gap-2">
                    <div class="max-w-[85%] sm:max-w-[75%] bg-brand-gradient text-white px-4 py-3 rounded-2xl rounded-tr-sm shadow-sm">
                        <div class="text-sm whitespace-pre-wrap leading-relaxed">{{ $c->message }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-ink-100 grid place-items-center text-ink-600 shrink-0 self-end">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                </div>
            @else
                <div class="flex gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-brand-100 text-brand-700 grid place-items-center shrink-0 self-start mt-0.5">
                        <i data-lucide="bot" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </div>
                    <div class="max-w-[85%] sm:max-w-[75%]">
                        <div class="bg-ink-50 border border-ink-200 px-4 py-3 rounded-2xl rounded-tl-sm">
                            <div class="text-sm text-ink-800 leading-relaxed chat-message">{!! nl2br(e($c->message)) !!}</div>
                        </div>
                        <div class="text-[10px] text-ink-400 mt-1 ml-1">
                            TaniAI · {{ $c->created_at->isoFormat('HH:mm') }}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Sending indicator --}}
        <div x-show="sending" x-cloak class="flex gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 grid place-items-center shrink-0">
                <i data-lucide="bot" class="w-5 h-5"></i>
            </div>
            <div class="bg-ink-50 border border-ink-200 px-4 py-3 rounded-2xl rounded-tl-sm">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Input form --}}
    <div class="border-t border-ink-200 p-3 sm:p-4">
        <form method="POST" action="{{ route('chat.send') }}"
            @submit="sending=true"
            class="flex items-end gap-2">@csrf
            <textarea
                name="message"
                required
                rows="1"
                class="input flex-1 resize-none overflow-hidden"
                placeholder="Tanyakan seputar pertanian..."
                maxlength="2000"
                @input="this.style.height='auto'; this.style.height=Math.min(this.scrollHeight,120)+'px'"
                @keydown.enter.prevent="if(!$event.shiftKey){ $el.form.requestSubmit(); sending=true; }"
            ></textarea>
            <button class="btn-primary h-10 px-4 shrink-0 self-end" :disabled="sending">
                <template x-if="!sending">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </template>
                <template x-if="sending">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                </template>
            </button>
        </form>
        <div class="mt-1.5 text-[10px] text-ink-400 text-center">
            Enter untuk kirim · Shift+Enter untuk baris baru · Respons dari AI bisa berbeda dengan kondisi nyata
        </div>
    </div>
</div>

<style>
/* Bold text in chat messages */
.chat-message strong, .chat-message b { font-weight: 700; }
/* Numbered / bulleted lists in chat */
.chat-message { white-space: pre-wrap; }
</style>

<script>
    // Auto-scroll to bottom
    const chatScroll = document.getElementById('chatScroll');
    if (chatScroll) {
        chatScroll.scrollTop = chatScroll.scrollHeight;
    }
</script>
@endsection
