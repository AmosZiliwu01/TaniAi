@extends('layouts.app')
@section('title','Cuaca & Peringatan')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Cuaca & Peringatan</h1>
    <p class="text-ink-500 mt-1">Informasi cuaca dan peringatan dini untuk lahan Anda.</p>
</div>

<div class="grid lg:grid-cols-3 gap-4 mb-6">
    <div class="card p-6 lg:col-span-1">
        <div class="text-sm font-semibold text-ink-500">{{ $current->location ?? 'Sleman, DIY' }}</div>
        <div class="flex items-center gap-4 mt-2">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 grid place-items-center text-amber-600"><i data-lucide="cloud-sun" class="w-8 h-8"></i></div>
            <div>
                <div class="text-4xl font-extrabold">{{ (int)($current->temperature ?? 27) }}°C</div>
                <div class="text-sm text-ink-500">{{ $current->condition ?? 'Berawan' }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs">
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Kelembapan</div><div class="font-bold mt-1">{{ (int)($current->humidity ?? 78) }}%</div></div>
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Angin</div><div class="font-bold mt-1">12km/j</div></div>
            <div class="p-2 rounded-lg bg-ink-50"><div class="text-ink-500">Hujan</div><div class="font-bold mt-1">{{ (int)($current->rainfall ?? 5) }}mm</div></div>
        </div>
    </div>

    <div class="card p-6 lg:col-span-2">
        <div class="font-bold mb-4">Prediksi 7 Hari</div>
        <div class="grid grid-cols-7 gap-2">
            @foreach($forecast as $w)
                @php
                    $c = strtolower($w->condition);
                    $icon = str_contains($c,'hujan') ? 'cloud-rain' : (str_contains($c,'petir') ? 'cloud-lightning' : (str_contains($c,'berawan') ? 'cloud' : 'sun'));
                @endphp
                <div class="p-3 rounded-xl bg-brand-50 text-center">
                    <div class="text-xs text-ink-500">{{ \Carbon\Carbon::parse($w->forecast_date)->isoFormat('dd') }}</div>
                    <i data-lucide="{{ $icon }}" class="w-6 h-6 mx-auto my-2 text-brand-700"></i>
                    <div class="font-bold">{{ (int)$w->temperature }}°</div>
                    <div class="text-[10px] text-ink-500">{{ (int)$w->rainfall }}mm</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card p-6 border-amber-200 bg-amber-50">
    <div class="flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600"></i>
        <div>
            <div class="font-bold text-amber-900">Peringatan Dini: Waspada Curah Hujan Tinggi</div>
            <p class="text-sm text-amber-800 mt-1">Dalam 3 hari ke depan diprediksi curah hujan tinggi di wilayah Anda. Siapkan saluran drainase dan lindungi tanaman muda.</p>
        </div>
    </div>
</div>
@endsection
