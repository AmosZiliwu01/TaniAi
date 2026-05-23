@extends('layouts.app')
@section('title','Penyuluh AI Chat')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Penyuluh AI Chat</h1>
        <p class="text-ink-500 mt-1">Tanya apapun seputar pertanian.</p>
    </div>
    <form method="POST" action="{{ route('chat.clear') }}">@csrf
        <button class="btn-outline text-sm"><i data-lucide="trash-2" class="w-4 h-4"></i> Bersihkan</button>
    </form>
</div>

<div class="card flex flex-col h-[calc(100vh-220px)] min-h-[500px]" x-data="{ sending:false }">
    <div class="flex-1 overflow-y-auto p-5 space-y-4 scrollbar-thin" id="chatScroll">
        @if($chats->isEmpty())
            <div class="text-center py-10">
                <div class="w-16 h-16 rounded-2xl bg-brand-gradient grid place-items-center text-white mx-auto shadow-glow">
                    <i data-lucide="bot" class="w-8 h-8"></i>
                </div>
                <div class="font-bold mt-3">Hai! Saya TaniAI</div>
                <div class="text-sm text-ink-500">Asisten pintar untuk pertanian yang lebih baik.</div>
                <div class="mt-5 grid sm:grid-cols-2 gap-2 max-w-xl mx-auto">
                    @foreach(['Bagaimana cara mengatasi hawar daun pada padi?','Rekomendasi pupuk untuk cabai musim hujan','Kapan waktu terbaik panen jagung?','Cara mengatasi serangan wereng'] as $s)
                        <form method="POST" action="{{ route('chat.send') }}">@csrf
                            <input type="hidden" name="message" value="{{ $s }}">
                            <button class="text-left w-full p-3 rounded-xl bg-ink-50 hover:bg-brand-50 text-sm transition">{{ $s }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach($chats as $c)
            @if($c->role === 'user')
                <div class="flex justify-end">
                    <div class="max-w-[80%] bg-brand-gradient text-white p-4 rounded-2xl rounded-tr-sm shadow-glow">
                        <div class="whitespace-pre-wrap text-sm">{{ $c->message }}</div>
                    </div>
                </div>
            @else
                <div class="flex gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 grid place-items-center shrink-0"><i data-lucide="bot" class="w-5 h-5"></i></div>
                    <div class="max-w-[80%] bg-ink-50 p-4 rounded-2xl rounded-tl-sm">
                        <div class="whitespace-pre-wrap text-sm text-ink-800">{{ $c->message }}</div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <form method="POST" action="{{ route('chat.send') }}" class="border-t border-ink-200 p-4" @submit="sending=true">@csrf
        <div class="flex items-center gap-2">
            <button type="button" class="p-2.5 rounded-xl hover:bg-ink-50"><i data-lucide="mic" class="w-5 h-5 text-ink-500"></i></button>
            <input name="message" required class="input flex-1" placeholder="Tanyakan sesuatu..." autofocus>
            <button class="btn-primary" :disabled="sending">
                <template x-if="!sending"><i data-lucide="send" class="w-4 h-4"></i></template>
                <template x-if="sending"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i></template>
            </button>
        </div>
    </form>
</div>

<script>
    const s = document.getElementById('chatScroll');
    if (s) s.scrollTop = s.scrollHeight;
</script>
@endsection
