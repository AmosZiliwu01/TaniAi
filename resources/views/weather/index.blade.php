@extends('layouts.app')
@section('title','Cuaca & Peringatan')
@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Cuaca & Peringatan</h1>
        <p class="text-ink-500 mt-1">Informasi cuaca untuk lahan Anda di <b>{{ $location }}</b>.</p>
    </div>
    @if(!$usingReal)
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700">
            <i data-lucide="info" class="w-4 h-4"></i>
            <span>Data perkiraan. <a href="{{ route('profile.settings') }}" class="font-semibold underline">Update lokasi</a> atau tambahkan API cuaca di .env</span>
        </div>
    @else
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-50 border border-brand-200 text-sm text-brand-700">
            <i data-lucide="wifi" class="w-4 h-4"></i>
            <span>Data real-time dari OpenWeatherMap</span>
        </div>
    @endif
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-6">
    <div class="card p-6 lg:col-span-1">
        <div class="text-sm font-semibold text-ink-500">{{ $current->location ?? $location }}</div>
        <div class="text-xs text-ink-400 mb-3">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
        <div class="flex items-center gap-4">
            @php
                $cLower = strtolower($current->condition ?? '');
                $icon = str_contains($cLower,'petir') ? 'cloud-lightning'
                    : (str_contains($cLower,'hujan lebat') ? 'cloud-rain'
                    : (str_contains($cLower,'hujan') ? 'cloud-drizzle'
                    : (str_contains($cLower,'berawan') ? 'cloud'
                    : 'sun')));
                $iconColor = str_contains($cLower,'hujan') || str_contains($cLower,'petir') ? 'text-blue-600 bg-blue-100' : 'text-amber-600 bg-amber-100';
            @endphp
            <div class="w-16 h-16 rounded-2xl {{ $iconColor }} grid place-items-center">
                <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
            </div>
            <div>
                <div class="text-4xl font-extrabold">{{ (int)($current->temperature ?? 27) }}°C</div>
                <div class="text-sm text-ink-500">{{ $current->condition ?? 'Berawan' }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
            <div class="p-2 rounded-lg bg-ink-50">
                <div class="text-ink-500">Kelembapan</div>
                <div class="font-bold mt-1">{{ (int)($current->humidity ?? 78) }}%</div>
            </div>
            <div class="p-2 rounded-lg bg-ink-50">
                <div class="text-ink-500">Angin</div>
                <div class="font-bold mt-1">{{ $current->wind_speed ?? 12 }}km/j</div>
            </div>
            <div class="p-2 rounded-lg bg-ink-50">
                <div class="text-ink-500">Hujan</div>
                <div class="font-bold mt-1">{{ $current->rainfall ?? 0 }}mm</div>
            </div>
        </div>
    </div>

    <div class="card p-6 lg:col-span-2">
        <div class="font-bold mb-4">Prediksi 7 Hari</div>
        <div class="grid grid-cols-7 gap-1.5">
            @foreach($forecast as $w)
                @php
                    $wc = strtolower($w->condition);
                    $wi = str_contains($wc,'petir') ? 'cloud-lightning'
                        : (str_contains($wc,'lebat') ? 'cloud-rain'
                        : (str_contains($wc,'hujan') ? 'cloud-drizzle'
                        : (str_contains($wc,'berawan') ? 'cloud' : 'sun')));
                    $wBg = str_contains($wc,'hujan') ? 'bg-blue-50' : 'bg-brand-50';
                @endphp
                <div class="p-2 rounded-xl {{ $wBg }} text-center">
                    <div class="text-[10px] text-ink-500">{{ \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}</div>
                    <i data-lucide="{{ $wi }}" class="w-5 h-5 mx-auto my-1.5 text-brand-700"></i>
                    <div class="font-bold text-sm">{{ (int)$w->temperature }}°</div>
                    <div class="text-[10px] text-ink-500">{{ $w->rainfall }}mm</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Alert --}}
@if($alert)
    <div class="card p-4 border-amber-200 bg-amber-50 mb-4">
        <div class="flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mt-0.5"></i>
            <div>
                <div class="font-bold text-amber-900">Peringatan Cuaca</div>
                <p class="text-sm text-amber-800 mt-1">{{ $alert }}</p>
            </div>
        </div>
    </div>
@endif

{{-- Rekomendasi berdasarkan cuaca --}}
<div class="card p-5">
    <div class="font-bold mb-3 flex items-center gap-2">
        <i data-lucide="lightbulb" class="w-4 h-4 text-brand-600"></i> Rekomendasi Pertanian Hari Ini
    </div>
    @php
        $cLower = strtolower($current->condition ?? '');
        $isRainy = str_contains($cLower, 'hujan');
        $recs = $isRainy ? [
            'Tunda pemupukan daun sampai cuaca membaik',
            'Periksa saluran drainase lahan agar tidak tergenang',
            'Waspadai serangan jamur dan penyakit daun',
            'Lindungi bibit/tanaman muda dari hujan lebat',
        ] : [
            'Waktu optimal untuk pemupukan daun (pagi/sore)',
            'Lakukan penyiraman jika tidak ada hujan 3 hari terakhir',
            'Kondisi baik untuk aplikasi pestisida',
            'Pantau kelembapan tanah secara berkala',
        ];
    @endphp
    <div class="grid sm:grid-cols-2 gap-2">
        @foreach($recs as $rec)
            <div class="flex items-start gap-2 text-sm p-3 rounded-xl bg-ink-50">
                <i data-lucide="check-circle" class="w-4 h-4 text-brand-600 mt-0.5 shrink-0"></i>
                <span>{{ $rec }}</span>
            </div>
        @endforeach
    </div>
    <div class="mt-3 text-xs text-ink-400 flex items-center gap-1">
        <i data-lucide="map-pin" class="w-3 h-3"></i>
        Berdasarkan cuaca di {{ $location }}. <a href="{{ route('profile.settings') }}" class="text-brand-600 hover:underline ml-1">Ubah lokasi</a>
    </div>
</div>
@endsection
