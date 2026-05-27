@extends('layouts.app')
@section('title','Harga Pasar')
@section('content')

<div class="mb-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">Harga Pasar & Rekomendasi</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <p class="text-ink-500 text-sm">Data harga hasil tani terkini</p>
                <span class="flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-brand-50 border border-brand-200 text-brand-700">
                    <i data-lucide="wifi" class="w-3 h-3"></i>
                    @if($isRealtime) Real-time @else Perkiraan @endif
                </span>
                @if($lastUpdated)
                    <span class="text-xs text-ink-400">
                        <i data-lucide="clock" class="w-3 h-3 inline"></i>
                        Terakhir: {{ $lastUpdated }}
                    </span>
                @endif
            </div>
        </div>
        @if(auth()->user()->location)
            <div class="flex items-center gap-1.5 text-sm font-semibold text-ink-700 bg-ink-50 border border-ink-200 px-3 py-1.5 rounded-xl">
                <i data-lucide="map-pin" class="w-4 h-4 text-brand-600"></i>
                {{ auth()->user()->location }}
                <a href="{{ route('profile.settings') }}" class="text-xs text-brand-600 ml-1 hover:underline">Ubah</a>
            </div>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5 mb-5">

    {{-- Trending / Top Movers --}}
    <div class="card p-5">
        <div class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="trending-up" class="w-4 h-4 text-brand-600"></i> Harga Trending
            <span class="text-xs text-ink-400 font-normal">7 hari terakhir</span>
        </div>
        <div class="space-y-2.5">
            @foreach($trending->take(6) as $t)
                @php
                    $emojis = ['Cabai'=>'🌶️','Bawang'=>'🧅','Tomat'=>'🍅','Jagung'=>'🌽','Kentang'=>'🥔','Padi'=>'🌾','Gabah'=>'🌾','Kol'=>'🥬','Wortel'=>'🥕'];
                    $em = '';
                    foreach ($emojis as $k=>$v) { if(str_contains($t->commodity,$k)){$em=$v;break;} }
                    $up = ($t->change_percent ?? 0) >= 0;
                @endphp
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">{{ $em ?: '🌿' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $t->commodity }}</div>
                        <div class="text-xs text-ink-400">Rp {{ number_format($t->price,0,',','.') }}/kg</div>
                    </div>
                    <span class="badge text-xs {{ $up ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} shrink-0">
                        {{ $up ? '▲' : '▼' }} {{ number_format(abs($t->change_percent ?? 0),1) }}%
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main price table --}}
    <div class="lg:col-span-2 card p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="table" class="w-4 h-4 text-brand-600"></i> Harga Pasar Hari Ini
            </div>
            {{-- Region filter --}}
            <form method="GET" class="flex items-center gap-2">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                <select name="region" class="input text-xs py-1.5" onchange="this.form.submit()">
                    <option value="">Semua Wilayah</option>
                    @foreach($regions as $r)
                        <option {{ request('region')===$r?'selected':'' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                    <tr>
                        <th class="pb-2 pr-3">Tanaman/Hasil Tani</th>
                        <th class="pb-2 pr-3">Harga (Rata-rata)</th>
                        <th class="pb-2 pr-3">Perubahan</th>
                        <th class="pb-2">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse($prices as $p)
                        @php
                            $price  = is_object($p) ? ($p->price  ?? 0) : ($p['price']  ?? 0);
                            $unit   = is_object($p) ? ($p->unit   ?? 'kg') : ($p['unit']   ?? 'kg');
                            $comm   = is_object($p) ? ($p->commodity ?? '-') : ($p['commodity'] ?? '-');
                            $change = is_object($p) ? ($p->change_percent ?? 0) : ($p['change_percent'] ?? 0);
                            $ts     = is_object($p) && $p->recorded_at ? \Carbon\Carbon::parse($p->recorded_at)->isoFormat('D MMM HH:mm') : 'Hari ini';
                            $up     = $change >= 0;
                            $emojis2 = ['Cabai'=>'🌶️','Bawang'=>'🧅','Tomat'=>'🍅','Jagung'=>'🌽','Kentang'=>'🥔','Padi'=>'🌾','Gabah'=>'🌾','Kol'=>'🥬'];
                            $em2 = '';
                            foreach ($emojis2 as $k=>$v) { if(str_contains($comm,$k)){$em2=$v;break;} }
                        @endphp
                        <tr class="hover:bg-ink-50 transition">
                            <td class="py-2.5 pr-3">
                                <div class="flex items-center gap-2">
                                    <span>{{ $em2 ?: '🌿' }}</span>
                                    <span class="font-semibold">{{ $comm }}</span>
                                </div>
                            </td>
                            <td class="pr-3 font-bold">Rp {{ number_format($price,0,',','.') }}/{{ $unit }}</td>
                            <td class="pr-3">
                                <span class="font-bold {{ $up ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $up ? '▲' : '▼' }} {{ number_format(abs($change),1) }}%
                                </span>
                            </td>
                            <td class="text-ink-400 text-xs">{{ $ts }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-ink-400">Tidak ada data ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($prices, 'links') && $prices->lastPage() > 1)
        <div class="mt-4 flex items-center justify-between gap-3 flex-wrap border-t border-ink-100 pt-4">
            <div class="text-xs text-ink-500">
                {{ $prices->firstItem() }}–{{ $prices->lastItem() }} dari {{ $prices->total() }} data
            </div>
            <div class="flex items-center gap-1">
                @if($prices->onFirstPage())
                    <span class="px-3 py-1.5 rounded-lg bg-ink-50 text-ink-300 text-sm cursor-not-allowed">←</span>
                @else
                    <a href="{{ $prices->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-ink-200 text-sm hover:bg-ink-50 transition">←</a>
                @endif
                @foreach($prices->getUrlRange(max(1,$prices->currentPage()-2), min($prices->lastPage(),$prices->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm {{ $page==$prices->currentPage() ? 'bg-brand-600 text-white' : 'border border-ink-200 hover:bg-ink-50' }} transition">{{ $page }}</a>
                @endforeach
                @if($prices->hasMorePages())
                    <a href="{{ $prices->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-ink-200 text-sm hover:bg-ink-50 transition">→</a>
                @else
                    <span class="px-3 py-1.5 rounded-lg bg-ink-50 text-ink-300 text-sm cursor-not-allowed">→</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Search bar --}}
<div class="mb-5 flex gap-2">
    <form method="GET" class="flex gap-2 flex-1 max-w-lg">
        @if(request('region'))<input type="hidden" name="region" value="{{ request('region') }}">@endif
        <div class="flex items-center gap-2 bg-white border border-ink-200 rounded-xl px-3 py-2 flex-1">
            <i data-lucide="search" class="w-4 h-4 text-ink-400 shrink-0"></i>
            <input name="search" type="text" class="bg-transparent text-sm flex-1 outline-none"
                placeholder="Cari nama tanaman atau hasil tani..." value="{{ request('search') }}">
        </div>
        <button class="btn-primary text-sm px-4">Cari</button>
        @if(request()->filled('search'))
            <a href="{{ route('market.index') }}" class="btn-outline text-sm px-3">✕</a>
        @endif
    </form>
</div>

{{-- Sell Recommendations (from AI/logic) --}}
@if($sellRecommendations->count())
<div class="card p-5">
    <div class="font-bold mb-4 flex items-center gap-2">
        <i data-lucide="lightbulb" class="w-4 h-4 text-amber-600"></i> Rekomendasi Waktu Jual
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($sellRecommendations as $rec)
            <div class="p-4 rounded-xl border {{ $rec['signal']==='buy' ? 'bg-green-50 border-green-200' : ($rec['signal']==='wait' ? 'bg-amber-50 border-amber-200' : 'bg-ink-50 border-ink-200') }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xl">{{ $rec['emoji'] }}</span>
                    <div class="font-bold text-sm">{{ $rec['commodity'] }}</div>
                    <span class="ml-auto badge text-xs {{ $rec['signal']==='buy' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $rec['signal']==='buy' ? 'Jual Sekarang' : 'Tunggu' }}
                    </span>
                </div>
                <p class="text-xs text-ink-700">{{ $rec['reason'] }}</p>
                @if(!empty($rec['best_days']))
                    <div class="mt-2 text-xs font-semibold text-{{ $rec['signal']==='buy'?'green':'amber' }}-700">
                        Waktu terbaik: {{ $rec['best_days'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="mt-3 text-xs text-ink-400 flex items-center gap-1">
    <i data-lucide="info" class="w-3 h-3"></i>
    Harga bersifat indikatif. @if(!$isRealtime) Data perkiraan — tambahkan API harga real untuk data akurat. @endif
</div>
@endsection
