@extends('layouts.admin')
@section('title','AI Analytics')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">AI Analytics</h1>
<div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="stat-card">
        <div class="text-xs text-ink-500 font-semibold uppercase mb-2">Total Chat AI</div>
        <div class="text-4xl font-extrabold text-brand-700">{{ $totals['chat'] }}</div>
    </div>
    <div class="stat-card">
        <div class="text-xs text-ink-500 font-semibold uppercase mb-2">Total Diagnosa AI</div>
        <div class="text-4xl font-extrabold text-brand-700">{{ $totals['diagnoses'] }}</div>
    </div>
</div>
<div class="card p-6">
    <div class="font-bold mb-4">Penggunaan AI Chat (14 hari terakhir)</div>
    @if($byDay->isEmpty())
        <div class="h-40 grid place-items-center text-ink-400 text-sm">Belum ada data.</div>
    @else
        @php $max = $byDay->max('c') ?: 1; @endphp
        <div class="flex items-end gap-1.5 h-40">
            @foreach($byDay as $d)
                <div class="flex-1 flex flex-col items-center justify-end gap-1 group">
                    <div class="text-[9px] text-ink-400 opacity-0 group-hover:opacity-100 font-semibold">{{ $d->c }}</div>
                    <div class="w-full bg-brand-gradient rounded-t-lg"
                        style="height:{{ max(($d->c/$max)*136,4) }}px"></div>
                    <div class="text-[9px] text-ink-400">{{ \Carbon\Carbon::parse($d->d)->format('d/m') }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
