@extends('layouts.app')
@section('title','Panduan Budidaya')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Panduan Budidaya</h1>
    <p class="text-ink-500 mt-1">Langkah lengkap budidaya tanaman.</p>
</div>

<div x-data="{tab:'Padi'}">
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
        @foreach($crops as $c)
            <button @click="tab='{{ $c }}'" :class="tab==='{{ $c }}' ? 'bg-brand-gradient text-white shadow-glow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50'" class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition">{{ $c }}</button>
        @endforeach
    </div>

    @foreach($crops as $c)
        <div x-show="tab==='{{ $c }}'" x-cloak class="card p-6 mt-4">
            <h2 class="text-xl font-bold">Tahapan Budidaya {{ $c }}</h2>
            <div class="mt-5 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(['Persiapan Lahan','Persemaian','Penanaman','Pemeliharaan'] as $i => $step)
                    <div class="p-4 rounded-2xl bg-ink-50">
                        <div class="w-8 h-8 rounded-full bg-brand-gradient text-white grid place-items-center font-bold">{{ $i+1 }}</div>
                        <div class="font-bold mt-3">{{ $step }}</div>
                        <p class="text-xs text-ink-500 mt-1">Lakukan secara teliti agar hasil maksimal.</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 grid sm:grid-cols-3 gap-3 text-sm">
                <div class="p-4 rounded-xl bg-brand-50"><div class="font-bold">Pupuk</div><div class="text-ink-600 mt-1">UREA, NPK 16-16-16, organik</div></div>
                <div class="p-4 rounded-xl bg-amber-50"><div class="font-bold">Penyakit Umum</div><div class="text-ink-600 mt-1">Hawar daun, wereng, bercak daun</div></div>
                <div class="p-4 rounded-xl bg-blue-50"><div class="font-bold">Panen</div><div class="text-ink-600 mt-1">90-120 hari setelah tanam</div></div>
            </div>
        </div>
    @endforeach
</div>
@endsection
