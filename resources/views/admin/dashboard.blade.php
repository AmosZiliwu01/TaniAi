@extends('layouts.admin')
@section('title','Admin Dashboard')
@section('content')

<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Dashboard Admin</h1>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Pengguna',      $stats['users'],     'users',          'brand'],
        ['Total Diagnosa',      $stats['diagnoses'], 'scan-line',      'blue'],
        ['AI Chat Calls',       $stats['ai_calls'],  'message-circle', 'purple'],
        ['Postingan Komunitas', $stats['posts'],     'message-square', 'green'],
    ] as [$l,$v,$i,$c])
        <div class="stat-card">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs text-ink-500 font-semibold uppercase tracking-wide">{{ $l }}</div>
                <div class="w-9 h-9 rounded-lg bg-{{ $c }}-100 text-{{ $c }}-700 grid place-items-center">
                    <i data-lucide="{{ $i }}" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold">{{ $v }}</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Recent users --}}
    <div class="card p-5">
        <div class="font-bold mb-4 flex items-center justify-between">
            <span>Pengguna Terbaru</span>
            <a href="{{ route('admin.users') }}" class="text-xs text-brand-700 font-semibold hover:underline">Semua →</a>
        </div>
        <div class="divide-y divide-ink-100">
            @foreach($recentUsers as $u)
                @php
                    $grads = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600'];
                    $gi = abs(crc32($u->name)) % 5;
                @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $grads[$gi] }} grid place-items-center text-white font-bold text-sm shrink-0">
                        {{ strtoupper(substr($u->name,0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $u->name }}</div>
                        <div class="text-xs text-ink-400 truncate">{{ $u->email }}</div>
                    </div>
                    <span class="badge text-xs {{ $u->role==='admin'?'bg-purple-100 text-purple-700':($u->role==='banned'?'bg-red-100 text-red-700':'badge-slate') }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Recent diagnoses --}}
    <div class="card p-5">
        <div class="font-bold mb-4 flex items-center justify-between">
            <span>Diagnosa Terbaru</span>
            <a href="{{ route('admin.logs') }}" class="text-xs text-brand-700 font-semibold hover:underline">Semua →</a>
        </div>
        <div class="divide-y divide-ink-100">
            @foreach($recentDiagnoses as $d)
                <div class="flex items-center gap-3 py-2.5">
                    @if($d->image_path)
                        <img src="{{ Storage::url($d->image_path) }}"
                            class="w-9 h-9 rounded-xl object-cover border border-ink-100 shrink-0"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                        <div style="display:none"
                            class="w-9 h-9 rounded-lg bg-brand-100 text-brand-700 place-items-center shrink-0">
                            <i data-lucide="leaf" class="w-4 h-4"></i>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-lg bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                            <i data-lucide="leaf" class="w-4 h-4"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $d->disease }}</div>
                        <div class="text-xs text-ink-400">{{ $d->user->name ?? '-' }} · {{ $d->crop }}</div>
                    </div>
                    <span class="badge text-xs {{ $d->risk_level==='Tinggi'?'bg-red-100 text-red-700':($d->risk_level==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                        {{ (int)$d->confidence }}%
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
