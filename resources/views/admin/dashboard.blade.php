@extends('layouts.admin')
@section('title','Admin Dashboard')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Dashboard Admin</h1>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Total Pengguna',$stats['users'],'users'],['Total Diagnosa',$stats['diagnoses'],'scan-line'],['AI Chat Calls',$stats['ai_calls'],'brain-circuit'],['Postingan Komunitas',$stats['posts'],'message-square']] as [$l,$v,$i])
        <div class="stat-card">
            <div class="flex justify-between items-center">
                <div class="text-xs text-ink-500 font-semibold uppercase">{{ $l }}</div>
                <div class="w-9 h-9 rounded-lg bg-brand-100 text-brand-700 grid place-items-center"><i data-lucide="{{ $i }}" class="w-4 h-4"></i></div>
            </div>
            <div class="text-3xl font-extrabold">{{ $v }}</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <div class="font-bold mb-3">Pengguna Terbaru</div>
        <div class="divide-y divide-ink-200">
            @foreach($recentUsers as $u)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm">{{ substr($u->name,0,1) }}</div>
                    <div class="flex-1"><div class="font-semibold text-sm">{{ $u->name }}</div><div class="text-xs text-ink-500">{{ $u->email }}</div></div>
                    <span class="badge {{ $u->role==='admin'?'bg-purple-100 text-purple-700':'badge-slate' }}">{{ $u->role }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card p-5">
        <div class="font-bold mb-3">Diagnosa Terbaru</div>
        <div class="divide-y divide-ink-200">
            @foreach($recentDiagnoses as $d)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="w-9 h-9 rounded-lg bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="leaf" class="w-4 h-4"></i></div>
                    <div class="flex-1"><div class="font-semibold text-sm">{{ $d->disease }}</div><div class="text-xs text-ink-500">{{ $d->user->name ?? '-' }} · {{ $d->crop }}</div></div>
                    <span class="badge-green">{{ (int)$d->confidence }}%</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
