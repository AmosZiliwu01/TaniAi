@extends('layouts.app')
@section('title','Profil')
@section('content')
@php $u = auth()->user(); @endphp
<div class="card p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
    <div class="w-24 h-24 rounded-2xl bg-brand-gradient grid place-items-center text-white text-3xl font-bold shadow-glow">{{ strtoupper(substr($u->name,0,1)) }}</div>
    <div class="flex-1">
        <h1 class="text-2xl font-extrabold">{{ $u->name }}</h1>
        <p class="text-ink-500">{{ $u->email }}</p>
        <div class="flex flex-wrap gap-2 mt-3">
            <span class="badge-green">{{ $u->farmer_type ?? 'Petani' }}</span>
            @if($u->location)<span class="badge-slate"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $u->location }}</span>@endif
            @if($u->isAdmin())<span class="badge bg-purple-100 text-purple-700">Administrator</span>@endif
        </div>
    </div>
    <a href="{{ route('profile.settings') }}" class="btn-primary"><i data-lucide="settings" class="w-4 h-4"></i> Edit Profil</a>
</div>

<div class="grid sm:grid-cols-3 gap-4 mt-6">
    @php
        $diagCount = \App\Models\Diagnosis::where('user_id',$u->id)->count();
        $postCount = \App\Models\CommunityPost::where('user_id',$u->id)->count();
        $recordCount = \App\Models\CropRecord::where('user_id',$u->id)->count();
    @endphp
    <div class="stat-card"><div class="text-xs text-ink-500 font-semibold">Total Diagnosa</div><div class="text-2xl font-extrabold">{{ $diagCount }}</div></div>
    <div class="stat-card"><div class="text-xs text-ink-500 font-semibold">Postingan</div><div class="text-2xl font-extrabold">{{ $postCount }}</div></div>
    <div class="stat-card"><div class="text-xs text-ink-500 font-semibold">Lahan Terdaftar</div><div class="text-2xl font-extrabold">{{ $recordCount }}</div></div>
</div>
@endsection
