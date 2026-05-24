@extends('layouts.admin')
@section('title','Moderasi Komunitas')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Moderasi Komunitas</h1>
        <p class="text-ink-500 mt-1">Tinjau dan kelola konten dari petani.</p>
    </div>
    @if($reportedCount > 0)
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
            <i data-lucide="flag" class="w-4 h-4"></i>
            <span><b>{{ $reportedCount }}</b> postingan dilaporkan</span>
        </div>
    @endif
</div>

@if(session('status'))
    <div class="mb-4 p-3 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('status') }}
    </div>
@endif

{{-- Filter Tabs --}}
<div class="flex gap-2 mb-5">
    <a href="{{ route('admin.moderation') }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold {{ !request('filter') ? 'bg-brand-gradient text-white' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
        Semua ({{ $posts->total() }})
    </a>
    <a href="{{ route('admin.moderation') }}?filter=flagged"
       class="px-4 py-2 rounded-xl text-sm font-semibold {{ request('filter')==='flagged' ? 'bg-red-500 text-white' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
        <i data-lucide="flag" class="w-3.5 h-3.5 inline -mt-0.5"></i> Dilaporkan ({{ $reportedCount }})
    </a>
</div>

<div class="card p-2 divide-y divide-ink-200">
    @forelse($posts as $p)
        <div class="p-4" x-data="{ expanded: false }">
            <div class="flex items-start gap-3">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(substr($p->user->name ?? 'U', 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold">{{ $p->title }}</span>
                        @if(!empty($p->flagged))
                            <span class="badge bg-red-100 text-red-700 text-[10px]">Dilaporkan</span>
                        @endif
                        <span class="badge badge-slate text-[10px]">{{ $p->category ?? 'Diskusi' }}</span>
                    </div>
                    <div class="text-xs text-ink-500 mt-0.5">
                        <b>{{ $p->user->name ?? '-' }}</b> ·
                        {{ $p->created_at->isoFormat('D MMM Y, HH:mm') }} ·
                        {{ $p->comments->count() }} komentar · {{ $p->likes }} suka
                    </div>
                    <p class="text-sm text-ink-600 mt-2 leading-relaxed">{{ \Illuminate\Support\Str::limit($p->content, 200) }}</p>

                    @if($p->image_path)
                        <img src="{{ Storage::url($p->image_path) }}" class="mt-2 h-20 rounded-lg object-cover">
                    @endif

                    {{-- Expandable comments --}}
                    @if($p->comments->count())
                        <button @click="expanded=!expanded" class="mt-2 text-xs text-brand-600 font-semibold flex items-center gap-1">
                            <i :data-lucide="expanded ? 'chevron-up' : 'chevron-down'" class="w-3.5 h-3.5"></i>
                            <span x-text="expanded ? 'Sembunyikan komentar' : 'Lihat ' + {{ $p->comments->count() }} + ' komentar'"></span>
                        </button>
                        <div x-show="expanded" x-cloak class="mt-3 space-y-2 border-t border-ink-100 pt-3">
                            @foreach($p->comments->take(5) as $cm)
                                <div class="flex gap-2 text-xs">
                                    <div class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-bold shrink-0">{{ strtoupper(substr($cm->user->name??'?',0,1)) }}</div>
                                    <div class="flex-1 bg-ink-50 rounded-lg px-2.5 py-1.5">
                                        <b>{{ $cm->user->name ?? '-' }}</b>: {{ \Illuminate\Support\Str::limit($cm->content, 150) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col gap-1.5 shrink-0">
                    <form method="POST" action="{{ route('admin.community.warn', $p) }}">@csrf
                        <button title="Beri peringatan" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 grid place-items-center transition">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.community.destroy', $p) }}">@csrf @method('DELETE')
                        <button title="Hapus postingan" onclick="return confirm('Hapus postingan ini?')"
                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 grid place-items-center transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="py-16 text-center text-ink-500">
            <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-brand-500"></i>
            <div class="font-semibold">Tidak ada postingan yang perlu dimoderasi</div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
