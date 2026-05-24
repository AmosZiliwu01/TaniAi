@extends('layouts.app')
@section('title','Profil Saya')
@section('content')
@php $u = auth()->user(); @endphp

<div class="card p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5 mb-6">
    {{-- Avatar --}}
    @if($u->avatar)
        <img src="{{ Storage::url($u->avatar) }}" class="w-24 h-24 rounded-2xl object-cover shadow border border-ink-200">
    @else
        <div class="w-24 h-24 rounded-2xl bg-brand-gradient grid place-items-center text-white text-3xl font-extrabold shadow-glow shrink-0">
            {{ strtoupper(substr($u->name, 0, 1)) }}
        </div>
    @endif

    <div class="flex-1">
        <h1 class="text-2xl font-extrabold">{{ $u->name }}</h1>
        <p class="text-ink-500 text-sm">{{ $u->email }}</p>
        <div class="flex flex-wrap gap-2 mt-3">
            <span class="badge-green">{{ $u->farmer_type ?? 'Petani' }}</span>
            @if($u->location)
                <span class="badge badge-slate">
                    <i data-lucide="map-pin" class="w-3 h-3"></i> {{ $u->location }}
                </span>
            @endif
            @if($u->phone)
                <span class="badge badge-slate">
                    <i data-lucide="phone" class="w-3 h-3"></i> {{ $u->phone }}
                </span>
            @endif
            @if($u->isAdmin())
                <span class="badge bg-purple-100 text-purple-700">Administrator</span>
            @endif
        </div>
    </div>

    <a href="{{ route('profile.settings') }}" class="btn-primary shrink-0">
        <i data-lucide="settings" class="w-4 h-4"></i> Edit Profil
    </a>
</div>

{{-- Stats --}}
@php
    $diagCount   = \App\Models\Diagnosis::where('user_id', $u->id)->count();
    $postCount   = \App\Models\CommunityPost::where('user_id', $u->id)->count();
    try { $recordCount = \App\Models\CropRecord::where('user_id', $u->id)->count(); }
    catch(\Throwable $e) { $recordCount = 0; }
@endphp
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card text-center">
        <div class="text-2xl font-extrabold text-brand-700">{{ $diagCount }}</div>
        <div class="text-xs text-ink-500 font-semibold">Total Diagnosa</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-2xl font-extrabold text-brand-700">{{ $postCount }}</div>
        <div class="text-xs text-ink-500 font-semibold">Postingan</div>
    </div>
    <div class="stat-card text-center">
        <div class="text-2xl font-extrabold text-brand-700">{{ $recordCount }}</div>
        <div class="text-xs text-ink-500 font-semibold">Lahan Tercatat</div>
    </div>
</div>

{{-- Recent Diagnoses --}}
@php $recentDiag = \App\Models\Diagnosis::where('user_id', $u->id)->latest()->limit(4)->get(); @endphp
@if($recentDiag->count())
<div class="card p-5 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="scan-line" class="w-4 h-4 text-brand-600"></i> Diagnosa Terbaru
        </div>
        <a href="{{ route('diagnosis.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Lihat semua →</a>
    </div>
    <div class="divide-y divide-ink-200">
        @foreach($recentDiag as $d)
            <a href="{{ route('diagnosis.show', $d) }}" class="flex items-center gap-3 py-2.5 hover:bg-ink-50 rounded-xl px-2 -mx-2 transition">
                <div class="w-10 h-10 rounded-xl {{ $d->risk_level === 'Tinggi' ? 'bg-red-100 text-red-700' : ($d->risk_level === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'bg-brand-100 text-brand-700') }} grid place-items-center shrink-0">
                    <i data-lucide="leaf" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">{{ $d->disease }}</div>
                    <div class="text-xs text-ink-500">{{ $d->crop }} · {{ $d->created_at->diffForHumans() }}</div>
                </div>
                <span class="badge text-xs {{ $d->risk_level === 'Tinggi' ? 'bg-red-100 text-red-700' : ($d->risk_level === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'badge-green') }}">
                    {{ $d->risk_level }}
                </span>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Posts --}}
@php $recentPosts = \App\Models\CommunityPost::where('user_id', $u->id)->latest()->limit(3)->get(); @endphp
@if($recentPosts->count())
<div class="card p-5">
    <div class="flex items-center justify-between mb-3">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4 text-brand-600"></i> Postingan Saya
        </div>
        <a href="{{ route('community.index') }}" class="text-xs text-brand-700 font-semibold hover:underline">Lihat semua →</a>
    </div>
    <div class="space-y-2">
        @foreach($recentPosts as $p)
            <div class="p-3 rounded-xl bg-ink-50">
                <div class="font-semibold text-sm">{{ $p->title }}</div>
                <div class="text-xs text-ink-500 mt-0.5">{{ $p->created_at->diffForHumans() }} · {{ $p->likes }} suka</div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
