@extends('layouts.app')
@section('title','Notifikasi')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Notifikasi</h1>
    <form method="POST" action="{{ route('notifications.read') }}">@csrf
        <button class="btn-outline"><i data-lucide="check" class="w-4 h-4"></i> Tandai semua dibaca</button>
    </form>
</div>

<div class="card divide-y divide-ink-200">
    @forelse($items as $n)
        @php
            $iconMap = ['warning'=>'alert-triangle','success'=>'check-circle','info'=>'info','error'=>'x-circle'];
            $colorMap = ['warning'=>'amber','success'=>'brand','info'=>'blue','error'=>'red'];
            $i = $iconMap[$n->type] ?? 'bell';
            $col = $colorMap[$n->type] ?? 'brand';
        @endphp
        <div class="p-4 flex items-start gap-4 {{ !$n->read_at ? 'bg-brand-50/40' : '' }}">
            <div class="w-10 h-10 rounded-xl bg-{{ $col }}-100 text-{{ $col }}-600 grid place-items-center"><i data-lucide="{{ $i }}" class="w-5 h-5"></i></div>
            <div class="flex-1">
                <div class="font-semibold">{{ $n->title }}</div>
                <div class="text-sm text-ink-600">{{ $n->message }}</div>
                <div class="text-xs text-ink-500 mt-1">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-sm text-ink-500">Belum ada notifikasi.</div>
    @endforelse
</div>
@endsection
