@extends('layouts.app')
@section('title','Panduan Budidaya')
@section('content')

<div class="mb-5">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Panduan Budidaya</h1>
    <p class="text-ink-500 mt-1 text-sm">Panduan lengkap menanam dan merawat tanaman pilihan Anda.</p>
</div>

@php
$cropData = [
    'Padi' => ['emoji'=>'🌾','steps'=>[['Persiapan Lahan','Bajak 20–30 cm, pupuk organik 2 ton/ha, SP36 100 kg/ha, ratakan.'],['Persemaian','Rendam benih 24 jam, tiriskan 48 jam. Semai di lahan basah 21–25 hari.'],['Penanaman','Jarak 25×25 cm, 2–3 bibit/lubang, tanam 21–25 HSS.'],['Pemeliharaan','UREA 50 kg/ha @ 14 & 42 HST. NPK 150 kg/ha @ 28 HST. Air 3–5 cm.']],'fertilizer'=>'Organik 2 ton · SP36 100 kg · UREA 150 kg · NPK 150 kg /ha','disease'=>'Hawar daun (BLB), Blast, Wereng batang, Bercak coklat','harvest'=>'100–120 HST · 85% gabah kuning · kadar air ~24%','water'=>'Pengairan berselang. Air 3–5 cm fase vegetatif.','tip'=>'Gunakan varietas unggul (Inpari 32, Ciherang). PHT rutin.'],
    'Jagung'=>['emoji'=>'🌽','steps'=>[['Persiapan Lahan','Olah tanah 25–30 cm, pH 5.5–7.0, kapur jika perlu.'],['Penanaman','2 biji/lubang, jarak 75×25 cm (53.000 tanaman/ha).'],['Pemupukan','Dasar: 200 kg Phonska + 150 kg UREA. Susulan 21 & 45 HST.'],['Pemeliharaan','Penjarangan 7 HST. Penyiangan 14 & 30 HST. Pembumbunan.']],'fertilizer'=>'UREA 200 kg · SP36 100 kg · KCl 100 kg /ha','disease'=>'Bulai, Hawar turcicum, Karat daun, Busuk batang','harvest'=>'90–105 HST · rambut coklat kering · biji keras','water'=>'Kritis fase berbunga (55–65 HST). Kebutuhan 500–800 mm/musim.','tip'=>'Varietas hibrida (NK7328, Pioneer). Mulsa kurangi gulma.'],
    'Cabai'=>['emoji'=>'🌶️','steps'=>[['Persemaian','Semai di tray 25–30 hari. Media tanah:pupuk 1:1.'],['Persiapan Bedengan','Lebar 100–120 cm, tinggi 30–40 cm. Pasang mulsa hitam-perak.'],['Penanaman','Jarak 60×50 cm, bibit 25–30 HSS, waktu sore hari.'],['Pemeliharaan','Pasang ajir 75 cm. Pangkas tunas air. Semprot preventif.']],'fertilizer'=>'Organik 20 ton · NPK 16-16-16 500 kg · KNO₃ berbuah /ha','disease'=>'Antraknosa (Patek), Layu fusarium, Virus CMV, Thrips','harvest'=>'70–90 HST · 70–80% merah · interval 3–5 hari','water'=>'Drip 1–2 liter/tanaman/hari. Hindari genangan.','tip'=>'Rotasi tanaman. Varietas tahan virus. Kendalikan thrips.'],
    'Tomat'=>['emoji'=>'🍅','steps'=>[['Persemaian','Polybag kecil 15–21 hari. Sterilisasi media cegah damping off.'],['Persiapan Lahan','Bedengan, pH 5.5–6.8. Kapur dolomit jika perlu.'],['Penanaman','Jarak 60×70 cm. Mulsa hitam perak. Lubang tanam 30×30×30 cm.'],['Pemeliharaan','Pasang ajir segera. Wiwilan rutin. Ikat batang tiap minggu.']],'fertilizer'=>'Organik 15–20 ton · NPK 600 kg · Kalsium cegah BER /ha','disease'=>'Early blight, Late blight, Layu bakteri, Gemini virus','harvest'=>'70–80 HST · merah 70% (pasar jauh) · 90% (lokal)','water'=>'Konsisten. Ketidakmerataan → BER. Drip ideal.','tip'=>'Varietas tahan virus. Hindari overhead irrigation.'],
    'Kedelai'=>['emoji'=>'🫘','steps'=>[['Persiapan Lahan','Olah minimum atau tanpa olah. pH 5.8–7.0.'],['Inokulasi Benih','Inokulasi Rhizobium 5 g/kg benih. Kurangi kebutuhan N.'],['Penanaman','2–3 biji/lubang, jarak 40×15 cm atau 30×20 cm.'],['Pemeliharaan','Penyiangan 21 & 35 HST. Pengairan kritis saat berbunga.']],'fertilizer'=>'SP36 100 kg · KCl 75 kg · UREA sedikit (25–50 kg) /ha','disease'=>'Karat kedelai, Pustul bakteri, Downy mildew, Penggerek polong','harvest'=>'80–90 HST · 90% polong kuning-coklat · kadar air 14%','water'=>'Kritis R1 (berbunga) dan R5–R6 (pengisian biji).','tip'=>'Tanam musim kemarau untuk biji lebih baik.'],
    'Bawang Merah'=>['emoji'=>'🧅','steps'=>[['Persiapan Lahan','Bedengan 120 cm, tinggi 25–30 cm. pH 5.6–7.0.'],['Penanaman Umbi','Potong ujung 1/3. Tanam 2–3 umbi/lubang, jarak 20×15 cm.'],['Pemupukan Awal','Pupuk dasar 0–7 HST. Penyiraman 2x/hari hingga 30 HST.'],['Pemeliharaan','Penyiangan 2–3 kali. Semprot fungisida/insektisida preventif.']],'fertilizer'=>'ZA 400 kg · SP36 150 kg · KCl 200 kg · Organik 10 ton /ha','disease'=>'Moler (Fusarium), Bercak ungu (Alternaria), Embun tepung','harvest'=>'60–70 HST · 60–70% daun rebah · jemur 7–14 hari','water'=>'2x/hari 0–30 HST. 1x/hari 31–60 HST. Stop 10 hari sebelum panen.','tip'=>'Umbi sehat bersertifikat. Rotasi minimal 2 musim.'],
    'Kentang'=>['emoji'=>'🥔','steps'=>[['Persiapan Benih','Bibit G0–G4. Chitting 2–4 minggu sebelum tanam.'],['Persiapan Lahan','Bedengan 70 cm. Organik 20 ton/ha.'],['Penanaman','Jarak 70×30 cm, kedalaman 10–15 cm. 1 umbi/lubang.'],['Pembumbunan','Wajib 2x: 21 & 40 HST. Pemupukan intensif.']],'fertilizer'=>'Organik 20–30 ton · ZA 500 kg · SP36 200 kg · KCl 300 kg /ha','disease'=>'Late blight (P. infestans), Layu bakteri, Scab, Nematoda','harvest'=>'90–120 HST · daun menguning · biarkan 10–14 hari setelah mati','water'=>'Drip atau furrow. Kritis fase stolon dan pembentukan umbi.','tip'=>'>700 mdpl untuk kualitas terbaik. Simpan benih 4–10°C.'],
    'Kopi'=>['emoji'=>'☕','steps'=>[['Pembibitan','Semai polybag 6–8 bulan. Naungan 50–60%.'],['Persiapan Lubang','60×60×60 cm. Organik 10–20 kg/lubang. Diamkan 1–2 minggu.'],['Penanaman','Jarak 2.5×2.5 m (arabika) atau 3×3 m (robusta). Awal hujan.'],['Pemangkasan','Bentuk (1–2 tahun), produksi, rejuvenasi.']],'fertilizer'=>'Organik 10–20 kg/pohon/tahun · NPK 200–400 g/pohon 2x/tahun','disease'=>'Karat daun (HV), Bubuk buah (CBB), Jamur akar','harvest'=>'3–4 tahun pertama. Panen cherry merah. Optimal tahun 8–20.','water'=>'1500–2000 mm/tahun. Kering singkat rangsang pembungaan.','tip'=>'Naungan pelindung penting. Arabika >700 mdpl.'],
];
$selectedCrops = array_keys($cropData);
@endphp

<div x-data="{
    tab: '{{ old('crop','Padi') }}',
    customSearch: '',
    showCustom: false,
    searchResult: null
}">

    {{-- Crop selector tabs --}}
    <div class="flex gap-2 flex-wrap mb-5">
        @foreach($selectedCrops as $c)
            @php $em = $cropData[$c]['emoji'] ?? '🌿'; @endphp
            <button @click="tab='{{ $c }}'; showCustom=false"
                :class="tab==='{{ $c }}'&&!showCustom ? 'bg-brand-gradient text-white shadow-glow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-1.5">
                <span>{{ $em }}</span> {{ $c }}
            </button>
        @endforeach

        {{-- Custom search button --}}
        <button @click="showCustom=true; tab=''"
            :class="showCustom ? 'bg-brand-gradient text-white shadow-glow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50'"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-1.5">
            <i data-lucide="search" class="w-3.5 h-3.5"></i> Cari Tanaman Lain
        </button>
    </div>

    {{-- Custom search panel --}}
    <div x-show="showCustom" x-cloak class="card p-5 mb-5">
        <div class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="search" class="w-4 h-4 text-brand-600"></i> Cari Panduan Tanaman
        </div>
        <div class="flex gap-2">
            <input x-model="customSearch" type="text" class="input flex-1"
                placeholder="Ketik nama tanaman, contoh: Pepaya, Rambutan, Durian..."
                @keydown.enter.prevent="if(customSearch.trim()) window.location='{{ route('chat.index') }}?crop='+encodeURIComponent(customSearch.trim())">
            <a :href="customSearch.trim() ? '{{ route('chat.index') }}?crop='+encodeURIComponent(customSearch.trim()) : '#'"
                class="btn-primary text-sm px-4">
                <i data-lucide="bot" class="w-4 h-4"></i> Tanya AI
            </a>
        </div>
        <p class="text-xs text-ink-400 mt-2">Tidak ditemukan di daftar? AI akan membantu memberikan panduan berdasarkan tanaman yang Anda cari.</p>
    </div>

    {{-- Per-crop content --}}
    @foreach($cropData as $cropName => $info)
    <div x-show="tab==='{{ $cropName }}'" x-cloak>
        <div class="card p-6">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $info['emoji'] }}</span>
                    <div>
                        <h2 class="text-xl font-extrabold">Panduan Budidaya {{ $cropName }}</h2>
                        <p class="text-xs text-ink-400">Panen: {{ $info['harvest'] }}</p>
                    </div>
                </div>
                <a href="{{ route('chat.index') }}?crop={{ urlencode($cropName) }}"
                    class="btn-outline text-sm">
                    <i data-lucide="bot" class="w-4 h-4"></i> Tanya AI Tentang {{ $cropName }}
                </a>
            </div>

            {{-- Steps --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                @foreach($info['steps'] as $i => [$title, $desc])
                    <div class="p-4 rounded-2xl bg-ink-50 hover:bg-brand-50/50 transition">
                        <div class="w-8 h-8 rounded-full bg-brand-gradient text-white grid place-items-center font-bold text-sm mb-3">{{ $i+1 }}</div>
                        <div class="font-bold text-sm">{{ $title }}</div>
                        <p class="text-xs text-ink-600 mt-1.5 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Detail cards --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                    ['sprout','brand','Pemupukan',   $info['fertilizer']],
                    ['bug',   'red',  'Penyakit Umum',$info['disease']],
                    ['droplets','blue','Pengairan',  $info['water']],
                    ['lightbulb','amber','Tips Penting',$info['tip']],
                ] as [$ico,$col,$label,$val])
                    <div class="p-4 rounded-xl bg-{{ $col }}-50 border border-{{ $col }}-100">
                        <div class="font-bold text-xs flex items-center gap-1.5 mb-2 text-{{ $col }}-700">
                            <i data-lucide="{{ $ico }}" class="w-3.5 h-3.5"></i> {{ $label }}
                        </div>
                        <p class="text-xs text-ink-700 leading-relaxed">{{ $val }}</p>
                    </div>
                @endforeach
            </div>

            {{-- AI CTA --}}
            <div class="mt-5 p-4 rounded-xl bg-brand-50 border border-brand-100 flex items-center gap-4">
                <i data-lucide="bot" class="w-8 h-8 text-brand-600 shrink-0"></i>
                <div class="flex-1">
                    <div class="font-semibold text-sm">Perlu panduan lebih spesifik untuk kondisi lahan Anda?</div>
                    <p class="text-xs text-ink-500 mt-0.5">AI dapat memberikan rekomendasi disesuaikan lokasi, cuaca, dan kondisi spesifik Anda.</p>
                </div>
                <a href="{{ route('chat.index') }}?crop={{ urlencode($cropName) }}" class="btn-primary text-sm shrink-0">
                    Tanya AI
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
