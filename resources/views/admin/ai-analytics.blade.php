@extends('layouts.admin')
@section('title','AI Analytics')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">AI Analytics</h1>

<div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="stat-card"><div class="text-xs text-ink-500 font-semibold uppercase">Total Chat AI</div><div class="text-3xl font-extrabold">{{ $totals['chat'] }}</div></div>
    <div class="stat-card"><div class="text-xs text-ink-500 font-semibold uppercase">Total Diagnosa AI</div><div class="text-3xl font-extrabold">{{ $totals['diagnoses'] }}</div></div>
</div>

<div class="card p-6">
    <div class="font-bold mb-3">Penggunaan AI (per hari)</div>
    <div class="flex items-end gap-2 h-48">
        @php $max = $byDay->max('c') ?: 1; @endphp
        @foreach($byDay as $d)
            <div class="flex-1 flex flex-col items-center justify-end gap-1">
                <div class="w-full bg-brand-gradient rounded-t-lg" style="height: {{ ($d->c/$max)*100 }}%; min-height: 4px;"></div>
                <div class="text-[10px] text-ink-500">{{ \Carbon\Carbon::parse($d->d)->format('d/m') }}</div>
            </div>
        @endforeach
        @if($byDay->isEmpty())
            <div class="flex-1 text-center text-sm text-ink-500">Belum ada data.</div>
        @endif
    </div>
</div>
@endsection
