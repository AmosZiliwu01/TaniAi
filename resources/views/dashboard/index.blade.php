@extends('layouts.app')
@section('title','Beranda')
@section('content')

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Selamat datang, {{ auth()->user()->name }} <span>🌱</span></h1>
        <p class="text-ink-500 mt-1">AI siap membantu meningkatkan hasil panen dan pendapatan Anda.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('diagnosis.index') }}" class="btn-primary"><i data-lucide="scan-line" class="w-4 h-4"></i> Diagnosa Cepat</a>
        <a href="{{ route('chat.index') }}" class="btn-outline"><i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI</a>
    </div>
</div>

<!-- HERO PANEL -->
<div class="card p-6 lg:p-8 mb-6 bg-gradient-to-br from-brand-50 to-white relative overflow-hidden">
    <div class="grid lg:grid-cols-3 gap-6 items-center">
        <div class="lg:col-span-2">
            <span class="badge-green"><i data-lucide="sparkles" class="w-3 h-3"></i> Insight AI Hari Ini</span>
            <h2 class="mt-3 text-2xl font-extrabold">Solusi Cerdas untuk <span class="text-brand-600">Petani Indonesia</span></h2>
            <p class="mt-2 text-ink-600 max-w-xl text-sm">Berdasarkan kondisi cuaca dan fase tanaman, AI menyarankan pemupukan UREA 50kg/ha dalam 3 hari ke depan.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <div class="card p-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="scan-line" class="w-4 h-4"></i></div>
                    <div><div class="text-xs text-ink-500">Diagnosa Tanaman</div><div class="text-sm font-semibold">dari foto daun</div></div>
                </div>
                <div class="card p-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 grid place-items-center text-blue-700"><i data-lucide="cloud-sun" class="w-4 h-4"></i></div>
                    <div><div class="text-xs text-ink-500">Prediksi Cuaca</div><div class="text-sm font-semibold">7 hari kedepan</div></div>
                </div>
                <div class="card p-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 grid place-items-center text-amber-700"><i data-lucide="trending-up" class="w-4 h-4"></i></div>
                    <div><div class="text-xs text-ink-500">Harga Pasar</div><div class="text-sm font-semibold">Terbaru</div></div>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">Hasil Diagnosa AI</div>
                <span class="badge-green">92%</span>
            </div>
            <div class="mt-3 h-28 rounded-xl bg-gradient-to-br from-brand-200 to-brand-50 grid place-items-center text-brand-700">
                <i data-lucide="leaf" class="w-12 h-12"></i>
            </div>
            <div class="mt-3 text-sm"><b>Hawar Daun (Blight)</b></div>
            <div class="text-xs text-ink-500">Rekomendasi: fungisida Propineb 70WP</div>
        </div>
    </div>
</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['Diagnosa Total',$stats['diagnoses'],'scan-line','brand'],
            ['Kesehatan Tanaman',$stats['healthy_rate'].'%','heart-pulse','green'],
            ['Peringatan Aktif',$stats['active_alerts'],'alert-triangle','amber'],
            ['Prediksi Panen',$stats['predicted_yield'].'%','trending-up','blue'],
        ];
    @endphp
    @foreach($cards as [$label,$value,$icon,$tone])
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">{{ $label }}</div>
                <div class="w-9 h-9 rounded-lg bg-brand-100 text-brand-700 grid place-items-center"><i data-lucide="{{ $icon }}" class="w-4 h-4"></i></div>
            </div>
            <div class="text-2xl font-extrabold">{{ $value }}</div>
            <div class="text-xs text-brand-700 font-semibold">+12% dari minggu lalu</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-6">
    <!-- Recent diagnoses -->
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold">Diagnosa Terbaru</div>
            <a href="{{ route('diagnosis.index') }}" class="text-xs font-semibold text-brand-700">Lihat semua →</a>
        </div>
        <div class="divide-y divide-ink-200">
            @forelse($recent_diagnoses as $d)
                <a href="{{ route('diagnosis.show',$d) }}" class="flex items-center gap-4 py-3 hover:bg-ink-50 rounded-xl px-2 -mx-2">
                    <div class="w-12 h-12 rounded-xl bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="leaf" class="w-5 h-5"></i></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $d->disease }} <span class="text-ink-500 font-normal">· {{ $d->crop }}</span></div>
                        <div class="text-xs text-ink-500">{{ $d->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge-green">{{ (int)$d->confidence }}%</span>
                </a>
            @empty
                <div class="py-10 text-center text-sm text-ink-500">Belum ada diagnosa. <a href="{{ route('diagnosis.index') }}" class="text-brand-700 font-semibold">Mulai sekarang</a></div>
            @endforelse
        </div>
    </div>

    <!-- Weather widget -->
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold">Cuaca Hari Ini</div>
            <a href="{{ route('weather.index') }}" class="text-xs font-semibold text-brand-700">Detail →</a>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 grid place-items-center text-amber-600"><i data-lucide="cloud-sun" class="w-8 h-8"></i></div>
            <div>
                <div class="text-3xl font-extrabold">27°C</div>
                <div class="text-sm text-ink-500">Berawan · Sleman, DIY</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Kelembapan</div><div class="font-bold mt-1">78%</div></div>
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Angin</div><div class="font-bold mt-1">12km/j</div></div>
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Hujan</div><div class="font-bold mt-1">20%</div></div>
        </div>
        <div class="mt-4 grid grid-cols-7 gap-1">
            @foreach($weather as $w)
                <div class="p-2 rounded-lg bg-brand-50 text-center">
                    <div class="text-[10px] text-ink-500">{{ \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}</div>
                    <i data-lucide="sun" class="w-4 h-4 mx-auto text-amber-500"></i>
                    <div class="text-[11px] font-bold">{{ (int)$w->temperature }}°</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-6">
    <!-- Market -->
    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold">Harga Komoditas Populer</div>
            <a href="{{ route('market.index') }}" class="text-xs font-semibold text-brand-700">Lihat semua →</a>
        </div>
        <div class="divide-y divide-ink-200">
            @foreach($market as $m)
                <div class="flex items-center gap-4 py-2.5">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="sprout" class="w-4 h-4"></i></div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm">{{ $m->commodity }}</div>
                        <div class="text-xs text-ink-500">{{ $m->region }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-sm">Rp {{ number_format($m->price,0,',','.') }}<span class="text-xs text-ink-500 font-normal">/{{ $m->unit }}</span></div>
                        <div class="text-xs font-semibold {{ $m->change_percent >= 0 ? 'text-brand-700' : 'text-red-600' }}">
                            {{ $m->change_percent >= 0 ? '▲' : '▼' }} {{ number_format(abs($m->change_percent),1) }}%
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Alert -->
    <div class="card p-5">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500"></i>
            <div class="font-bold">Peringatan Dini</div>
        </div>
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
            <div class="font-bold text-amber-900">Waspada Curah Hujan Tinggi</div>
            <p class="text-xs text-amber-800 mt-1">Dalam 3 hari ke depan diprediksi curah hujan tinggi di wilayah Anda. Siapkan saluran drainase!</p>
        </div>
        <div class="mt-4 p-4 rounded-xl bg-brand-50 border border-brand-200">
            <div class="font-bold text-brand-800">AI Rekomendasi Hari Ini</div>
            <p class="text-xs text-brand-700 mt-1">Pemupukan UREA 50kg/ha dianjurkan dalam 3 hari ke depan.</p>
        </div>
    </div>
</div>

<!-- Community -->
<div class="card p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="font-bold">Komunitas Petani</div>
        <a href="{{ route('community.index') }}" class="btn-outline text-sm">Gabung Komunitas</a>
    </div>
    <div class="grid sm:grid-cols-2 gap-3">
        @foreach($posts as $p)
            <div class="p-4 rounded-xl bg-ink-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm">{{ substr($p->user->name,0,1) }}</div>
                    <div>
                        <div class="font-semibold text-sm">{{ $p->user->name }}</div>
                        <div class="text-xs text-ink-500">{{ $p->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div class="mt-2 font-semibold text-sm">{{ $p->title }}</div>
                <p class="text-xs text-ink-500 line-clamp-2">{{ $p->content }}</p>
            </div>
        @endforeach
    </div>
</div>

@endsection
