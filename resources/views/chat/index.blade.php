@extends('layouts.app')
@section('title','Asisten Tani AI')
@section('content')

<div class="mb-4 flex items-center justify-between">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Asisten Tani AI</h1>
        <p class="text-ink-500 mt-0.5 text-sm">Asisten AI khusus pertanian Indonesia · powered by Llama 4</p>
    </div>
    <form method="POST" action="{{ route('chat.clear') }}">@csrf
        <button class="btn-outline text-sm" onclick="return confirm('Bersihkan riwayat chat?')">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            <span class="hidden sm:inline ml-1">Bersihkan</span>
        </button>
    </form>
</div>

{{-- Crop context banner --}}
@if($cropContext)
<div class="mb-3 px-4 py-2.5 rounded-xl bg-brand-50 border border-brand-200 flex items-center gap-2 text-sm">
    <i data-lucide="sprout" class="w-4 h-4 text-brand-600 shrink-0"></i>
    <span class="text-brand-800">Konteks dari panduan: <b>{{ $cropContext }}</b></span>
    <form method="POST" action="{{ route('chat.clear') }}" class="ml-auto">@csrf
        <button class="text-xs text-brand-600 hover:underline">Hapus konteks</button>
    </form>
</div>
@endif

<div class="card flex flex-col" style="height:calc(100vh - {{ $cropContext ? '260' : '220' }}px);min-height:460px;" x-data="{sending:false}">

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 scrollbar-thin" id="chatScroll">

        @if($chats->isEmpty())
        <div class="flex flex-col items-center justify-center h-full text-center px-4">
            <div class="w-16 h-16 rounded-2xl bg-brand-gradient grid place-items-center text-white shadow-glow mb-4">
                <i data-lucide="bot" class="w-8 h-8"></i>
            </div>
            <div class="font-bold text-lg">Asisten Tani AI 🌾</div>
            <p class="text-sm text-ink-500 mt-1 max-w-xs">Tanyakan apapun seputar pertanian, saya siap membantu!</p>

            {{-- Quick action chips --}}
            <div class="mt-5 flex flex-wrap justify-center gap-2 max-w-md">
                @foreach([
                    'Cara mengatasi hawar daun padi',
                    'Pupuk terbaik untuk cabai',
                    'Kenapa daun tomat menguning?',
                    'Waktu panen jagung yang tepat',
                    'Cara tanam bawang merah',
                    'Hama apa yang menyerang kedelai?',
                ] as $q)
                    <form method="POST" action="{{ route('chat.send') }}">@csrf
                        <input type="hidden" name="message" value="{{ $q }}">
                        <button type="submit"
                            class="px-3 py-2 rounded-xl border border-ink-200 bg-white hover:bg-brand-50 hover:border-brand-300 text-xs text-ink-700 font-medium transition">
                            {{ $q }}
                        </button>
                    </form>
                @endforeach
            </div>

            @if($cropContext)
            <div class="mt-4">
                <form method="POST" action="{{ route('chat.send') }}">@csrf
                    <input type="hidden" name="message" value="Berikan panduan lengkap budidaya {{ $cropContext }} dari awal hingga panen.">
                    <button type="submit" class="btn-primary text-sm">
                        <i data-lucide="sprout" class="w-4 h-4"></i>
                        Panduan budidaya {{ $cropContext }}
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endif

        @foreach($chats as $c)
            @if($c->role === 'user')
                <div class="flex justify-end gap-2.5">
                    <div class="max-w-[82%] bg-brand-600 text-white px-4 py-3 rounded-2xl rounded-tr-sm text-sm leading-relaxed">
                        {{ $c->message }}
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-ink-100 grid place-items-center text-ink-500 shrink-0 self-end">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                </div>
            @else
                <div class="flex gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 grid place-items-center shrink-0 self-start mt-0.5">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                    </div>
                    <div class="max-w-[82%]">
                        <div class="bg-ink-50 border border-ink-200 px-4 py-3 rounded-2xl rounded-tl-sm text-sm text-ink-800 leading-relaxed ai-message">
                            {!! \App\Helpers\MarkdownHelper::toHtml($c->message) !!}
                        </div>
                        <div class="text-[10px] text-ink-400 mt-1 ml-1">
                            Asisten Tani AI · {{ $c->created_at->isoFormat('HH:mm') }}
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div x-show="sending" x-cloak class="flex gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 grid place-items-center shrink-0">
                <i data-lucide="bot" class="w-4 h-4"></i>
            </div>
            <div class="bg-ink-50 border border-ink-200 px-4 py-3 rounded-2xl rounded-tl-sm">
                <div class="flex gap-1.5 items-center h-4">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-bounce" style="animation-delay:160ms"></span>
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-bounce" style="animation-delay:320ms"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div class="border-t border-ink-200 p-3 sm:p-4">
        <form method="POST" action="{{ route('chat.send') }}"
            @submit="sending=true" class="flex items-end gap-2">@csrf
            <textarea name="message" required maxlength="2000" rows="1"
                class="input flex-1 resize-none overflow-hidden"
                placeholder="Tanyakan seputar pertanian..."
                @input="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"
                @keydown.enter.prevent="if(!$event.shiftKey&&!sending){sending=true;$el.form.submit();}"></textarea>
            <button class="btn-primary w-10 h-10 grid place-items-center shrink-0 self-end" :disabled="sending">
                <template x-if="!sending"><i data-lucide="send" class="w-4 h-4"></i></template>
                <template x-if="sending"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i></template>
            </button>
        </form>
        <p class="text-[10px] text-ink-400 text-center mt-1.5">
            Enter = kirim · Shift+Enter = baris baru · Hanya menjawab topik pertanian
        </p>
    </div>
</div>

<style>
.ai-message p { margin-bottom: 0.5em; }
.ai-message p:last-child { margin-bottom: 0; }
.ai-message strong, .ai-message b { font-weight: 700; }
.ai-message ul, .ai-message ol { padding-left: 1.25rem; margin: 0.4em 0; }
.ai-message ul { list-style: disc; }
.ai-message ol { list-style: decimal; }
.ai-message li { margin-bottom: 0.2em; }
.ai-message table { width:100%; border-collapse:collapse; font-size:0.8em; margin:0.5em 0; }
.ai-message th, .ai-message td { border:1px solid #e2e8f0; padding:4px 8px; }
.ai-message th { background:#f8fafc; font-weight:600; }
.ai-message code { background:#f1f5f9; padding:1px 5px; border-radius:4px; font-size:0.85em; }
</style>

<script>
const sc = document.getElementById('chatScroll');
if (sc) sc.scrollTop = sc.scrollHeight;
</script>
@endsection
