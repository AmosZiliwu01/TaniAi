@extends('layouts.app')
@section('title','Komunitas Petani')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Komunitas Petani</h1>
        <p class="text-ink-500 mt-1">Berbagi pengalaman & solusi bersama petani lain.</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="card p-5">
            <form method="POST" action="{{ route('community.store') }}" class="space-y-3">@csrf
                <input name="title" required class="input" placeholder="Judul diskusi...">
                <textarea name="content" required rows="3" class="input" placeholder="Apa yang ingin Anda diskusikan?"></textarea>
                <div class="flex justify-between items-center">
                    <select name="category" class="input max-w-xs">
                        <option>Diskusi</option><option>Pertanyaan</option><option>Tips</option><option>Berita</option>
                    </select>
                    <button class="btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Posting</button>
                </div>
            </form>
        </div>

        @foreach($posts as $p)
            <div class="card p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-gradient grid place-items-center text-white font-bold">{{ substr($p->user->name,0,1) }}</div>
                    <div class="flex-1">
                        <div class="font-bold">{{ $p->user->name }}</div>
                        <div class="text-xs text-ink-500">{{ $p->created_at->diffForHumans() }} · {{ $p->category }}</div>
                    </div>
                </div>
                <div class="mt-3 font-bold">{{ $p->title }}</div>
                <p class="text-sm text-ink-600 mt-1">{{ $p->content }}</p>
                <div class="mt-3 flex items-center gap-4 text-sm text-ink-500">
                    <form method="POST" action="{{ route('community.like',$p) }}">@csrf
                        <button class="flex items-center gap-1 hover:text-brand-700"><i data-lucide="heart" class="w-4 h-4"></i> {{ $p->likes }}</button>
                    </form>
                    <span class="flex items-center gap-1"><i data-lucide="message-circle" class="w-4 h-4"></i> {{ $p->comments->count() }}</span>
                </div>
                @if($p->comments->count())
                    <div class="mt-4 space-y-2 border-t border-ink-200 pt-3">
                        @foreach($p->comments as $cm)
                            <div class="flex gap-2 text-sm">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-xs font-bold">{{ substr($cm->user->name,0,1) }}</div>
                                <div class="flex-1 bg-ink-50 rounded-xl px-3 py-2">
                                    <b>{{ $cm->user->name }}</b>
                                    <div class="text-ink-600">{{ $cm->content }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('community.comment',$p) }}" class="mt-3 flex gap-2">@csrf
                    <input name="content" required class="input flex-1" placeholder="Tulis komentar...">
                    <button class="btn-outline"><i data-lucide="send" class="w-4 h-4"></i></button>
                </form>
            </div>
        @endforeach

        <div>{{ $posts->links() }}</div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <div class="font-bold mb-3">Trending</div>
            @foreach(['Cara mengatasi wereng','Pupuk organik terbaik','Harga gabah terkini'] as $t)
                <div class="py-2 border-b border-ink-200 last:border-0 text-sm">#{{ str_replace(' ','',$t) }}<div class="text-xs text-ink-500">{{ $t }}</div></div>
            @endforeach
        </div>
        <div class="card p-5">
            <div class="font-bold mb-3">Petani Aktif</div>
            @foreach(['Budi','Siti','Ahmad','Dewi'] as $n)
                <div class="flex items-center gap-3 py-2">
                    <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm">{{ $n[0] }}</div>
                    <div class="flex-1"><div class="font-semibold text-sm">{{ $n }}</div><div class="text-xs text-ink-500">12 postingan</div></div>
                    <button class="btn-outline text-xs py-1 px-3">Ikuti</button>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
