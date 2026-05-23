@extends('layouts.app')
@section('title','Toko & Rekomendasi')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Toko & Rekomendasi</h1>
    <p class="text-ink-500 mt-1">Produk pertanian terbaik untuk Anda.</p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach($items as $r)
        @php $col=$r->priority==='high'?'red':($r->priority==='medium'?'amber':'brand'); @endphp
        <div class="card p-5">
            <span class="badge bg-{{ $col }}-100 text-{{ $col }}-700">Prioritas {{ ucfirst($r->priority) }}</span>
            <div class="font-bold mt-3">{{ $r->title }}</div>
            <p class="text-sm text-ink-600 mt-1">{{ $r->content }}</p>
            @if($r->crop)<div class="text-xs text-ink-500 mt-2">Untuk: {{ $r->crop }}</div>@endif
        </div>
    @endforeach
</div>

<div class="mb-3 font-bold">Produk Rekomendasi</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach([['Pupuk UREA','50 kg','Rp 285.000'],['Fungisida Propineb','70WP','Rp 120.000'],['Pupuk NPK','16-16-16','Rp 310.000'],['Insektisida','500ml','Rp 95.000'],['Pupuk Organik','25 kg','Rp 75.000'],['Pupuk Cair','1L','Rp 65.000'],['Mulsa Plastik','5kg','Rp 180.000'],['ZPT','250ml','Rp 55.000']] as [$n,$desc,$price])
        <div class="card p-4">
            <div class="h-28 rounded-xl bg-gradient-to-br from-brand-100 to-brand-50 grid place-items-center text-brand-700 mb-3"><i data-lucide="package" class="w-10 h-10"></i></div>
            <div class="font-bold text-sm">{{ $n }}</div>
            <div class="text-xs text-ink-500">{{ $desc }}</div>
            <div class="font-extrabold text-brand-700 mt-2">{{ $price }}</div>
            <button class="btn-primary w-full mt-3 text-sm py-2">Beli</button>
        </div>
    @endforeach
</div>
@endsection
