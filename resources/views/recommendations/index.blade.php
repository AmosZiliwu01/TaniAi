@extends('layouts.app')
@section('title','Toko & Rekomendasi')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Toko & Rekomendasi</h1>
    <p class="text-ink-500 mt-1">Produk pertanian pilihan untuk kebutuhan lahan Anda.</p>
</div>

@php
// Produk statis yang dikelola admin (bisa dipindah ke DB nanti)
$products = [
    ['Pupuk UREA Subsidi',     '50 kg',   285000,  'Pupuk nitrogen untuk pertumbuhan vegetatif. Cocok untuk semua tanaman pangan.', 'wa', '6281234567890', 'sprout'],
    ['Fungisida Propineb 70WP', '500 gr',  95000,   'Fungisida kontak untuk pencegahan hawar daun, bercak, dan embun tepung.', 'shopee', 'https://shopee.co.id', 'shield'],
    ['Pupuk NPK Mutiara 16-16-16', '50 kg', 310000, 'Pupuk majemuk seimbang untuk semua fase tanaman.', 'wa', '6281234567890', 'layers'],
    ['Insektisida Decis 25EC', '100 ml',   85000,   'Insektisida sistemik untuk pengendalian hama ulat, thrips, dan aphid.', 'shopee', 'https://shopee.co.id', 'bug'],
    ['Pupuk Organik Granul',   '25 kg',    75000,   'Memperbaiki struktur tanah dan meningkatkan ketersediaan hara.', 'wa', '6281234567890', 'leaf'],
    ['Pupuk Cair Hantu',       '1 Liter',  65000,   'Pupuk cair organik untuk mempercepat pertumbuhan dan meningkatkan hasil.', 'shopee', 'https://shopee.co.id', 'flask-conical'],
    ['Mulsa Plastik Hitam-Perak', '5 kg',  180000,  'Menekan gulma, menjaga kelembapan tanah, memantulkan sinar UV.', 'wa', '6281234567890', 'layers'],
    ['ZPT Atonik 6.5L',        '250 ml',   55000,   'Zat pengatur tumbuh untuk mempercepat perkecambahan dan pembungaan.', 'shopee', 'https://shopee.co.id', 'zap'],
    ['Kapur Dolomit',          '25 kg',    45000,   'Menetralkan pH tanah asam. Mengandung Ca dan Mg untuk tanaman.', 'wa', '6281234567890', 'mountain'],
    ['Trichoderma sp.',        '200 gr',   35000,   'Agen hayati untuk pengendalian penyakit tular tanah.', 'shopee', 'https://shopee.co.id', 'microscope'],
    ['Bacillus thuringiensis', '100 gr',   55000,   'Pestisida biologis untuk ulat grayak dan hama lepidoptera.', 'wa', '6281234567890', 'flask-conical'],
    ['Benih Padi Inpari 32',   '5 kg',     125000,  'Varietas padi unggul baru dengan produktivitas tinggi dan tahan hama.', 'shopee', 'https://shopee.co.id', 'wheat'],
];
@endphp

{{-- Rekomendasi dari sistem --}}
@if($items->count())
    <div class="card p-5 mb-6">
        <div class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="sparkles" class="w-4 h-4 text-brand-600"></i> Rekomendasi untuk Anda
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($items as $r)
                @php $col = $r->priority==='high' ? 'red' : ($r->priority==='medium' ? 'amber' : 'brand'); @endphp
                <div class="p-4 rounded-xl border border-ink-200 bg-{{ $col }}-50/40">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge bg-{{ $col }}-100 text-{{ $col }}-700 text-xs">{{ ucfirst($r->priority) }}</span>
                        @if($r->crop)<span class="badge badge-slate text-xs">{{ $r->crop }}</span>@endif
                    </div>
                    <div class="font-bold text-sm">{{ $r->title }}</div>
                    <p class="text-xs text-ink-600 mt-1">{{ $r->content }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Produk --}}
<div class="font-bold mb-3 flex items-center gap-2">
    <i data-lucide="shopping-bag" class="w-4 h-4 text-brand-600"></i> Produk Pertanian
</div>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($products as [$name, $unit, $price, $desc, $buyType, $buyTarget, $icon])
        <div class="card p-4 flex flex-col">
            <div class="h-24 rounded-xl bg-gradient-to-br from-brand-100 to-brand-50 grid place-items-center text-brand-700 mb-3">
                <i data-lucide="{{ $icon }}" class="w-10 h-10"></i>
            </div>
            <div class="font-bold text-sm leading-tight">{{ $name }}</div>
            <div class="text-xs text-ink-500 mt-0.5">{{ $unit }}</div>
            <p class="text-xs text-ink-500 mt-1.5 leading-relaxed line-clamp-2 flex-1">{{ $desc }}</p>
            <div class="font-extrabold text-brand-700 mt-2">Rp {{ number_format($price, 0, ',', '.') }}</div>
            @if($buyType === 'wa')
                <a href="https://wa.me/{{ $buyTarget }}?text={{ urlencode('Halo, saya ingin memesan ' . $name . ' (' . $unit . '). Info lebih lanjut?') }}"
                   target="_blank"
                   class="btn-primary w-full mt-3 text-sm py-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Beli via WA
                </a>
            @else
                <a href="{{ $buyTarget }}" target="_blank"
                   class="btn-primary w-full mt-3 text-sm py-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Beli di Shopee
                </a>
            @endif
        </div>
    @endforeach
</div>

<div class="mt-5 p-4 rounded-xl bg-ink-50 text-xs text-ink-500 flex items-start gap-2">
    <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 shrink-0"></i>
    Harga dapat berubah sewaktu-waktu. Pembelian diarahkan ke WhatsApp admin atau platform Shopee. TaniAI tidak bertanggung jawab atas transaksi di luar platform ini.
</div>
@endsection
