@extends('layouts.app')
@section('title','Hasil Diagnosa')
@section('content')

<div class="mb-4 flex items-center gap-2 text-sm">
    <a href="{{ route('diagnosis.index') }}" class="flex items-center gap-1 text-brand-700 font-semibold hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>
    <span class="text-ink-300">/</span>
    <span class="text-ink-400">Hasil Diagnosa</span>
</div>

@php
    $risk     = $diagnosis->risk_level ?? 'Sedang';
    $col      = $risk==='Tinggi' ? 'red' : ($risk==='Sedang' ? 'amber' : 'green');
    $causes   = is_array($diagnosis->causes)    ? $diagnosis->causes    : (json_decode($diagnosis->causes    ?? '[]', true) ?: []);
    $solutions= is_array($diagnosis->solutions)  ? $diagnosis->solutions  : (json_decode($diagnosis->solutions ?? '[]', true) ?: ($diagnosis->recommendations ?? []));
    $prevention=is_array($diagnosis->prevention) ? $diagnosis->prevention : (json_decode($diagnosis->prevention??'[]',true)?:[]);
    $recs     = is_array($diagnosis->recommendations) ? $diagnosis->recommendations : (json_decode($diagnosis->recommendations??'[]',true)?:[]);
@endphp

<div class="grid lg:grid-cols-3 gap-5">

    {{-- Main --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-1">Kemungkinan Penyakit</div>
                    <h2 class="text-2xl font-extrabold">{{ $diagnosis->disease }}</h2>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="badge text-xs {{ $risk==='Tinggi'?'bg-red-100 text-red-700':($risk==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                            Risiko {{ $risk }}
                        </span>
                        @if($diagnosis->plant_part)
                            <span class="badge badge-slate text-xs">{{ ucfirst($diagnosis->plant_part) }}</span>
                        @endif
                        @if($diagnosis->health_status)
                            <span class="badge {{ $diagnosis->health_status==='Kritis'?'bg-red-100 text-red-700':($diagnosis->health_status==='Terancam'?'bg-amber-100 text-amber-700':'badge-green') }} text-xs">
                                Status: {{ $diagnosis->health_status }}
                            </span>
                        @endif
                        <span class="badge badge-slate text-xs"><i data-lucide="sprout" class="w-3 h-3"></i> {{ $diagnosis->crop }}</span>
                        <span class="text-xs text-ink-400">{{ $diagnosis->created_at->isoFormat('D MMM Y, HH:mm') }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-extrabold text-{{ $col }}-600">{{ (int)$diagnosis->confidence }}%</div>
                    <div class="text-xs text-ink-400">keyakinan AI</div>
                </div>
            </div>

            {{-- Image --}}
            @if($diagnosis->image_path)
            <div class="mb-4">
                <img src="{{ Storage::url($diagnosis->image_path) }}"
                    alt="Foto diagnosa"
                    class="w-full max-h-72 object-cover rounded-2xl border border-ink-100 cursor-pointer"
                    loading="lazy"
                    onclick="this.style.maxHeight=this.style.maxHeight==='none'?'18rem':'none'"
                    onerror="this.parentElement.style.display='none'">
                <p class="text-xs text-ink-400 text-center mt-1">Klik foto untuk zoom</p>
            </div>
            @endif

            <p class="text-sm text-ink-700 leading-relaxed">{{ $diagnosis->description }}</p>
        </div>

        {{-- Causes, Solutions, Prevention in grid --}}
        <div class="grid sm:grid-cols-3 gap-4">
            @if(count($causes))
            <div class="card p-4">
                <div class="font-bold text-sm mb-3 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-{{ $col }}-600"></i> Penyebab
                </div>
                <ul class="space-y-2">
                    @foreach($causes as $c)
                        <li class="text-xs text-ink-700 flex items-start gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $col }}-400 shrink-0 mt-1.5"></span>
                            {{ $c }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(count($solutions))
            <div class="card p-4">
                <div class="font-bold text-sm mb-3 flex items-center gap-2">
                    <i data-lucide="stethoscope" class="w-4 h-4 text-brand-600"></i> Solusi
                </div>
                <ul class="space-y-2">
                    @foreach($solutions as $s)
                        <li class="text-xs text-ink-700 flex items-start gap-1.5">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-brand-500 shrink-0 mt-0.5"></i>
                            {{ $s }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(count($prevention))
            <div class="card p-4">
                <div class="font-bold text-sm mb-3 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i> Pencegahan
                </div>
                <ul class="space-y-2">
                    @foreach($prevention as $p)
                        <li class="text-xs text-ink-700 flex items-start gap-1.5">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-blue-500 shrink-0 mt-0.5"></i>
                            {{ $p }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Recommendations --}}
        @if(count($recs))
        <div class="card p-5">
            <div class="font-bold mb-3 flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-brand-600"></i> Tindakan Awal yang Disarankan
            </div>
            <ol class="space-y-2">
                @foreach($recs as $i => $rec)
                    <li class="flex gap-3 p-3 rounded-xl bg-brand-50 border border-brand-100">
                        <div class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs font-bold grid place-items-center shrink-0">{{ $i+1 }}</div>
                        <span class="text-sm text-ink-800 leading-relaxed">{{ $rec }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        @php $pct = $risk==='Tinggi'?88:($risk==='Sedang'?55:22); @endphp
        <div class="card p-5">
            <div class="font-bold mb-3">Tingkat Risiko</div>
            <div class="text-3xl font-extrabold text-{{ $col }}-600 mb-1">{{ $risk }}</div>
            <div class="text-xs text-ink-500 mb-3">{{ $risk==='Tinggi'?'Tangani segera':($risk==='Sedang'?'Perlu perhatian':'Pantau rutin') }}</div>
            <div class="h-2.5 rounded-full bg-ink-100 overflow-hidden">
                <div class="h-full rounded-full bg-{{ $col }}-500" style="width:{{ $pct }}%"></div>
            </div>
        </div>

        <div class="card p-5">
            <div class="font-bold mb-3">Detail</div>
            <div class="divide-y divide-ink-100 text-sm">
                @foreach([
                    ['Tanaman', $diagnosis->crop],
                    ['Keyakinan', (int)$diagnosis->confidence.'%'],
                    ['Status', $diagnosis->health_status ?? '-'],
                    ['Bagian', ucfirst($diagnosis->plant_part ?? '-')],
                    ['Dianalisa', $diagnosis->created_at->diffForHumans()],
                ] as [$k,$v])
                    <div class="flex justify-between py-2">
                        <span class="text-ink-500">{{ $k }}</span>
                        <span class="font-semibold">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5 space-y-2">
            <div class="font-bold mb-2">Langkah Lanjutan</div>
            <a href="{{ route('chat.index') }}?crop={{ urlencode($diagnosis->crop) }}"
                class="btn-outline w-full text-sm justify-start">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI lebih lanjut
            </a>
            <a href="{{ route('diagnosis.index') }}" class="btn-outline w-full text-sm justify-start">
                <i data-lucide="scan-line" class="w-4 h-4"></i> Diagnosa ulang
            </a>
            <form method="POST" action="{{ route('diagnosis.destroy', $diagnosis) }}">@csrf @method('DELETE')
                <button onclick="return confirm('Hapus riwayat ini?')"
                    class="w-full flex items-center justify-start gap-2 px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition text-sm font-semibold">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Riwayat
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
