@extends('layouts.app')
@section('title','Beranda')
@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">
            Selamat datang, {{ explode(' ', auth()->user()->name)[0] }} 🌱
        </h1>
        <p class="text-ink-500 mt-1 text-sm">AI siap membantu meningkatkan hasil panen Anda.</p>
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

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @php
    $statItems = [
        ['Diagnosa Saya', $stats['diagnoses'], 'scan-line', 'brand-100', 'brand-700'],
        ['Kesehatan Tanaman', $stats['healthy_rate'].'%', 'heart-pulse', 'green-100', 'green-700'],
        ['Peringatan Aktif', $stats['active_alerts'], 'alert-triangle', 'amber-100', 'amber-700'],
        ['Prediksi Panen', $stats['predicted_yield'].'%', 'trending-up', 'blue-100', 'blue-700'],
    ];
    @endphp
    @foreach($statItems as [$label, $value, $icon, $bg, $fg])
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">{{ $label }}</div>
                <div class="w-9 h-9 rounded-lg bg-{{ $bg }} text-{{ $fg }} grid place-items-center shrink-0">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold mt-1">{{ $value }}</div>
        </div>
    @endforeach
</div>

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
                        <img src="{{ Storage::url($d->image_path) }}" class="w-11 h-11 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-11 h-11 rounded-xl bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                            <i data-lucide="leaf" class="w-5 h-5"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $d->disease }}</div>
                        <div class="text-xs text-ink-500">{{ $d->crop }} · {{ $d->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge text-xs shrink-0 {{ $d->risk_level === 'Tinggi' ? 'bg-red-100 text-red-700' : ($d->risk_level === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'badge-green') }}">
                        {{ (int)$d->confidence }}%
                    </span>
                </a>
            @empty
                <div class="py-10 text-center text-sm text-ink-500">
                    Belum ada diagnosa.
                    <a href="{{ route('diagnosis.index') }}" class="text-brand-700 font-semibold block mt-1">Mulai diagnosa pertama →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Weather Widget --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="cloud-sun" class="w-4 h-4 text-brand-600"></i> Cuaca Hari Ini
            </div>
            <a href="{{ route('weather.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Detail →</a>
        </div>

        @if($weather->count())
            @php $todayW = $weather->first(); @endphp
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 grid place-items-center text-amber-600 shrink-0">
                    <i data-lucide="cloud-sun" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="text-3xl font-extrabold">{{ (int)($todayW->temperature ?? 27) }}°C</div>
                    <div class="text-sm text-ink-500">{{ $todayW->condition ?? 'Berawan' }} · {{ auth()->user()->location ?? 'Indonesia' }}</div>
                </div>
            </div>
            <div class="grid grid-cols-{{ min($weather->count(), 7) }} gap-1">
                @foreach($weather->take(7) as $w)
                    @php
                        $wc = strtolower($w->condition ?? '');
                        $wi = str_contains($wc,'hujan') ? 'cloud-rain' : (str_contains($wc,'berawan') ? 'cloud' : 'sun');
                    @endphp
                    <div class="p-1.5 rounded-lg bg-brand-50 text-center">
                        <div class="text-[9px] text-ink-500">{{ \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}</div>
                        <i data-lucide="{{ $wi }}" class="w-4 h-4 mx-auto my-1 text-brand-600"></i>
                        <div class="text-[10px] font-bold">{{ (int)($w->temperature ?? 27) }}°</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-sm text-ink-500 py-4">
                <a href="{{ route('weather.index') }}" class="text-brand-700 font-semibold">Lihat info cuaca →</a>
            </div>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-5">
    {{-- Market Prices --}}
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="line-chart" class="w-4 h-4 text-brand-600"></i> Harga Komoditas
            </div>
            <a href="{{ route('market.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse($market->take(5) as $m)
                @php
                    $price     = is_object($m) && isset($m->price)   ? $m->price   : ($m['price'] ?? 0);
                    $unit      = is_object($m) && isset($m->unit)    ? $m->unit    : ($m['unit'] ?? 'kg');
                    $commodity = is_object($m) && isset($m->commodity) ? $m->commodity : ($m['commodity'] ?? '-');
                    $region    = is_object($m) && isset($m->region)  ? $m->region  : ($m['region'] ?? '-');
                    $change    = is_object($m) && isset($m->change_percent) ? $m->change_percent : ($m['change_percent'] ?? 0);
                @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                        <i data-lucide="sprout" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm">{{ $commodity }}</div>
                        <div class="text-xs text-ink-500">{{ $region }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold text-sm">Rp {{ number_format($price, 0, ',', '.') }}<span class="text-xs text-ink-400 font-normal">/{{ $unit }}</span></div>
                        <div class="text-xs font-semibold {{ $change >= 0 ? 'text-brand-700' : 'text-red-600' }}">
                            {{ $change >= 0 ? '▲' : '▼' }} {{ number_format(abs($change), 1) }}%
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-ink-500">
                    <a href="{{ route('market.index') }}" class="text-brand-700 font-semibold">Lihat harga pasar →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card p-5">
        <div class="font-bold mb-4 flex items-center gap-2">
            <i data-lucide="zap" class="w-4 h-4 text-brand-600"></i> Aksi Cepat
        </div>
        <div class="space-y-2">
            @foreach([
                [route('diagnosis.index'), 'scan-line',       'Diagnosa Tanaman',  'Foto & analisa penyakit',  'brand'],
                [route('chat.index'),      'message-circle',  'Tanya AI',          'Konsultasi pertanian',     'blue'],
                [route('records.index'),   'clipboard-list',  'Catat Lahan',       'Input kegiatan lahan',     'green'],
                [route('community.index'), 'users',           'Komunitas',         'Diskusi dengan petani',    'purple'],
            ] as [$href, $icon, $title, $desc, $color])
                <a href="{{ $href }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-ink-50 border border-transparent hover:border-ink-200 transition">
                    <div class="w-9 h-9 rounded-xl bg-{{ $color }}-100 text-{{ $color }}-700 grid place-items-center shrink-0">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-sm">{{ $title }}</div>
                        <div class="text-xs text-ink-500">{{ $desc }}</div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-ink-300 ml-auto shrink-0"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Community preview --}}
@if($posts->count())
<div class="card p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-brand-600"></i> Diskusi Terbaru
        </div>
        <a href="{{ route('community.index') }}" class="btn-outline text-sm">
            Gabung Komunitas
        </a>
    </div>
    <div class="grid sm:grid-cols-2 gap-3">
        @foreach($posts as $p)
            <div class="p-4 rounded-xl bg-ink-50 hover:bg-brand-50 transition">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-7 h-7 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="text-sm font-semibold">{{ $p->user->name ?? 'Anonim' }}</div>
                    <div class="text-xs text-ink-400 ml-auto">{{ $p->created_at->diffForHumans() }}</div>
                </div>
                <div class="font-bold text-sm">{{ $p->title }}</div>
                <p class="text-xs text-ink-500 mt-1 line-clamp-2">{{ $p->content }}</p>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
