@extends('layouts.app')
@section('title','Hasil Diagnosa')
@section('content')

<div class="mb-4 flex items-center gap-3">
    <a href="{{ route('diagnosis.index') }}" class="flex items-center gap-1 text-sm text-brand-700 font-semibold hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>
    <span class="text-ink-300">/</span>
    <span class="text-sm text-ink-500">Hasil Diagnosa</span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Main Result Card --}}
    <div class="card p-6 lg:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide mb-1">Hasil Diagnosa AI</div>
                <h2 class="text-2xl font-extrabold">{{ $diagnosis->disease }}</h2>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span class="badge badge-slate">
                        <i data-lucide="sprout" class="w-3 h-3"></i> {{ $diagnosis->crop }}
                    </span>
                    <span class="badge {{ $diagnosis->risk_level==='Tinggi' ? 'bg-red-100 text-red-700' : ($diagnosis->risk_level==='Sedang' ? 'bg-amber-100 text-amber-700' : 'badge-green') }}">
                        Risiko {{ $diagnosis->risk_level }}
                    </span>
                    <span class="text-xs text-ink-500">{{ $diagnosis->created_at->isoFormat('D MMM Y, HH:mm') }}</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-4xl font-extrabold text-brand-700">{{ (int)$diagnosis->confidence }}%</div>
                <div class="text-xs text-ink-500 mt-0.5">Keyakinan AI</div>
            </div>
        </div>

        {{-- Gambar --}}
        @if($diagnosis->image_path)
            <div class="mt-5">
                <img src="{{ asset('storage/'.$diagnosis->image_path) }}"
                     class="rounded-2xl w-full max-h-80 object-cover cursor-pointer"
                     onclick="this.style.maxHeight=this.style.maxHeight==='none'?'20rem':'none'">
                <div class="text-xs text-ink-400 mt-1.5 text-center">Foto yang dianalisa · klik untuk zoom</div>
            </div>
        @else
            <div class="mt-5 h-40 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-50 grid place-items-center text-brand-700">
                <div class="text-center">
                    <i data-lucide="leaf" class="w-12 h-12 mx-auto"></i>
                    <div class="text-xs text-brand-600 mt-1">Tanpa foto</div>
                </div>
            </div>
        @endif

        {{-- Deskripsi --}}
        <div class="mt-6">
            <div class="font-bold mb-2">Deskripsi Penyakit</div>
            <p class="text-sm text-ink-600 leading-relaxed">{{ $diagnosis->description }}</p>
        </div>

        {{-- Rekomendasi --}}
        <div class="mt-6">
            <div class="font-bold mb-3 flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-brand-600"></i> Langkah Penanganan
            </div>
            <ul class="space-y-2">
                @foreach((array)$diagnosis->recommendations as $i => $r)
                    <li class="flex gap-3 p-3 rounded-xl bg-brand-50 border border-brand-100">
                        <div class="w-6 h-6 rounded-full bg-brand-gradient text-white text-xs grid place-items-center font-bold shrink-0">{{ $i+1 }}</div>
                        <span class="text-sm text-brand-900 leading-relaxed">{{ $r }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Risk Meter --}}
        <div class="card p-5">
            <div class="font-bold mb-3">Tingkat Risiko</div>
            @php
                $risk = $diagnosis->risk_level;
                $col  = $risk==='Tinggi' ? 'red' : ($risk==='Sedang' ? 'amber' : 'brand');
                $pct  = $risk==='Tinggi' ? 90 : ($risk==='Sedang' ? 55 : 25);
            @endphp
            <div class="flex items-end gap-3 mb-3">
                <div class="text-4xl font-extrabold text-{{ $col }}-600">{{ $risk }}</div>
                <div class="text-sm text-ink-500 mb-1">{{ $risk==='Tinggi' ? 'Perlu segera ditangani' : ($risk==='Sedang' ? 'Perlu perhatian' : 'Dapat ditangani rutin') }}</div>
            </div>
            <div class="h-3 rounded-full bg-ink-100 overflow-hidden">
                <div class="h-full rounded-full bg-{{ $col }}-500 transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <div class="mt-2 text-xs text-ink-500 flex justify-between">
                <span>Rendah</span><span>Sedang</span><span>Tinggi</span>
            </div>
        </div>

        {{-- Info Diagnosa --}}
        <div class="card p-5">
            <div class="font-bold mb-3">Detail Diagnosa</div>
            <div class="space-y-2 text-sm divide-y divide-ink-200">
                <div class="flex justify-between py-1.5">
                    <span class="text-ink-500">Tanaman</span>
                    <span class="font-semibold">{{ $diagnosis->crop }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-ink-500">Kepercayaan</span>
                    <span class="font-semibold">{{ (int)$diagnosis->confidence }}%</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-ink-500">Risiko</span>
                    <span class="font-semibold text-{{ $col }}-600">{{ $risk }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-ink-500">Dianalisa</span>
                    <span class="font-semibold">{{ $diagnosis->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        {{-- Aksi Cepat --}}
        <div class="card p-5">
            <div class="font-bold mb-3">Aksi Selanjutnya</div>
            <div class="space-y-2">
                <a href="{{ route('chat.index') }}" class="btn-outline w-full text-sm">
                    <i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI lebih lanjut
                </a>
                <a href="{{ route('recommendations.index') }}" class="btn-outline w-full text-sm">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> Cari produk penanganan
                </a>
                <a href="{{ route('diagnosis.index') }}" class="btn-outline w-full text-sm">
                    <i data-lucide="scan-line" class="w-4 h-4"></i> Diagnosa ulang
                </a>
                <form method="POST" action="{{ route('diagnosis.destroy', $diagnosis) }}">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Hapus riwayat diagnosa ini?')"
                        class="btn w-full text-sm border border-red-200 text-red-600 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Riwayat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
