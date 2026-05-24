@extends('layouts.app')
@section('title','Komunitas Petani')
@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Komunitas Petani</h1>
        <p class="text-ink-500 mt-1">Berbagi pengalaman & solusi bersama petani lain.</p>
    </div>
    {{-- Search Bar --}}
    <form method="GET" class="flex gap-2 w-full sm:w-auto">
        <div class="flex items-center gap-2 bg-ink-50 border border-ink-200 rounded-xl px-3 py-2 flex-1 sm:w-64">
            <i data-lucide="search" class="w-4 h-4 text-ink-500 shrink-0"></i>
            <input name="search" type="text" class="bg-transparent text-sm flex-1 outline-none" placeholder="Cari postingan..." value="{{ request('search') }}">
        </div>
        <select name="category" class="input text-sm" onchange="this.form.submit()">
            <option value="Semua" {{ request('category','Semua')==='Semua'?'selected':'' }}>Semua</option>
            @foreach(['Diskusi','Pertanyaan','Tips','Berita'] as $cat)
                <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button class="btn-primary px-3 py-2 text-sm"><i data-lucide="search" class="w-4 h-4"></i></button>
    </form>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">

        {{-- Form Buat Postingan --}}
        <div class="card p-5" x-data="{ open: false, customCrop: false, imagePreview: null }">
            <button @click="open = !open" class="w-full flex items-center gap-3 text-left" :class="open ? 'mb-4' : ''">
                <div class="w-10 h-10 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                </div>
                <div class="flex-1 bg-ink-50 rounded-xl px-4 py-2.5 text-ink-500 text-sm hover:bg-ink-100 transition">
                    Bagikan pengalaman atau tanyakan sesuatu...
                </div>
            </button>

            <div x-show="open" x-cloak>
                <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data" class="space-y-3">@csrf

                    <input name="title" required class="input" placeholder="Judul postingan...">

                    {{-- Tag tanaman --}}
                    <div x-data="{ customCropInner: false }">
                        <label class="label text-xs">Tag Tanaman (opsional)</label>
                        <div class="flex gap-2 mb-1.5">
                            <button type="button" @click="customCropInner=false"
                                :class="!customCropInner?'bg-brand-100 text-brand-700':'bg-ink-100 text-ink-500'"
                                class="text-xs px-2.5 py-1 rounded-lg font-medium transition">Pilih</button>
                            <button type="button" @click="customCropInner=true"
                                :class="customCropInner?'bg-brand-100 text-brand-700':'bg-ink-100 text-ink-500'"
                                class="text-xs px-2.5 py-1 rounded-lg font-medium transition">Ketik</button>
                        </div>
                        <template x-if="!customCropInner">
                            <select name="crop_tag" class="input text-sm">
                                <option value="">-- Tidak ada --</option>
                                @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao','Lainnya'] as $c)
                                    <option>{{ $c }}</option>
                                @endforeach
                            </select>
                        </template>
                        <template x-if="customCropInner">
                            <input name="crop_tag" type="text" class="input text-sm" placeholder="Contoh: Singkong, Pepaya...">
                        </template>
                    </div>

                    <textarea name="content" required rows="3" class="input" placeholder="Jelaskan pertanyaan atau pengalaman Anda..."></textarea>

                    {{-- Upload gambar --}}
                    <div>
                        <label class="label text-xs">Foto (opsional)</label>
                        <label class="flex items-center gap-2 cursor-pointer border border-dashed border-ink-300 rounded-xl px-4 py-3 hover:border-brand-400 hover:bg-brand-50/30 transition">
                            <input type="file" name="image" accept="image/*" class="hidden"
                                @change="const f=$event.target.files[0]; if(f){const r=new FileReader(); r.onload=e=>imagePreview=e.target.result; r.readAsDataURL(f)}">
                            <i data-lucide="image" class="w-4 h-4 text-ink-500"></i>
                            <span class="text-sm text-ink-600">Upload foto (maks 5MB)</span>
                            <span x-show="imagePreview" class="text-xs text-brand-600 font-semibold ml-auto">✓ Dipilih</span>
                        </label>
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="mt-2 max-h-32 rounded-xl object-cover">
                        </template>
                    </div>

                    <div class="flex justify-between items-center">
                        <select name="category" class="input max-w-[160px] text-sm">
                            <option>Diskusi</option><option>Pertanyaan</option><option>Tips</option><option>Berita</option>
                        </select>
                        <button class="btn-primary">
                            <i data-lucide="send" class="w-4 h-4"></i> Posting
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($posts->isEmpty())
            <div class="card p-10 text-center text-ink-500">
                <i data-lucide="message-square" class="w-10 h-10 mx-auto mb-3 text-ink-300"></i>
                <div>Belum ada postingan. Jadilah yang pertama!</div>
            </div>
        @endif

        @foreach($posts as $p)
            <div class="card p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-gradient grid place-items-center text-white font-bold">{{ substr($p->user->name,0,1) }}</div>
                    <div class="flex-1">
                        <div class="font-bold">{{ $p->user->name }}</div>
                        <div class="text-xs text-ink-500">{{ $p->created_at->diffForHumans() }} · <span class="bg-ink-100 text-ink-600 px-1.5 py-0.5 rounded-md">{{ $p->category }}</span></div>
                    </div>
                </div>
                <div class="mt-3 font-bold">{{ $p->title }}</div>
                <p class="text-sm text-ink-600 mt-1">{{ $p->content }}</p>

                @if($p->image_path)
                    <img src="{{ Storage::url($p->image_path) }}" class="mt-3 rounded-xl max-h-64 w-full object-cover cursor-pointer"
                        onclick="this.style.maxHeight = this.style.maxHeight==='none' ? '16rem' : 'none'">
                @endif

                <div class="mt-3 flex items-center gap-4 text-sm text-ink-500">
                    <form method="POST" action="{{ route('community.like',$p) }}">@csrf
                        <button class="flex items-center gap-1 hover:text-red-500 transition">
                            <i data-lucide="heart" class="w-4 h-4"></i> {{ $p->likes }}
                        </button>
                    </form>
                    <span class="flex items-center gap-1">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> {{ $p->comments->count() }}
                    </span>
                </div>

                {{-- Komentar (nested) --}}
                @if($p->comments->count())
                    <div class="mt-4 space-y-2 border-t border-ink-200 pt-3">
                        @foreach($p->comments->where('parent_id', null) as $cm)
                            <div class="flex gap-2 text-sm">
                                <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-xs font-bold shrink-0">{{ substr($cm->user->name,0,1) }}</div>
                                <div class="flex-1">
                                    <div class="bg-ink-50 rounded-xl px-3 py-2">
                                        <b class="text-ink-800">{{ $cm->user->name }}</b>
                                        <div class="text-ink-600 mt-0.5">{{ $cm->content }}</div>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 px-1">
                                        <span class="text-[11px] text-ink-400">{{ $cm->created_at->diffForHumans() }}</span>
                                        <button
                                            class="text-[11px] text-brand-600 font-semibold hover:underline"
                                            @click="$refs.replyInput{{ $p->id }}_{{ $cm->id }}.classList.toggle('hidden')">
                                            Balas
                                        </button>
                                    </div>

                                    {{-- Reply form --}}
                                    <form method="POST" action="{{ route('community.comment',$p) }}"
                                        class="hidden mt-2 flex gap-2" x-ref="replyInput{{ $p->id }}_{{ $cm->id }}">@csrf
                                        <input type="hidden" name="parent_id" value="{{ $cm->id }}">
                                        <input name="content" required class="input flex-1 text-sm py-1.5"
                                            placeholder="@{{ $cm->user->name }} ...">
                                        <button class="btn-primary text-sm py-1.5 px-3">Balas</button>
                                    </form>

                                    {{-- Replies --}}
                                    @foreach($p->comments->where('parent_id', $cm->id) as $reply)
                                        <div class="flex gap-2 mt-2 ml-4 text-sm">
                                            <div class="w-6 h-6 rounded-full bg-brand-50 text-brand-700 grid place-items-center text-xs font-bold shrink-0">{{ substr($reply->user->name,0,1) }}</div>
                                            <div class="bg-brand-50 rounded-xl px-3 py-2 flex-1">
                                                <b class="text-brand-800">@{{ $reply->user->name }}</b>
                                                <div class="text-ink-600 mt-0.5">{{ $reply->content }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Form komentar baru --}}
                <form method="POST" action="{{ route('community.comment',$p) }}" class="mt-3 flex gap-2">@csrf
                    <div class="w-7 h-7 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <input name="content" required class="input flex-1 text-sm" placeholder="Tulis komentar...">
                    <button class="btn-outline text-sm"><i data-lucide="send" class="w-4 h-4"></i></button>
                </form>
            </div>
        @endforeach

        <div>{{ $posts->links() }}</div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <div class="font-bold mb-3 flex items-center gap-2">
                <i data-lucide="trending-up" class="w-4 h-4 text-brand-600"></i> Trending
            </div>
            @foreach(['cara-mengatasi-wereng','pupuk-organik-terbaik','harga-gabah-terkini','tips-tanam-cabai','jadwal-panen'] as $t)
                <div class="py-2 border-b border-ink-200 last:border-0 text-sm">
                    <span class="text-brand-600 font-semibold">#{{ $t }}</span>
                </div>
            @endforeach
        </div>

        <div class="card p-5">
            <div class="font-bold mb-3 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-brand-600"></i> Kontributor Aktif
            </div>
            @foreach($activeUsers as $u)
                <div class="flex items-center gap-3 py-2">
                    <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm">{{ $u->name }}</div>
                        <div class="text-xs text-ink-500">{{ $u->posts->count() }} postingan</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
