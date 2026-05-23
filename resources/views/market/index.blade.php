@extends('layouts.app')
@section('title','Harga Pasar')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Harga Pasar</h1>
        <p class="text-ink-500 mt-1">Pantau harga komoditas di pasar terdekat.</p>
    </div>
    <form method="GET" class="flex gap-2">
        <select name="region" class="input" onchange="this.form.submit()">
            <option value="">Semua Wilayah</option>
            @foreach($regions as $r)
                <option value="{{ $r }}" {{ request('region')==$r?'selected':'' }}>{{ $r }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @foreach($prices->take(6) as $p)
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center"><i data-lucide="sprout" class="w-5 h-5"></i></div>
                    <div>
                        <div class="font-bold">{{ $p->commodity }}</div>
                        <div class="text-xs text-ink-500">{{ $p->region }}</div>
                    </div>
                </div>
                <span class="badge {{ $p->change_percent>=0 ? 'bg-brand-100 text-brand-700':'bg-red-100 text-red-700' }}">
                    {{ $p->change_percent>=0?'▲':'▼' }} {{ number_format(abs($p->change_percent),1) }}%
                </span>
            </div>
            <div class="mt-3 text-2xl font-extrabold">Rp {{ number_format($p->price,0,',','.') }}<span class="text-sm text-ink-500 font-medium">/{{ $p->unit }}</span></div>
            <div class="text-xs text-ink-500 mt-1">{{ $p->recorded_at->diffForHumans() }}</div>
        </div>
    @endforeach
</div>

<div class="card p-5">
    <div class="font-bold mb-3">Semua Harga</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr><th class="py-2">Komoditas</th><th>Wilayah</th><th>Harga</th><th>Perubahan</th><th>Update</th></tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($prices as $p)
                    <tr>
                        <td class="py-2.5 font-semibold">{{ $p->commodity }}</td>
                        <td>{{ $p->region }}</td>
                        <td>Rp {{ number_format($p->price,0,',','.') }}/{{ $p->unit }}</td>
                        <td class="{{ $p->change_percent>=0?'text-brand-700':'text-red-600' }} font-semibold">{{ $p->change_percent>=0?'▲':'▼' }} {{ number_format(abs($p->change_percent),1) }}%</td>
                        <td class="text-ink-500">{{ $p->recorded_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
