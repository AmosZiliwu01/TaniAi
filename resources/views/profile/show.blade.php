@extends('layouts.app')
@section('title','Profil Saya')
@section('content')
@php $u = auth()->user(); @endphp

<div class="card p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5 mb-6">
    {{-- Letter avatar --}}
    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br {{ $u->avatar_gradient }} grid place-items-center text-white text-4xl font-extrabold shadow-lg shrink-0 select-none">
        {{ $u->initial }}
    </div>

    <div class="flex-1 min-w-0">
        <h1 class="text-2xl font-extrabold truncate">{{ $u->name }}</h1>
        <p class="text-ink-500 text-sm mt-0.5">{{ $u->email }}</p>
        <div class="flex flex-wrap gap-2 mt-3">
            <span class="badge-green text-xs">{{ $u->farmer_type ?? 'Petani' }}</span>
            @if($u->location)
                <span class="badge badge-slate text-xs">
                    <i data-lucide="map-pin" class="w-3 h-3"></i> {{ $u->location }}
                </span>
            @else
                <a href="{{ route('profile.settings') }}"
                    class="badge text-xs bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 transition">
                    <i data-lucide="map-pin" class="w-3 h-3"></i> Atur lokasi
                </a>
            @endif
            @if($u->phone)
                <span class="badge badge-slate text-xs">
                    <i data-lucide="phone" class="w-3 h-3"></i> {{ $u->phone }}
                </span>
            @endif
            @if($u->isAdmin())
                <span class="badge bg-purple-100 text-purple-700 text-xs">Administrator</span>
            @endif
        </div>
    </div>

    <a href="{{ route('profile.settings') }}" class="btn-primary shrink-0">
        <i data-lucide="settings" class="w-4 h-4"></i> Edit Profil
    </a>
</div>

{{-- Stats --}}
@php
    $diagCount   = \App\Models\Diagnosis::where('user_id',$u->id)->count();
    $postCount   = \App\Models\CommunityPost::where('user_id',$u->id)->count();
    try { $recCount = \App\Models\CropRecord::where('user_id',$u->id)->count(); }
    catch (\Throwable $e) { $recCount = 0; }
@endphp
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        [$diagCount,  'scan-line',      'Total Diagnosa', 'brand'],
        [$postCount,  'message-square', 'Postingan',      'blue'],
        [$recCount,   'clipboard-list', 'Lahan Tercatat', 'green'],
    ] as [$v,$ico,$l,$col])
        <div class="stat-card text-center py-4">
            <i data-lucide="{{ $ico }}" class="w-5 h-5 mx-auto text-{{ $col }}-600 mb-1"></i>
            <div class="text-2xl font-extrabold text-{{ $col }}-700">{{ $v }}</div>
            <div class="text-xs text-ink-500 font-semibold">{{ $l }}</div>
        </div>
    @endforeach
</div>

{{-- Recent diagnoses --}}
@php $recent = \App\Models\Diagnosis::where('user_id',$u->id)->latest()->limit(5)->get(); @endphp
@if($recent->count())
<div class="card p-5 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="scan-line" class="w-4 h-4 text-brand-600"></i> Diagnosa Terbaru
        </div>
        <a href="{{ route('diagnosis.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Lihat semua →</a>
    </div>
    <div class="divide-y divide-ink-100">
        @foreach($recent as $d)
            <a href="{{ route('diagnosis.show',$d) }}"
                class="flex items-center gap-3 py-2.5 hover:bg-ink-50 rounded-xl px-2 -mx-2 transition">
                @if($d->image_path)
                    <img src="{{ Storage::url($d->image_path) }}"
                        class="w-10 h-10 rounded-xl object-cover shrink-0 border border-ink-100"
                        loading="lazy"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                    <div style="display:none"
                        class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 place-items-center shrink-0">
                        <i data-lucide="leaf" class="w-4 h-4"></i>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                        <i data-lucide="leaf" class="w-4 h-4"></i>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">{{ $d->disease }}</div>
                    <div class="text-xs text-ink-500">{{ $d->crop }} · {{ $d->created_at->diffForHumans() }}</div>
                </div>
                <span class="badge text-xs shrink-0 {{ $d->risk_level==='Tinggi'?'bg-red-100 text-red-700':($d->risk_level==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                    {{ $d->risk_level }}
                </span>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Recent posts --}}
@php $posts = \App\Models\CommunityPost::where('user_id',$u->id)->latest()->limit(3)->get(); @endphp
@if($posts->count())
<div class="card p-5">
    <div class="flex items-center justify-between mb-3">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4 text-brand-600"></i> Postingan Saya
        </div>
        <a href="{{ route('community.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Semua →</a>
    </div>
    <div class="space-y-2">
        @foreach($posts as $p)
            <div class="p-3 rounded-xl bg-ink-50 hover:bg-ink-100 transition">
                <div class="font-semibold text-sm">{{ $p->title }}</div>
                <div class="text-xs text-ink-400 mt-0.5 flex gap-2">
                    <span>{{ $p->created_at->diffForHumans() }}</span>
                    <span>·</span><span>{{ $p->likes }} suka</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
