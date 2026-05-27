@extends('layouts.app')
@section('title','Komunitas Petani')
@section('content')

<div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Komunitas Petani</h1>
        <p class="text-ink-500 mt-1 text-sm">Berbagi pengalaman & solusi bersama petani Indonesia.</p>
    </div>
    <form method="GET" class="flex gap-2">
        <div class="flex items-center gap-2 bg-white border border-ink-200 rounded-xl px-3 py-2 w-56">
            <i data-lucide="search" class="w-4 h-4 text-ink-400 shrink-0"></i>
            <input name="search" type="text" class="bg-transparent text-sm flex-1 outline-none"
                placeholder="Cari diskusi..." value="{{ request('search') }}">
        </div>
        <button class="btn-primary px-3"><i data-lucide="search" class="w-4 h-4"></i></button>
    </form>
</div>

{{-- Category tabs (like screenshot) --}}
<div class="flex gap-2 mb-5 flex-wrap">
    @foreach(['Semua','Diskusi','Pertanyaan','Berita','Tips'] as $cat)
        <a href="{{ route('community.index', array_merge(request()->except('category','page'), ['category'=>$cat])) }}"
            class="px-4 py-1.5 rounded-xl text-sm font-semibold transition {{ request('category',$cat==='Semua'?'Semua':request('category','Semua'))===$cat||($cat==='Semua'&&!request('category')) ? 'bg-brand-gradient text-white shadow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
            {{ $cat }}
        </a>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-5">
<div class="lg:col-span-2 space-y-4">

    {{-- Create post button --}}
    <div class="card p-4" x-data="{open:false,imgPrev:null}">
        <button @click="open=!open"
            class="w-full flex items-center gap-3 text-left">
            @php
                $avatarColors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600'];
                $ac = crc32(auth()->user()->name) % 5;
            @endphp
            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $avatarColors[$ac] }} grid place-items-center text-white font-bold text-sm shrink-0">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>
            <div class="flex-1 bg-ink-50 border border-ink-200 rounded-xl px-4 py-2.5 text-ink-400 text-sm hover:bg-ink-100 transition">
                Bagikan pengalaman atau ajukan pertanyaan...
            </div>
        </button>

        <div x-show="open" x-cloak x-transition class="mt-4">
            <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data" class="space-y-3">@csrf
                <input name="title" required maxlength="255" class="input" placeholder="Judul postingan...">

                <textarea name="content" required rows="3" maxlength="5000" class="input"
                    placeholder="Jelaskan pertanyaan atau pengalaman Anda secara detail..."></textarea>

                {{-- Image upload --}}
                <div>
                    <label class="flex items-center gap-2 cursor-pointer border border-dashed border-ink-300 rounded-xl px-4 py-2.5 hover:border-brand-400 hover:bg-brand-50/20 transition text-sm text-ink-500">
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden"
                            @change="const f=$event.target.files[0];if(f){const r=new FileReader();r.onload=e=>imgPrev=e.target.result;r.readAsDataURL(f)}">
                        <i data-lucide="image" class="w-4 h-4 text-ink-400"></i>
                        <span>Lampirkan foto (opsional · maks 5MB)</span>
                        <span x-show="imgPrev" class="text-brand-600 font-semibold ml-auto text-xs">✓ Foto dipilih</span>
                    </label>
                    <template x-if="imgPrev">
                        <div class="mt-2 relative inline-block">
                            <img :src="imgPrev" class="max-h-32 rounded-xl object-cover">
                            <button type="button" @click="imgPrev=null"
                                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/50 text-white grid place-items-center">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div class="flex gap-2">
                        @foreach(['Diskusi','Pertanyaan','Tips','Berita'] as $cat)
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="{{ $cat }}" class="hidden peer" {{ $cat==='Diskusi'?'checked':'' }}>
                                <span class="peer-checked:bg-brand-600 peer-checked:text-white px-3 py-1.5 rounded-lg text-xs font-semibold bg-ink-100 text-ink-600 transition">{{ $cat }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="open=false" class="btn-outline text-sm">Batal</button>
                        <button class="btn-primary text-sm"><i data-lucide="send" class="w-4 h-4"></i> Posting</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Posts --}}
    @forelse($posts as $p)
    @php
        $ownerColors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600'];
        $pc = crc32($p->user->name ?? '?') % 5;
        $isOwner = $p->user_id === auth()->id();
        $userLiked = \Illuminate\Support\Facades\Cache::has("like_{$p->id}_u".auth()->id());
    @endphp
    <div class="card p-5" x-data="{
        cmtOpen: {{ $p->comments->count() > 0 ? 'true' : 'false' }},
        reportOpen: false,
        editOpen: false,
        replyTo: null,
        likes: {{ $p->likes }},
        liked: {{ $userLiked ? 'true' : 'false' }}
    }">
        {{-- Post header --}}
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $ownerColors[$pc] }} grid place-items-center text-white font-bold text-sm shrink-0">
                {{ strtoupper(substr($p->user->name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-ink-900">{{ $p->user->name ?? 'Anonim' }}</span>
                    <span class="badge badge-slate text-[10px]">{{ $p->category ?? 'Diskusi' }}</span>
                    @if($p->flagged)
                        <span class="badge bg-red-100 text-red-700 text-[10px]">Dilaporkan</span>
                    @endif
                    <span class="text-xs text-ink-400 ml-auto">{{ $p->created_at->diffForHumans() }}</span>
                </div>
                <div class="font-bold mt-0.5">{{ $p->title }}</div>
            </div>

            {{-- Actions dropdown --}}
            <div class="relative shrink-0" x-data="{menuOpen:false}">
                <button @click="menuOpen=!menuOpen" class="p-1.5 rounded-lg hover:bg-ink-50 text-ink-400">
                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                </button>
                <div x-show="menuOpen" x-cloak @click.outside="menuOpen=false"
                    class="absolute right-0 mt-1 w-44 card p-1 z-30 shadow-lg">
                    @if($isOwner)
                        <button @click="editOpen=true;menuOpen=false" class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-ink-50 rounded-lg text-left">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </button>
                        <form method="POST" action="{{ route('community.destroy', $p) }}">@csrf @method('DELETE')
                            <button onclick="return confirm('Hapus postingan ini?')"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-red-50 text-red-600 rounded-lg text-left">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                            </button>
                        </form>
                    @else
                        <button @click="reportOpen=true;menuOpen=false"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-red-50 text-red-600 rounded-lg text-left">
                            <i data-lucide="flag" class="w-3.5 h-3.5"></i> Laporkan
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <p class="text-sm text-ink-700 mt-2 leading-relaxed">{{ $p->content }}</p>

        {{-- Post image --}}
        @if($p->image_path)
            <div class="mt-3">
                <img src="{{ Storage::url($p->image_path) }}"
                    alt="Foto postingan"
                    class="w-full max-h-72 object-cover rounded-2xl cursor-pointer border border-ink-100"
                    loading="lazy"
                    onerror="this.parentElement.style.display='none'"
                    onclick="this.style.maxHeight=this.style.maxHeight==='none'?'18rem':'none'">
            </div>
        @endif

        {{-- Action bar --}}
        <div class="mt-3 flex items-center gap-4 text-sm">
            {{-- Like toggle (AJAX) --}}
            <button
                @click="toggleLike({{ $p->id }})"
                class="flex items-center gap-1.5 font-semibold transition"
                :class="liked ? 'text-red-500' : 'text-ink-500 hover:text-red-400'">
                <svg class="w-4 h-4 transition" :style="liked?'fill:currentColor':'fill:none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span x-text="likes"></span>
            </button>

            <button @click="cmtOpen=!cmtOpen"
                class="flex items-center gap-1.5 text-ink-500 hover:text-brand-600 font-semibold transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>{{ $p->comments->count() }}</span>
            </button>
        </div>

        {{-- Comments --}}
        <div x-show="cmtOpen" class="mt-3 border-t border-ink-100 pt-3 space-y-3">
            @foreach($p->comments as $cm)
                @php $cac = crc32($cm->user->name ?? '?') % 5; @endphp
                <div class="flex gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $ownerColors[$cac] }} grid place-items-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr($cm->user->name??'?',0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="bg-ink-50 rounded-2xl rounded-tl-sm px-3 py-2">
                            <span class="font-semibold text-xs text-ink-800">{{ $cm->user->name ?? 'Anonim' }}</span>
                            <p class="text-sm text-ink-700 mt-0.5 leading-relaxed">{{ $cm->content }}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-1 px-1">
                            <span class="text-[11px] text-ink-400">{{ $cm->created_at->diffForHumans() }}</span>
                            <button class="text-[11px] text-brand-600 font-semibold hover:underline"
                                @click="replyTo = (replyTo===`{{ $p->id }}_{{ $cm->id }}` ? null : `{{ $p->id }}_{{ $cm->id }}`)">
                                Balas
                            </button>
                        </div>

                        {{-- Reply form (one at a time) --}}
                        <form x-show="replyTo===`{{ $p->id }}_{{ $cm->id }}`" x-cloak
                            method="POST" action="{{ route('community.comment', $p) }}"
                            class="mt-2 flex gap-2">@csrf
                            <input type="hidden" name="parent_id" value="{{ $cm->id }}">
                            <input name="content" required maxlength="1000"
                                class="input flex-1 text-sm py-1.5"
                                :placeholder="`@{{ $cm->user->name ?? 'pengguna' }} `"
                                x-ref="replyInput_{{ $p->id }}_{{ $cm->id }}"
                                x-init="$watch('replyTo', v => v===`{{ $p->id }}_{{ $cm->id }}` && $nextTick(()=>$refs['replyInput_{{ $p->id }}_{{ $cm->id }}']?.focus()))">
                            <button class="btn-primary text-xs px-3 py-1.5">Kirim</button>
                        </form>

                        {{-- Nested replies --}}
                        @if($cm->replies && $cm->replies->count())
                            <div class="mt-2 space-y-2 pl-2 border-l-2 border-brand-100">
                                @foreach($cm->replies as $r)
                                    @php $rac = crc32($r->user->name ?? '?') % 5; @endphp
                                    <div class="flex gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br {{ $ownerColors[$rac] }} grid place-items-center text-white text-[10px] font-bold shrink-0">
                                            {{ strtoupper(substr($r->user->name??'?',0,1)) }}
                                        </div>
                                        <div class="flex-1 bg-brand-50/60 rounded-xl px-2.5 py-2">
                                            <span class="font-semibold text-xs text-brand-800">{{ $r->user->name ?? 'Anonim' }}</span>
                                            <p class="text-xs text-ink-700 mt-0.5">{{ $r->content }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- New comment --}}
            <form method="POST" action="{{ route('community.comment', $p) }}" class="flex gap-2">@csrf
                <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $avatarColors[$ac] }} grid place-items-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                </div>
                <input name="content" required maxlength="1000" class="input flex-1 text-sm" placeholder="Tulis komentar...">
                <button class="btn-outline px-3 text-sm"><i data-lucide="send" class="w-4 h-4"></i></button>
            </form>
        </div>

        {{-- Report Modal --}}
        <div x-show="reportOpen" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4" @keydown.escape.window="reportOpen=false">
            <div @click.outside="reportOpen=false" class="card p-6 w-full max-w-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-bold">Laporkan Postingan</div>
                    <button @click="reportOpen=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
                </div>
                <form method="POST" action="{{ route('community.report', $p) }}">@csrf
                    <div class="mb-4">
                        <label class="label text-xs">Pilih alasan laporan</label>
                        <div class="space-y-1.5">
                            @foreach(['Spam atau iklan','Hoax / Informasi menyesatkan','Penipuan','Ujaran kebencian','Konten tidak pantas','Lainnya'] as $reason)
                                <label class="flex items-center gap-2 cursor-pointer p-2.5 rounded-xl border border-ink-200 hover:bg-ink-50 transition">
                                    <input type="radio" name="reason" value="{{ $reason }}" required class="text-brand-600">
                                    <span class="text-sm">{{ $reason }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="reportOpen=false" class="btn-outline text-sm">Batal</button>
                        <button class="btn-primary text-sm bg-red-500 hover:bg-red-600">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        @if($isOwner)
        <div x-show="editOpen" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
            <div @click.outside="editOpen=false" class="card p-6 w-full max-w-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-bold">Edit Postingan</div>
                    <button @click="editOpen=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
                </div>
                <form method="POST" action="{{ route('community.update', $p) }}" class="space-y-3">@csrf @method('PATCH')
                    <input name="title" required class="input" value="{{ $p->title }}">
                    <textarea name="content" required rows="4" class="input">{{ $p->content }}</textarea>
                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="editOpen=false" class="btn-outline text-sm">Batal</button>
                        <button class="btn-primary text-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    @empty
        <div class="card p-14 text-center text-ink-500">
            <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3 text-ink-300"></i>
            <div class="font-semibold">Belum ada postingan</div>
            <p class="text-sm mt-1">Jadilah yang pertama berbagi di komunitas!</p>
        </div>
    @endforelse

    <div>{{ $posts->links() }}</div>
</div>

{{-- Sidebar --}}
<div class="space-y-4">
    <div class="card p-5">
        <div class="font-bold mb-3">Kontributor Aktif</div>
        @foreach($activeUsers as $u)
            @php $uc = crc32($u->name) % 5; @endphp
            <div class="flex items-center gap-3 py-2">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $avatarColors[$uc] }} grid place-items-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(substr($u->name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">{{ $u->name }}</div>
                    <div class="text-xs text-ink-400">{{ $u->posts_count }} postingan</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card p-5">
        <div class="font-bold mb-3">Topik Populer</div>
        @foreach(['cara-tanam-padi','hama-wereng','pupuk-cabai','harga-bawang','cuaca-musim'] as $t)
            <a href="{{ route('community.index',['search'=>str_replace('-',' ',$t)]) }}"
                class="block py-2 border-b border-ink-100 last:border-0 text-sm text-brand-700 font-semibold hover:underline">
                #{{ $t }}
            </a>
        @endforeach
    </div>
</div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function toggleLike(postId) {
    try {
        const r = await fetch(`/community/${postId}/like`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF, 'Accept':'application/json'}
        });
        const d = await r.json();
        // Alpine updates via x-data binding in each post component
        return d;
    } catch(e) {
        console.error(e);
    }
}

// Patch: each post calls its own toggleLike through Alpine
// Override in component:
document.querySelectorAll('[x-data]').forEach(el => {
    if (!el.__x) return;
    const data = el.__x.$data;
    if (typeof data.likes !== 'undefined') {
        data.toggleLike = async function(postId) {
            const d = await window.toggleLike(postId);
            if (d) { this.liked = d.liked; this.likes = d.count; }
        };
    }
});
</script>
@endsection
