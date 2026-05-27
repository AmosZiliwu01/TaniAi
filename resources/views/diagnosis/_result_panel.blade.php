{{-- Reusable result panel: include as @include('diagnosis._result_panel', ['d' => $diagnosis]) --}}
@php
    $risk     = $d->risk_level ?? 'Sedang';
    $col      = $risk==='Tinggi' ? 'red' : ($risk==='Sedang' ? 'amber' : 'green');
    $hStatus  = $d->health_status ?? ($risk==='Tinggi' ? 'Kritis' : ($risk==='Sedang' ? 'Terancam' : 'Sehat'));
    $causes   = is_array($d->causes)   ? $d->causes   : (json_decode($d->causes ?? '[]', true) ?: []);
    $solutions= is_array($d->solutions) ? $d->solutions : (json_decode($d->solutions ?? '[]', true) ?: ($d->recommendations ?? []));
    $prevention=is_array($d->prevention)?$d->prevention:(json_decode($d->prevention??'[]',true)?:[]);
    $confidence = (int)($d->confidence ?? 0);
    $pct = $risk==='Tinggi' ? 88 : ($risk==='Sedang' ? 55 : 22);
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-1">Kemungkinan Penyakit</div>
            <h3 class="text-xl font-extrabold leading-tight">{{ $d->disease }}</h3>
            <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="badge text-xs {{ $risk==='Tinggi'?'bg-red-100 text-red-700':($risk==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                    Risiko {{ $risk }}
                </span>
                @if($d->plant_part)
                    <span class="badge badge-slate text-xs">
                        <i data-lucide="leaf" class="w-3 h-3"></i> {{ ucfirst($d->plant_part) }}
                    </span>
                @endif
                <span class="badge badge-slate text-xs">
                    <i data-lucide="sprout" class="w-3 h-3"></i> {{ $d->crop ?? '-' }}
                </span>
            </div>
        </div>
        <div class="text-right shrink-0">
            <div class="text-3xl font-extrabold {{ $col==='red'?'text-red-600':($col==='amber'?'text-amber-600':'text-green-600') }}">{{ $confidence }}%</div>
            <div class="text-xs text-ink-400">keyakinan AI</div>
        </div>
    </div>

    {{-- Confidence bar --}}
    <div>
        <div class="text-xs font-semibold text-ink-600 mb-1">Tingkat Keparahan</div>
        <div class="h-2.5 rounded-full bg-ink-100 overflow-hidden">
            <div class="h-full rounded-full {{ $col==='red'?'bg-red-500':($col==='amber'?'bg-amber-500':'bg-green-500') }} transition-all"
                style="width:{{ $pct }}%"></div>
        </div>
        <div class="flex justify-between text-[10px] text-ink-400 mt-1">
            <span>Ringan</span><span>Sedang</span><span>Parah</span>
        </div>
    </div>

    {{-- Description --}}
    <div class="p-3 rounded-xl bg-ink-50">
        <div class="text-xs font-semibold text-ink-600 mb-1">Deskripsi</div>
        <p class="text-sm text-ink-700 leading-relaxed">{{ $d->description }}</p>
    </div>

    {{-- Causes --}}
    @if(count($causes))
    <div>
        <div class="text-xs font-semibold text-ink-600 mb-2">Penyebab</div>
        <ul class="space-y-1">
            @foreach($causes as $c)
                <li class="flex items-start gap-2 text-sm text-ink-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $col }}-400 shrink-0 mt-2"></span>
                    {{ $c }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Solutions --}}
    @if(count($solutions))
    <div>
        <div class="text-xs font-semibold text-ink-600 mb-2">Solusi</div>
        <ul class="space-y-1.5">
            @foreach(array_slice($solutions,0,4) as $s)
                <li class="flex items-start gap-2 text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-brand-600 shrink-0 mt-0.5"></i>
                    <span class="text-ink-700">{{ $s }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Prevention --}}
    @if(count($prevention))
    <div>
        <div class="text-xs font-semibold text-ink-600 mb-2">Pencegahan</div>
        <ul class="space-y-1.5">
            @foreach($prevention as $p)
                <li class="flex items-start gap-2 text-sm">
                    <i data-lucide="shield" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5"></i>
                    <span class="text-ink-700">{{ $p }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Action buttons --}}
    <div class="border-t border-ink-100 pt-3 flex gap-2 flex-wrap">
        <a href="{{ route('diagnosis.show', $d) }}" class="btn-primary text-sm flex-1">
            <i data-lucide="eye" class="w-4 h-4"></i> Detail Lengkap
        </a>
        <a href="{{ route('chat.index') }}?crop={{ urlencode($d->crop) }}" class="btn-outline text-sm">
            <i data-lucide="message-circle" class="w-4 h-4"></i> Tanya AI
        </a>
    </div>
</div>
