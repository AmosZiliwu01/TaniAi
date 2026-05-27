@extends('layouts.app')
@section('title','Beranda')
@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">
            Selamat datang, {{ explode(' ', $user->name)[0] }} 🌱
        </h1>
        <p class="text-ink-500 mt-1 text-sm">
            {{ $user->location ? 'Lokasi: '.$user->location : 'Belum ada lokasi — ' }}
            @if(!$user->location)
                <a href="{{ route('profile.settings') }}" class="text-brand-600 font-semibold underline">atur lokasi</a>
            @endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('diagnosis.index') }}" class="btn-primary text-sm">
            <i data-lucide="scan-line" class="w-4 h-4"></i> Diagnosa Cepat
        </a>
        <a href="{{ route('chat.index') }}" class="btn-outline text-sm">
            <i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI
        </a>
    </div>
</div>

{{-- Stats Row (real data only) --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    {{-- Diagnosa count --}}
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">Diagnosa Saya</div>
            <div class="w-9 h-9 rounded-lg bg-brand-100 text-brand-700 grid place-items-center">
                <i data-lucide="scan-line" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="text-2xl font-extrabold">{{ $stats['diagnoses'] }}</div>
        <div class="text-xs text-ink-400 mt-0.5">total diagnosa</div>
    </div>

    {{-- Kesehatan Tanaman — only real data --}}
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide flex items-center gap-1">
                Kesehatan Tanaman
                @if($stats['health']['has_data'])
                    <span title="Berdasarkan hasil diagnosa terakhir" class="text-ink-300 cursor-help">
                        <i data-lucide="info" class="w-3 h-3"></i>
                    </span>
                @endif
            </div>
            <div class="w-9 h-9 rounded-lg bg-green-100 text-green-700 grid place-items-center">
                <i data-lucide="heart-pulse" class="w-4 h-4"></i>
            </div>
        </div>
        @if($stats['health']['has_data'])
            <div class="text-2xl font-extrabold {{ $stats['health']['rate'] >= 70 ? 'text-green-700' : ($stats['health']['rate'] >= 45 ? 'text-amber-600' : 'text-red-600') }}">
                {{ $stats['health']['rate'] }}%
            </div>
            <div class="text-xs text-ink-400 mt-0.5">{{ $stats['health']['label'] }} · per {{ $stats['health']['last_date'] }}</div>
        @else
            <div class="text-lg font-bold text-ink-300">—</div>
            <a href="{{ route('diagnosis.index') }}" class="text-xs text-brand-600 font-semibold mt-0.5 block hover:underline">Mulai diagnosa →</a>
        @endif
    </div>

    {{-- Peringatan Aktif --}}
    <div class="stat-card {{ count($stats['alerts']) > 0 ? 'border-amber-200 bg-amber-50/50' : '' }}">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">Peringatan</div>
            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 grid place-items-center">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="text-2xl font-extrabold {{ count($stats['alerts']) > 0 ? 'text-amber-600' : 'text-ink-800' }}">
            {{ count($stats['alerts']) }}
        </div>
        <div class="text-xs text-ink-400 mt-0.5">{{ count($stats['alerts']) > 0 ? 'perlu perhatian' : 'tidak ada peringatan' }}</div>
    </div>

    {{-- Prediksi Panen — only real data --}}
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">Prediksi Panen</div>
            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 grid place-items-center">
                <i data-lucide="scissors" class="w-4 h-4"></i>
            </div>
        </div>
        @if($stats['harvest']['has_data'])
            <div class="text-lg font-extrabold text-blue-700 leading-tight">{{ $stats['harvest']['days_left'] }}<span class="text-sm font-normal text-ink-500"> hari</span></div>
            <div class="text-xs text-ink-400 mt-0.5 truncate">{{ $stats['harvest']['crop'] }} · {{ $stats['harvest']['field'] }}</div>
        @else
            <div class="text-lg font-bold text-ink-300">—</div>
            <a href="{{ route('records.index') }}" class="text-xs text-brand-600 font-semibold mt-0.5 block hover:underline">Catat lahan →</a>
        @endif
    </div>
</div>

{{-- Active Alerts section --}}
@if(count($stats['alerts']) > 0)
<div class="card p-5 mb-5">
    <div class="font-bold mb-3 flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i> Peringatan Aktif
    </div>
    <div class="space-y-2">
        @foreach($stats['alerts'] as $alert)
            @php
                $colors = [
                    'danger'  => ['bg-red-50 border-red-200', 'text-red-600', 'bg-red-100 text-red-700'],
                    'warning' => ['bg-amber-50 border-amber-200', 'text-amber-600', 'bg-amber-100 text-amber-700'],
                    'info'    => ['bg-blue-50 border-blue-200', 'text-blue-600', 'bg-blue-100 text-blue-700'],
                    'success' => ['bg-green-50 border-green-200', 'text-green-600', 'bg-green-100 text-green-700'],
                ];
                [$cardCls, $iconCls, $badgeCls] = $colors[$alert['type']] ?? $colors['info'];
            @endphp
            <a href="{{ $alert['action'] ?? '#' }}" class="flex items-start gap-3 p-3 rounded-xl border {{ $cardCls }} hover:opacity-90 transition">
                <div class="w-8 h-8 rounded-lg {{ $badgeCls }} grid place-items-center shrink-0 mt-0.5">
                    <i data-lucide="{{ $alert['icon'] }}" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm">{{ $alert['title'] }}</div>
                    <div class="text-xs text-ink-600 mt-0.5">{{ $alert['desc'] }}</div>
                </div>
                <div class="text-xs text-ink-400 shrink-0 mt-0.5">{{ $alert['time'] }}</div>
            </a>
        @endforeach
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-4 mb-5">
    {{-- Recent Diagnoses --}}
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="scan-line" class="w-4 h-4 text-brand-600"></i> Diagnosa Terbaru
            </div>
            <a href="{{ route('diagnosis.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse($recent_diagnoses as $d)
                <a href="{{ route('diagnosis.show', $d) }}"
                    class="flex items-center gap-3 py-3 hover:bg-ink-50 rounded-xl px-2 -mx-2 transition">
                    @if($d->image_path)
                        <img src="{{ Storage::url($d->image_path) }}"
                            class="w-11 h-11 rounded-xl object-cover shrink-0 border border-ink-100"
                            loading="lazy"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                        <div style="display:none" class="w-11 h-11 rounded-xl bg-brand-100 text-brand-700 place-items-center shrink-0">
                            <i data-lucide="leaf" class="w-5 h-5"></i>
                        </div>
                    @else
                        <div class="w-11 h-11 rounded-xl bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                            <i data-lucide="leaf" class="w-5 h-5"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $d->disease }}</div>
                        <div class="text-xs text-ink-500 mt-0.5">{{ $d->crop }} · {{ $d->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge text-xs shrink-0 {{ $d->risk_level==='Tinggi'?'bg-red-100 text-red-700':($d->risk_level==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                        {{ $d->risk_level }}
                    </span>
                </a>
            @empty
                <div class="py-10 text-center">
                    <i data-lucide="scan-line" class="w-10 h-10 mx-auto text-ink-300 mb-2"></i>
                    <div class="text-sm text-ink-500">Belum ada diagnosa.</div>
                    <a href="{{ route('diagnosis.index') }}" class="text-brand-700 font-semibold text-sm block mt-1 hover:underline">Mulai diagnosa pertama →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Weather widget --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="cloud-sun" class="w-4 h-4 text-brand-600"></i> Cuaca Hari Ini
            </div>
            <a href="{{ route('weather.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Detail →</a>
        </div>

        @if($weatherCurrent)
            @php
                $cLow = strtolower($weatherCurrent->condition ?? '');
                $wIcon = str_contains($cLow,'petir')||str_contains($cLow,'thunder') ? 'cloud-lightning'
                    : (str_contains($cLow,'lebat')||str_contains($cLow,'heavy') ? 'cloud-rain'
                    : (str_contains($cLow,'hujan')||str_contains($cLow,'rain')||str_contains($cLow,'drizzle') ? 'cloud-drizzle'
                    : (str_contains($cLow,'berawan')||str_contains($cLow,'cloud') ? 'cloud' : 'sun')));
            @endphp
            <div class="flex items-center gap-3 mb-3">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center shrink-0">
                    <i data-lucide="{{ $wIcon }}" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="text-4xl font-extrabold leading-none">{{ $weatherCurrent->temperature }}°<span class="text-xl text-ink-400">C</span></div>
                    <div class="text-xs text-ink-500 mt-1">{{ $weatherCurrent->condition }} · {{ $weatherCurrent->location }}</div>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-1">
                @foreach($weatherForecast->take(7) as $w)
                    @php
                        $wc = strtolower($w->condition ?? '');
                        $wi = str_contains($wc,'hujan')||str_contains($wc,'rain') ? 'cloud-rain'
                            : (str_contains($wc,'berawan')||str_contains($wc,'cloud') ? 'cloud' : 'sun');
                    @endphp
                    <div class="text-center p-1.5 rounded-lg bg-ink-50 hover:bg-brand-50 transition">
                        <div class="text-[9px] text-ink-400 font-medium">{{ \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}</div>
                        <i data-lucide="{{ $wi }}" class="w-4 h-4 mx-auto my-1 text-brand-600"></i>
                        <div class="text-[10px] font-bold">{{ $w->temperature }}°</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 text-[10px] text-ink-400 flex items-center gap-1">
                <i data-lucide="clock" class="w-2.5 h-2.5"></i>
                Diperbarui {{ now()->isoFormat('HH:mm') }} WIB · {{ $user->location ?? 'Indonesia' }}
            </div>
        @else
            <div class="py-6 text-center">
                <i data-lucide="map-pin" class="w-8 h-8 mx-auto text-ink-300 mb-2"></i>
                <div class="text-sm text-ink-500">Atur lokasi untuk cuaca akurat</div>
                <a href="{{ route('profile.settings') }}" class="text-brand-700 font-semibold text-sm mt-1 block hover:underline">Atur Lokasi →</a>
            </div>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-5">
    {{-- Market Prices (reference screenshot UI) --}}
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="line-chart" class="w-4 h-4 text-brand-600"></i> Harga Pasar Hari Ini
            </div>
            <a href="{{ route('market.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse($market->take(5) as $m)
                @php
                    $price    = is_object($m) ? ($m->price ?? 0)           : ($m['price'] ?? 0);
                    $unit     = is_object($m) ? ($m->unit ?? 'kg')          : ($m['unit'] ?? 'kg');
                    $commodity= is_object($m) ? ($m->commodity ?? '-')      : ($m['commodity'] ?? '-');
                    $region   = is_object($m) ? ($m->region ?? '-')         : ($m['region'] ?? '-');
                    $change   = is_object($m) ? ($m->change_percent ?? 0)   : ($m['change_percent'] ?? 0);
                    $up       = $change >= 0;
                @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 grid place-items-center shrink-0 text-lg">
                        {{ ['Cabai'=>'🌶️','Bawang'=>'🧅','Tomat'=>'🍅','Jagung'=>'🌽','Kentang'=>'🥔','Padi'=>'🌾','Gabah'=>'🌾'][$commodity] ?? '🌿' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm">{{ $commodity }}</div>
                        <div class="text-xs text-ink-400">{{ $region }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold text-sm">Rp {{ number_format($price,0,',','.') }}<span class="text-xs text-ink-400">/{{ $unit }}</span></div>
                        <div class="text-xs font-bold {{ $up ? 'text-green-600' : 'text-red-500' }}">
                            {{ $up ? '▲' : '▼' }} {{ number_format(abs($change),1) }}%
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-ink-400">
                    <a href="{{ route('market.index') }}" class="text-brand-700 font-semibold">Lihat harga pasar →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card p-5">
        <div class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="zap" class="w-4 h-4 text-brand-600"></i> Aksi Cepat
        </div>
        <div class="space-y-1.5">
            @foreach([
                [route('diagnosis.index'), 'scan-line',      'Diagnosa Tanaman', 'Foto & analisa penyakit',   'brand'],
                [route('chat.index'),      'message-circle', 'Tanya AI',         'Konsultasi pertanian',      'blue'],
                [route('records.index'),   'clipboard-list', 'Catatan Lahan',    'Pantau tanaman Anda',       'green'],
                [route('community.index'), 'users',          'Komunitas',        'Diskusi dengan petani',     'purple'],
            ] as [$href, $icon, $title, $desc, $color])
                <a href="{{ $href }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-ink-50 border border-transparent hover:border-ink-200 transition">
                    <div class="w-9 h-9 rounded-xl bg-{{ $color }}-100 text-{{ $color }}-700 grid place-items-center shrink-0">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-sm">{{ $title }}</div>
                        <div class="text-xs text-ink-400">{{ $desc }}</div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-ink-300 ml-auto shrink-0"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Community --}}
@if($posts->count())
<div class="card p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-brand-600"></i> Diskusi Terbaru
        </div>
        <a href="{{ route('community.index') }}" class="btn-outline text-sm">Lihat Semua</a>
    </div>
    <div class="grid sm:grid-cols-2 gap-3">
        @foreach($posts as $p)
            <a href="{{ route('community.index') }}" class="block p-3.5 rounded-xl bg-ink-50 hover:bg-brand-50/60 transition">
                <div class="flex items-center gap-2 mb-1.5">
                    @php
                        $colors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600'];
                        $ci = crc32($p->user->name ?? '?') % 4;
                    @endphp
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br {{ $colors[$ci] }} grid place-items-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr($p->user->name??'?',0,1)) }}
                    </div>
                    <span class="text-xs font-semibold truncate">{{ $p->user->name ?? 'Anonim' }}</span>
                    <span class="text-xs text-ink-400 ml-auto shrink-0">{{ $p->created_at->diffForHumans() }}</span>
                </div>
                <div class="font-bold text-sm line-clamp-1">{{ $p->title }}</div>
                <p class="text-xs text-ink-500 mt-1 line-clamp-2">{{ $p->content }}</p>
            </a>
        @endforeach
    </div>
</div>
@endif
@endsection
