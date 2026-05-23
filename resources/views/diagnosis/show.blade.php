@extends('layouts.app')
@section('title','Hasil Diagnosa')
@section('content')

<div class="mb-4">
    <a href="{{ route('diagnosis.index') }}" class="text-sm text-brand-700 font-semibold">← Kembali</a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="card p-6 lg:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs text-ink-500">Hasil Diagnosa AI</div>
                <h2 class="text-2xl font-extrabold mt-1">{{ $diagnosis->disease }}</h2>
                <div class="text-sm text-ink-500 mt-1">Tanaman: {{ $diagnosis->crop }}</div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-extrabold text-brand-700">{{ (int)$diagnosis->confidence }}%</div>
                <div class="text-xs text-ink-500">Tingkat kepercayaan</div>
            </div>
        </div>

        @if($diagnosis->image_path)
            <img src="{{ asset('storage/'.$diagnosis->image_path) }}" class="mt-5 rounded-2xl w-full max-h-80 object-cover">
        @else
            <div class="mt-5 h-64 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-50 grid place-items-center text-brand-700">
                <i data-lucide="leaf" class="w-20 h-20"></i>
            </div>
        @endif

        <div class="mt-6">
            <div class="font-bold mb-2">Deskripsi</div>
            <p class="text-sm text-ink-600">{{ $diagnosis->description }}</p>
        </div>

        <div class="mt-6">
            <div class="font-bold mb-2">Rekomendasi AI</div>
            <ul class="space-y-2">
                @foreach((array)$diagnosis->recommendations as $r)
                    <li class="flex gap-3 text-sm p-3 rounded-xl bg-brand-50 border border-brand-200">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-600 shrink-0"></i>
                        <span class="text-brand-900">{{ $r }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <div class="font-bold mb-3">Tingkat Risiko</div>
            @php $risk=$diagnosis->risk_level; $col=$risk==='Tinggi'?'red':($risk==='Sedang'?'amber':'brand'); @endphp
            <div class="text-3xl font-extrabold text-{{ $col }}-600">{{ $risk }}</div>
            <div class="mt-3 h-2 rounded-full bg-ink-200 overflow-hidden">
                <div class="h-full bg-{{ $col }}-500" style="width: {{ $risk==='Tinggi'?90:($risk==='Sedang'?60:30) }}%"></div>
            </div>
        </div>

        <div class="card p-5">
            <div class="font-bold mb-3">Faktor Penyebab</div>
            @foreach(['Kelembapan: Tinggi','pH Tanah: Netral','Curah Hujan: Tinggi','Sirkulasi Udara: Sedang'] as $f)
                <div class="flex justify-between text-sm py-1.5 border-b border-ink-200 last:border-0"><span class="text-ink-600">{{ explode(':',$f)[0] }}</span><span class="font-semibold">{{ trim(explode(':',$f)[1]) }}</span></div>
            @endforeach
        </div>

        <div class="card p-5">
            <div class="font-bold mb-3">Aksi Cepat</div>
            <div class="space-y-2">
                <a href="{{ route('chat.index') }}" class="btn-outline w-full"><i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI lebih lanjut</a>
                <a href="{{ route('recommendations.index') }}" class="btn-outline w-full"><i data-lucide="shopping-bag" class="w-4 h-4"></i> Lihat Toko Rekomendasi</a>
            </div>
        </div>
    </div>
</div>
@endsection
