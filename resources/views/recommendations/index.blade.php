@extends('layouts.app')
@section('title','Toko & Rekomendasi')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Toko & Rekomendasi</h1>
    <p class="text-ink-500 mt-1">Produk pertanian pilihan yang dikelola oleh tim TaniAI.</p>
</div>

@php
// DB products (admin managed) — fallback to static if empty
$dbProducts = $items ?? collect();
$hasDb = $dbProducts->count() > 0;

// Static fallback products
$staticProducts = [
    ['Pupuk UREA 50kg',         'Pupuk nitrogen untuk pertumbuhan vegetatif. Cocok untuk semua tanaman pangan.',          285000, 'wa',     '6281234567890', 'Pupuk',     'sprout'],
    ['Fungisida Propineb 70WP', 'Fungisida kontak untuk hawar daun, bercak daun, dan embun tepung.',                     95000,  'shopee', 'https://shopee.co.id', 'Pestisida', 'shield'],
    ['Pupuk NPK Mutiara 16-16-16','Pupuk majemuk seimbang untuk semua fase pertumbuhan tanaman.',                        310000, 'wa',     '6281234567890', 'Pupuk',     'layers'],
    ['Insektisida Decis 25EC',  'Insektisida untuk hama ulat, thrips, dan aphid pada berbagai tanaman.',                 85000,  'shopee', 'https://shopee.co.id', 'Pestisida', 'bug'],
    ['Pupuk Organik Granul 25kg','Memperbaiki struktur tanah dan meningkatkan ketersediaan hara makro-mikro.',           75000,  'wa',     '6281234567890', 'Pupuk',     'leaf'],
    ['Kapur Dolomit 25kg',      'Menetralkan pH tanah asam. Mengandung Ca dan Mg untuk pertumbuhan optimal.',            45000,  'wa',     '6281234567890', 'Lainnya',   'mountain'],
    ['Mulsa Plastik Hitam-Perak','Menekan gulma, menjaga kelembapan tanah, dan memantulkan sinar UV.',                   180000, 'shopee', 'https://shopee.co.id', 'Alat Tani','layers'],
    ['Benih Padi Inpari 32 5kg','Varietas unggul produktivitas tinggi, tahan hama blast dan bercak daun.',               125000, 'shopee', 'https://shopee.co.id', 'Benih',    'wheat'],
    ['Trichoderma sp. 200gr',   'Agen biologis untuk pengendalian penyakit layu fusarium dan busuk akar.',                35000,  'wa',     '6281234567890', 'Pestisida', 'microscope'],
    ['Bacillus thuringiensis',  'Pestisida biologis untuk ulat grayak dan hama lepidoptera. Aman lingkungan.',            55000,  'wa',     '6281234567890', 'Pestisida', 'flask-conical'],
    ['Pupuk Cair Organik 1L',   'Mempercepat pertumbuhan vegetatif dan meningkatkan ketahanan tanaman.',                  65000,  'shopee', 'https://shopee.co.id', 'Pupuk',    'droplets'],
    ['ZPT Atonik 6.5L 250ml',   'Zat pengatur tumbuh untuk mempercepat perkecambahan dan pembungaan.',                   55000,  'wa',     '6281234567890', 'Lainnya',  'zap'],
];
@endphp

{{-- Filter by category --}}
@php
$allCats = $hasDb
    ? $dbProducts->pluck('category')->filter()->unique()->values()->prepend('Semua')
    : collect(['Semua','Pupuk','Pestisida','Benih','Alat Tani','Lainnya']);
$activeCat = request('cat', 'Semua');
@endphp
<div class="flex gap-2 flex-wrap mb-5">
    @foreach($allCats as $cat)
        <a href="{{ route('recommendations.index') }}?cat={{ $cat }}"
           class="px-4 py-1.5 rounded-xl text-sm font-semibold transition {{ $activeCat===$cat ? 'bg-brand-gradient text-white shadow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50' }}">
            {{ $cat }}
        </a>
    @endforeach
</div>

{{-- Product grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @if($hasDb)
        @foreach($dbProducts->when($activeCat!=='Semua', fn($c)=>$c->where('category',$activeCat)) as $prod)
        <div class="card p-4 flex flex-col" x-data="{detail:false}">
            <div class="h-20 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 grid place-items-center text-brand-700 mb-3 shrink-0">
                <i data-lucide="sprout" class="w-10 h-10"></i>
            </div>
            <div class="font-bold text-sm leading-tight flex-1">{{ $prod->title }}</div>
            @if($prod->category)
                <div class="mt-1"><span class="badge badge-slate text-[10px]">{{ $prod->category }}</span></div>
            @endif
            <p class="text-xs text-ink-500 mt-1.5 line-clamp-2 leading-relaxed">{{ $prod->content }}</p>
            @if($prod->price)
                <div class="font-extrabold text-brand-700 mt-2 text-base">Rp {{ number_format($prod->price,0,',','.') }}</div>
            @endif
            <button @click="detail=true" class="btn-outline w-full mt-2.5 text-xs py-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5"></i> Detail & Beli
            </button>

            {{-- Detail modal --}}
            <div x-show="detail" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
                <div @click.outside="detail=false" class="card p-6 w-full max-w-sm">
                    <div class="flex justify-between mb-3">
                        <div class="font-bold">{{ $prod->title }}</div>
                        <button @click="detail=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
                    </div>
                    @if($prod->category)
                        <span class="badge badge-slate text-xs">{{ $prod->category }}</span>
                    @endif
                    <p class="text-sm text-ink-600 mt-3 leading-relaxed">{{ $prod->content }}</p>
                    @if($prod->price)
                        <div class="text-2xl font-extrabold text-brand-700 mt-3">Rp {{ number_format($prod->price,0,',','.') }}</div>
                    @endif
                    <div class="mt-4">
                        @if($prod->buy_type === 'wa' && $prod->buy_target)
                            <a href="https://wa.me/{{ $prod->buy_target }}?text={{ urlencode('Halo, saya tertarik dengan produk: '.$prod->title) }}"
                               target="_blank" class="btn-primary w-full text-sm">
                                <i data-lucide="message-circle" class="w-4 h-4"></i> Beli via WhatsApp
                            </a>
                        @elseif($prod->buy_target)
                            <a href="{{ $prod->buy_target }}" target="_blank" class="btn-primary w-full text-sm">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Beli di Toko
                            </a>
                        @else
                            <div class="text-xs text-ink-400 text-center">Hubungi admin untuk pembelian</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        {{-- Static fallback --}}
        @foreach($staticProducts as [$name, $desc, $price, $buyType, $buyTarget, $cat, $icon])
            @if($activeCat === 'Semua' || $activeCat === $cat)
            <div class="card p-4 flex flex-col" x-data="{detail:false}">
                <div class="h-20 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 grid place-items-center text-brand-700 mb-3 shrink-0">
                    <i data-lucide="{{ $icon }}" class="w-10 h-10"></i>
                </div>
                <div class="font-bold text-sm leading-tight flex-1">{{ $name }}</div>
                <span class="badge badge-slate text-[10px] mt-1 w-fit">{{ $cat }}</span>
                <p class="text-xs text-ink-500 mt-1.5 line-clamp-2 leading-relaxed">{{ $desc }}</p>
                <div class="font-extrabold text-brand-700 mt-2 text-base">Rp {{ number_format($price,0,',','.') }}</div>
                <button @click="detail=true" class="btn-outline w-full mt-2.5 text-xs py-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i> Detail & Beli
                </button>

                <div x-show="detail" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
                    <div @click.outside="detail=false" class="card p-6 w-full max-w-sm">
                        <div class="flex justify-between mb-3">
                            <div class="font-bold">{{ $name }}</div>
                            <button @click="detail=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
                        </div>
                        <span class="badge badge-slate text-xs">{{ $cat }}</span>
                        <p class="text-sm text-ink-600 mt-3 leading-relaxed">{{ $desc }}</p>
                        <div class="text-2xl font-extrabold text-brand-700 mt-3">Rp {{ number_format($price,0,',','.') }}</div>
                        <div class="mt-4">
                            @if($buyType === 'wa')
                                <a href="https://wa.me/{{ $buyTarget }}?text={{ urlencode('Halo, saya tertarik dengan produk: '.$name) }}"
                                   target="_blank" class="btn-primary w-full text-sm">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i> Beli via WhatsApp
                                </a>
                            @else
                                <a href="{{ $buyTarget }}" target="_blank" class="btn-primary w-full text-sm">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Beli di Shopee
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endif
</div>

<div class="mt-5 p-3 rounded-xl bg-ink-50 text-xs text-ink-400 flex items-start gap-2">
    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
    <span>Produk dikelola oleh tim TaniAI. Harga dapat berubah. TaniAI tidak bertanggung jawab atas transaksi di luar platform.</span>
</div>
@endsection
