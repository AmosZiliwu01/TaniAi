@extends('layouts.admin')
@section('title','Moderasi Komunitas')
@section('content')

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Moderasi Komunitas</h1>
        <p class="text-ink-500 mt-1">Tinjau dan kelola konten dari pengguna.</p>
    </div>
    @if($reportedCount > 0)
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
            <i data-lucide="flag" class="w-4 h-4"></i>
            <b>{{ $reportedCount }}</b> postingan dilaporkan
        </div>
    @endif
</div>

{{-- Filter tabs --}}
<div class="flex gap-2 mb-5">
    <a href="{{ route('admin.moderation') }}"
        class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ !request('filter') ? 'bg-brand-gradient text-white shadow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
        Semua ({{ $posts->total() }})
    </a>
    <a href="{{ route('admin.moderation', ['filter'=>'flagged']) }}"
        class="px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-1.5 transition {{ request('filter')==='flagged' ? 'bg-red-500 text-white shadow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
        <i data-lucide="flag" class="w-3.5 h-3.5"></i>
        Dilaporkan ({{ $reportedCount }})
    </a>
</div>

<div class="card overflow-hidden divide-y divide-ink-200">
    @forelse($posts as $p)
        <div class="p-4 sm:p-5" x-data="{expanded:false, warnOpen:false}">
            <div class="flex items-start gap-3">
                {{-- Letter avatar --}}
                @php
                    $grads = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600'];
                    $gi = abs(crc32($p->user->name ?? '?')) % 5;
                @endphp
                <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $grads[$gi] }} grid place-items-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(substr($p->user->name??'U',0,1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold">{{ $p->title }}</span>
                        @if($p->flagged)
                            <span class="badge bg-red-100 text-red-700 text-[10px] flex items-center gap-1">
                                <i data-lucide="flag" class="w-2.5 h-2.5"></i> Dilaporkan
                            </span>
                        @endif
                        <span class="badge badge-slate text-[10px]">{{ $p->category ?? 'Diskusi' }}</span>
                    </div>

                    <div class="text-xs text-ink-500 mt-0.5">
                        <b class="text-ink-700">{{ $p->user->name ?? '-' }}</b> ·
                        {{ $p->created_at->isoFormat('D MMM Y, HH:mm') }} ·
                        {{ $p->comments->count() }} komentar · {{ $p->likes }} suka
                    </div>

                    @if($p->flag_reason)
                        <div class="mt-1.5 inline-flex items-center gap-1.5 text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg px-2.5 py-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            <b>Alasan laporan:</b> {{ $p->flag_reason }}
                        </div>
                    @endif

                    <p class="text-sm text-ink-600 mt-2 leading-relaxed">{{ \Illuminate\Support\Str::limit($p->content, 220) }}</p>

                    @if($p->image_path)
                        <img src="{{ Storage::url($p->image_path) }}"
                            class="mt-2 h-20 rounded-xl object-cover border border-ink-100"
                            loading="lazy" onerror="this.style.display='none'">
                    @endif

                    @if($p->comments->count())
                        <button @click="expanded=!expanded"
                            class="mt-2 text-xs text-brand-600 font-semibold flex items-center gap-1 hover:underline">
                            <i :data-lucide="expanded?'chevron-up':'chevron-down'" class="w-3.5 h-3.5"></i>
                            <span x-text="expanded?'Sembunyikan':'Lihat '+{{ $p->comments->count() }}+' komentar'"></span>
                        </button>
                        <div x-show="expanded" x-cloak class="mt-2 space-y-1.5 border-t border-ink-100 pt-2">
                            @foreach($p->comments->take(5) as $cm)
                                <div class="flex gap-2 text-xs">
                                    <div class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-bold shrink-0">
                                        {{ strtoupper(substr($cm->user->name??'?',0,1)) }}
                                    </div>
                                    <div class="flex-1 bg-ink-50 rounded-lg px-2.5 py-1.5">
                                        <b>{{ $cm->user->name ?? '-' }}</b>: {{ \Illuminate\Support\Str::limit($cm->content, 160) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-col gap-1.5 shrink-0">
                    <button @click="warnOpen=true" title="Beri peringatan"
                        class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 grid place-items-center transition">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.community.destroy', $p) }}">@csrf @method('DELETE')
                        <button onclick="return confirm('Hapus postingan ini? Tidak dapat dibatalkan.')"
                            title="Hapus postingan"
                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 grid place-items-center transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Warn confirm modal (prevents double-click) --}}
            <div x-show="warnOpen" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
                <div @click.outside="warnOpen=false" class="card p-6 w-full max-w-sm">
                    <div class="font-bold mb-2 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                        Kirim Peringatan?
                    </div>
                    <p class="text-sm text-ink-600 mb-4">
                        <b>{{ $p->user->name ?? '-' }}</b> akan mendapat notifikasi peringatan untuk postingan
                        "<em>{{ \Str::limit($p->title,50) }}</em>".
                    </p>
                    <div class="flex gap-2 justify-end">
                        <button @click="warnOpen=false" class="btn-outline text-sm">Batal</button>
                        <form method="POST" action="{{ route('admin.community.warn', $p) }}">@csrf
                            <button class="btn-primary text-sm bg-amber-500 hover:bg-amber-600">
                                <i data-lucide="send" class="w-4 h-4"></i> Kirim Peringatan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="py-16 text-center text-ink-500">
            <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-brand-500"></i>
            <div class="font-semibold">
                @if(request('filter')==='flagged') Tidak ada postingan dilaporkan
                @else Belum ada postingan
                @endif
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
