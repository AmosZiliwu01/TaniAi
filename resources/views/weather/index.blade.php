@extends('layouts.app')
@section('title','Cuaca & Peringatan')
@section('content')

<div class="mb-5 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Cuaca & Peringatan</h1>
        <p class="text-ink-500 mt-1 text-sm">
            Data cuaca untuk <b>{{ $current->location ?? $location ?: 'lokasi Anda' }}</b>
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if($usingReal)
            <span class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-brand-50 border border-brand-200 text-brand-700">
                <i data-lucide="wifi" class="w-3.5 h-3.5"></i> Data real-time
            </span>
        @elseif($errorMsg)
            <span class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Gagal memuat
            </span>
        @else
            <span class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Data perkiraan
            </span>
        @endif
        <a href="{{ route('profile.settings') }}" class="btn-outline text-sm">
            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Ubah Lokasi
        </a>
    </div>
</div>

{{-- Location not set --}}
@if(!$location)
    <div class="card p-8 text-center mb-5">
        <i data-lucide="map-pin" class="w-12 h-12 mx-auto mb-3 text-ink-300"></i>
        <div class="font-semibold text-ink-700">Lokasi belum diatur</div>
        <p class="text-sm text-ink-500 mt-1">Atur lokasi di pengaturan untuk melihat data cuaca yang akurat.</p>
        <a href="{{ route('profile.settings') }}" class="btn-primary mt-4 inline-flex">
            <i data-lucide="settings" class="w-4 h-4"></i> Atur Lokasi Sekarang
        </a>
    </div>
@endif

{{-- Error message --}}
@if($errorMsg)
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 shrink-0 mt-0.5"></i>
        <div class="flex-1">
            <div class="font-semibold text-red-800">Gagal Memuat Data Cuaca</div>
            <p class="text-sm text-red-700 mt-0.5">{{ $errorMsg }}</p>
            <a href="{{ route('profile.settings') }}" class="inline-flex items-center gap-1 mt-2 text-sm font-semibold text-red-700 underline">
                <i data-lucide="settings" class="w-3.5 h-3.5"></i> Perbaiki Lokasi
            </a>
        </div>
    </div>
@endif

{{-- Alert --}}
@if($alert)
    <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
        <div>
            <div class="font-bold text-amber-900">Peringatan Cuaca</div>
            <p class="text-sm text-amber-800 mt-0.5">{{ $alert }}</p>
        </div>
    </div>
@endif

@if($current)
<div class="grid lg:grid-cols-3 gap-4 mb-5">

    {{-- Current weather --}}
    <div class="card p-6">
        <div class="text-xs text-ink-400 mb-3">
            Hari ini · {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}
        </div>
        @php
            $cLow = strtolower($current->condition ?? '');
            $wIcon = str_contains($cLow,'petir')||str_contains($cLow,'thunder') ? 'cloud-lightning'
                : (str_contains($cLow,'lebat')||str_contains($cLow,'heavy') ? 'cloud-rain'
                : (str_contains($cLow,'hujan')||str_contains($cLow,'rain')||str_contains($cLow,'drizzle') ? 'cloud-drizzle'
                : (str_contains($cLow,'berawan')||str_contains($cLow,'cloud') ? 'cloud' : 'sun')));
            $isRain = str_contains($cLow,'hujan')||str_contains($cLow,'rain')||str_contains($cLow,'drizzle');
        @endphp
        <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-2xl {{ $isRain ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600' }} grid place-items-center shrink-0">
                <i data-lucide="{{ $wIcon }}" class="w-8 h-8"></i>
            </div>
            <div>
                <div class="text-5xl font-extrabold leading-none">{{ $current->temperature }}°</div>
                <div class="text-sm text-ink-500 mt-1">{{ $current->condition }}</div>
                @if(isset($current->feels_like))
                    <div class="text-xs text-ink-400 mt-0.5">Terasa {{ $current->feels_like }}°C</div>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center">
            <div class="p-2.5 rounded-xl bg-ink-50">
                <i data-lucide="droplets" class="w-4 h-4 mx-auto text-ink-500 mb-1"></i>
                <div class="font-bold text-sm">{{ $current->humidity ?? 70 }}%</div>
                <div class="text-[10px] text-ink-400">Kelembapan</div>
            </div>
            <div class="p-2.5 rounded-xl bg-ink-50">
                <i data-lucide="wind" class="w-4 h-4 mx-auto text-ink-500 mb-1"></i>
                <div class="font-bold text-sm">{{ $current->wind_speed ?? 12 }}</div>
                <div class="text-[10px] text-ink-400">km/jam</div>
            </div>
            <div class="p-2.5 rounded-xl bg-ink-50">
                <i data-lucide="cloud-rain" class="w-4 h-4 mx-auto text-ink-500 mb-1"></i>
                <div class="font-bold text-sm">{{ $current->rainfall ?? 0 }}mm</div>
                <div class="text-[10px] text-ink-400">Hujan</div>
            </div>
        </div>
        @if($usingReal)
            <div class="mt-3 text-[10px] text-ink-400 flex items-center gap-1">
                <i data-lucide="clock" class="w-3 h-3"></i>
                Diperbarui {{ now()->timezone('Asia/Jakarta')->isoFormat('HH:mm') }} WIB · OpenWeatherMap
            </div>
        @endif
    </div>

    {{-- 7-day forecast --}}
    <div class="card p-5 lg:col-span-2">
        <div class="font-bold mb-4">Prakiraan 7 Hari ke Depan</div>
        <div class="grid grid-cols-7 gap-1.5">
            @foreach(array_slice($forecast, 0, 7) as $i => $w)
                @php
                    $wc = strtolower($w->condition ?? '');
                    $wi = str_contains($wc,'petir')||str_contains($wc,'thunder') ? 'cloud-lightning'
                        : (str_contains($wc,'lebat')||str_contains($wc,'heavy') ? 'cloud-rain'
                        : (str_contains($wc,'hujan')||str_contains($wc,'rain')||str_contains($wc,'drizzle') ? 'cloud-drizzle'
                        : (str_contains($wc,'berawan')||str_contains($wc,'cloud') ? 'cloud' : 'sun')));
                    $isToday = $i === 0;
                @endphp
                <div class="p-2 rounded-xl {{ $isToday ? 'bg-brand-50 border border-brand-200' : 'bg-ink-50' }} text-center">
                    <div class="text-[10px] font-semibold {{ $isToday ? 'text-brand-700' : 'text-ink-500' }}">
                        {{ $isToday ? 'Hari ini' : \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}
                    </div>
                    <i data-lucide="{{ $wi }}" class="w-5 h-5 mx-auto my-1.5 {{ $isToday ? 'text-brand-700' : 'text-ink-600' }}"></i>
                    <div class="font-bold text-sm {{ $isToday ? 'text-brand-800' : '' }}">{{ $w->temperature }}°</div>
                    @if($w->rainfall > 0)
                        <div class="text-[10px] text-blue-600 font-semibold">{{ $w->rainfall }}mm</div>
                    @else
                        <div class="text-[10px] text-ink-300">—</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Farming recommendations --}}
<div class="card p-5">
    <div class="font-bold mb-3 flex items-center gap-2">
        <i data-lucide="lightbulb" class="w-4 h-4 text-brand-600"></i> Rekomendasi Pertanian Hari Ini
    </div>
    @php
        $cLow  = strtolower($current->condition ?? '');
        $rain  = str_contains($cLow,'hujan')||str_contains($cLow,'rain')||str_contains($cLow,'drizzle');
        $hot   = ($current->temperature ?? 27) >= 33;
        $recs  = $rain ? [
            ['cloud-off',   'Tunda pemupukan daun — hujan mengurangi efektivitas'],
            ['droplets',    'Periksa drainase lahan, cegah genangan air'],
            ['shield',      'Waspada penyakit jamur — semprot fungisida preventif jika perlu'],
            ['package',     'Lindungi bibit dan tanaman muda dari hujan lebat'],
        ] : ($hot ? [
            ['sun',         'Suhu panas — tambah frekuensi pengairan pagi/sore'],
            ['thermometer', 'Pasang naungan sementara untuk bibit/tanaman muda'],
            ['sprout',      'Pantau stres panas: daun menggulung di siang hari'],
            ['clock',       'Semprot pestisida lebih awal pagi (06:00–08:00)'],
        ] : [
            ['check-circle','Kondisi ideal — waktu terbaik pemupukan daun'],
            ['droplets',    'Lakukan pengairan ringan jika tidak hujan 2+ hari terakhir'],
            ['bug',         'Kondisi baik untuk aplikasi pestisida (tidak hujan, angin tenang)'],
            ['scissors',    'Pantau kematangan tanaman — kondisi optimal untuk panen'],
        ]);
    @endphp
    <div class="grid sm:grid-cols-2 gap-2">
        @foreach($recs as [$ico, $rec])
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-ink-50 hover:bg-brand-50/40 transition">
                <i data-lucide="{{ $ico }}" class="w-4 h-4 text-brand-600 shrink-0 mt-0.5"></i>
                <span class="text-sm text-ink-700">{{ $rec }}</span>
            </div>
        @endforeach
    </div>
    @if(!$usingReal)
        <p class="text-xs text-ink-400 mt-3 flex items-center gap-1">
            <i data-lucide="info" class="w-3 h-3"></i>
            Tambahkan <code class="bg-ink-100 px-1 rounded text-xs">OPENWEATHER_API_KEY</code> di <code class="bg-ink-100 px-1 rounded text-xs">.env</code> untuk data cuaca real-time.
        </p>
    @endif
</div>
@endif
@endsection
