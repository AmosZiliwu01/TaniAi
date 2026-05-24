@extends('layouts.app')
@section('title','Panduan Budidaya')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Panduan Budidaya</h1>
    <p class="text-ink-500 mt-1">Langkah lengkap budidaya tanaman berdasarkan pilihan Anda.</p>
</div>

@php
$cropData = [
    'Padi' => [
        'steps' => [
            ['Persiapan Lahan', 'Bajak tanah sedalam 20-30 cm, beri pupuk dasar organik 2 ton/ha dan SP36 100 kg/ha, ratakan dengan leveler.'],
            ['Persemaian', 'Rendam benih 24 jam, tiriskan 48 jam. Semai di lahan basah dengan benih 25 kg/ha selama 21-25 hari.'],
            ['Penanaman', 'Tanam bibit 21-25 HSS dengan jarak 25x25 cm atau 20x20 cm. Tanam 2-3 bibit per lubang.'],
            ['Pemeliharaan', 'Beri UREA 50 kg/ha pada 14 HST, 45 HST. NPK 100 kg/ha pada 28 HST. Jaga air 3-5 cm.'],
        ],
        'fertilizer'  => 'UREA 150 kg/ha · SP36 100 kg/ha · KCl 75 kg/ha · Organik 2 ton/ha',
        'diseases'    => 'Hawar daun bakteri (BLB), Blast, Bercak coklat, Wereng batang',
        'harvest'     => '100-120 hari setelah tanam. Panen saat 85-90% gabah menguning.',
        'water'       => 'Pengairan berselang (intermittent). Jaga air 3-5 cm pada fase vegetatif.',
        'tips'        => 'Gunakan varietas unggul (Inpari 32, Ciherang). Pengendalian hama terpadu (PHT).',
    ],
    'Jagung' => [
        'steps' => [
            ['Persiapan Lahan', 'Olah tanah 25-30 cm, beri kapur jika pH < 5.5. Buat bedengan atau tanam langsung.'],
            ['Penanaman', 'Tanam benih langsung 2 biji/lubang. Jarak 75x25 cm (populasi 53.000 tanaman/ha).'],
            ['Pemupukan', 'Pupuk dasar 200 kg/ha Phonska + 150 kg UREA. Susulan pada 21 HST dan 45 HST.'],
            ['Pemeliharaan', 'Penjarangan bila 2 tanaman tumbuh (sisakan 1). Penyiangan 14 dan 30 HST.'],
        ],
        'fertilizer'  => 'UREA 200 kg/ha · SP36 100 kg/ha · KCl 100 kg/ha terbagi 2 aplikasi',
        'diseases'    => 'Bulai (Peronosclerospora), Hawar daun turcicum, Karat daun, Busuk batang',
        'harvest'     => '85-100 hari. Panen saat daun kering, klobot kuning, biji keras (kadar air 28-32%).',
        'water'       => 'Kritis pada fase berbunga (55-65 HST). Kebutuhan air 500-800 mm/musim.',
        'tips'        => 'Pilih varietas hibrida (NK7328, Pioneer 21). Gunakan mulsa untuk menjaga kelembapan.',
    ],
    'Cabai' => [
        'steps' => [
            ['Persemaian', 'Semai benih di tray selama 25-30 hari. Gunakan media campuran tanah:pupuk 1:1.'],
            ['Persiapan Bedengan', 'Lebar bedengan 100-120 cm, tinggi 30-40 cm. Pasang mulsa plastik hitam perak.'],
            ['Penanaman', 'Tanam bibit 25-30 HSS dengan jarak 60x50 cm. Waktu terbaik: pagi atau sore.'],
            ['Pemeliharaan', 'Pasang ajir setinggi 75 cm. Pemangkasan tunas air. Semprot pestisida preventif.'],
        ],
        'fertilizer'  => 'Organik 20 ton/ha · NPK 16-16-16 500 kg/ha · KNO3 untuk perangsang buah',
        'diseases'    => 'Antraknosa (Colletotrichum), Layu fusarium, Virus CMV/CVPD, Thrips',
        'harvest'     => '70-90 hari setelah tanam. Panen saat 70-80% warna merah. Interval 3-5 hari.',
        'water'       => 'Drip irrigation ideal. 1-2 liter/tanaman/hari. Hindari genangan.',
        'tips'        => 'Rotasi tanaman penting. Gunakan varietas tahan virus. Kendalikan vektor (thrips).',
    ],
    'Tomat' => [
        'steps' => [
            ['Persemaian', 'Semai di polybag kecil 15-21 hari. Sterilisasi media untuk cegah damping off.'],
            ['Persiapan Lahan', 'Buat bedengan, pH tanah 5.5-6.8. Berikan kapur dolomit jika perlu.'],
            ['Penanaman', 'Jarak 60x70 cm. Pasang mulsa hitam perak. Buat lubang tanam 30x30x30 cm.'],
            ['Pemeliharaan', 'Pasang ajir segera. Pemangkasan tunas air (wiwilan). Ikat batang tiap minggu.'],
        ],
        'fertilizer'  => 'Organik 15-20 ton/ha · NPK 600 kg/ha · Kalsium untuk cegah BER',
        'diseases'    => 'Early blight, Late blight (Phytophthora), Layu bakteri, Gemini virus',
        'harvest'     => '70-80 hari. Panen saat 70% merah untuk pasar jauh, 90% merah untuk lokal.',
        'water'       => 'Konsisten penting. Ketidakmerataan menyebabkan blossom end rot. Drip ideal.',
        'tips'        => 'Gunakan varietas tahan virus. Hindari overhead irrigation. Rotasi 2-3 musim.',
    ],
    'Kedelai' => [
        'steps' => [
            ['Persiapan Lahan', 'Olah tanah minimum atau tanpa olah tanah. pH optimal 5.8-7.0.'],
            ['Penanaman', 'Tanam langsung, 2-3 biji/lubang. Jarak 40x15 cm atau 30x20 cm.'],
            ['Inokulasi', 'Inokulasi benih dengan Rhizobium untuk fiksasi nitrogen. Beri pupuk Rhizobium 5 g/kg benih.'],
            ['Pemeliharaan', 'Penyiangan 21 dan 35 HST. Pengairan kritis pada fase pembungaan dan pengisian biji.'],
        ],
        'fertilizer'  => 'SP36 100 kg/ha · KCl 75 kg/ha · UREA sedikit (25-50 kg, ada Rhizobium)',
        'diseases'    => 'Karat kedelai, Pustul bakteri, Downy mildew, Penggerek polong',
        'harvest'     => '80-90 hari. Panen saat polong 90% kuning-coklat. Kadar air biji 14-15%.',
        'water'       => 'Butuh air 450-700 mm/musim. Kritis pada R1 (berbunga) dan R5-R6 (pengisian biji).',
        'tips'        => 'Tanam di musim kemarau untuk mutu biji lebih baik. Panen pagi untuk kurangi kerontokan.',
    ],
    'Bawang Merah' => [
        'steps' => [
            ['Persiapan Lahan', 'Bedengan lebar 120 cm, tinggi 25-30 cm. pH 5.6-7.0. Beri Dolomit jika perlu.'],
            ['Penanaman Umbi', 'Potong ujung umbi 1/3 bagian. Tanam 2-3 umbi/lubang dengan jarak 20x15 cm.'],
            ['Pemupukan Awal', 'Aplikasi pupuk dasar pada 0-7 HST. Penyiraman 2x sehari hingga 30 HST.'],
            ['Pemeliharaan', 'Penyiangan 2-3 kali. Semprot fungisida/insektisida preventif setiap minggu.'],
        ],
        'fertilizer'  => 'ZA 400 kg/ha · SP36 150 kg/ha · KCl 200 kg/ha · Organik 10 ton/ha',
        'diseases'    => 'Moler (Fusarium), Bercak ungu (Alternaria), Embun tepung, Ulat bawang',
        'harvest'     => '60-70 hari. Panen saat 60-70% daun rebah. Jemur 7-14 hari di bawah terik matahari.',
        'water'       => '2x sehari 0-30 HST, 1x sehari 31-60 HST. Stop irigasi 10 hari sebelum panen.',
        'tips'        => 'Gunakan umbi sehat bersertifikat. Rotasi tanaman minimal 2 musim.',
    ],
    'Kentang' => [
        'steps' => [
            ['Persiapan Benih', 'Siapkan bibit bersertifikat G0-G4. Chitting (perkecambahan) 2-4 minggu sebelum tanam.'],
            ['Persiapan Lahan', 'Buat bedengan lebar 70 cm. Tambahkan pupuk organik 20 ton/ha.'],
            ['Penanaman', 'Jarak 70x30 cm. Kedalaman 10-15 cm. Bibit 1 umbi/lubang atau potongan dengan 2 mata tunas.'],
            ['Pemeliharaan', 'Pembumbunan wajib 2x (21 dan 40 HST). Pemupukan intensif.'],
        ],
        'fertilizer'  => 'Organik 20-30 ton/ha · ZA 500 kg/ha · SP36 200 kg/ha · KCl 300 kg/ha',
        'diseases'    => 'Late blight (Phytophthora infestans), Layu bakteri, Scab, Nematoda',
        'harvest'     => '90-120 hari. Panen saat daun menguning. Biarkan 10-14 hari setelah daun mati.',
        'water'       => 'Drip atau furrow. Kritis pada fase stolon dan pembentukan umbi.',
        'tips'        => 'Dataran tinggi >700 mdpl untuk kualitas terbaik. Simpan benih di suhu 4-10°C.',
    ],
    'Kopi' => [
        'steps' => [
            ['Pembibitan', 'Semai biji kopi pada polybag selama 6-8 bulan. Naungan 50-60%.'],
            ['Persiapan Lubang Tanam', 'Lubang 60x60x60 cm. Beri pupuk organik 10-20 kg/lubang. Diamkan 1-2 minggu.'],
            ['Penanaman', 'Jarak 2.5x2.5 m (arabika) atau 3x3 m (robusta). Tanam awal musim hujan.'],
            ['Pemangkasan', 'Pangkasan bentuk (1-2 tahun pertama), pangkasan produksi, dan pangkasan rejuvenasi.'],
        ],
        'fertilizer'  => 'Organik 10-20 kg/pohon/tahun · NPK 200-400 g/pohon 2x/tahun',
        'diseases'    => 'Karat daun (Hemileia vastatrix), Bubuk buah (Hypothenemus hampei), Jamur akar',
        'harvest'     => 'Mulai berbuah 3-4 tahun. Panen saat buah merah (cherry ripe). Produksi optimal tahun 8-20.',
        'water'       => 'Butuh 1500-2000 mm/tahun. Musim kering singkat merangsang pembungaan.',
        'tips'        => 'Naungan pohon pelindung penting. Arabika di >700 mdpl, robusta di bawahnya.',
    ],
    'Kakao' => [
        'steps' => [
            ['Pembibitan', 'Semai biji segar dalam polybag 4-6 bulan. Pilih buah sehat ukuran besar.'],
            ['Persiapan Lahan', 'Lubang 60x60x60 cm. Beri mulsa organik. Tanaman pelindung disiapkan dulu.'],
            ['Penanaman', 'Jarak 3x3 m. Tanam awal musim hujan. Pasang pelindung dari sinar matahari langsung.'],
            ['Pemangkasan', 'Bentuk jorket (percabangan primer) dan pangkasan rutin tahunan.'],
        ],
        'fertilizer'  => 'Organik 10-20 kg/pohon · NPK 200-500 g/pohon 3x/tahun',
        'diseases'    => 'Busuk buah (Phytophthora), VSD (Vascular Streak Dieback), Helopeltis',
        'harvest'     => 'Mulai berbuah 3-4 tahun. Panen saat kulit buah berubah warna. Interval 2 minggu.',
        'water'       => '1500-2500 mm/tahun merata. Naungan pohon penting untuk menjaga kelembapan.',
        'tips'        => 'Sanitasi kebun kritis untuk cegah busuk buah. Fermentasi 5-7 hari pasca panen.',
    ],
];
$selectedCrops = array_keys($cropData);
@endphp

<div x-data="{ tab: '{{ old('crop', 'Padi') }}' }">
    <div class="flex gap-2 flex-wrap pb-2">
        @foreach($selectedCrops as $c)
            <button @click="tab='{{ $c }}'"
                :class="tab==='{{ $c }}' ? 'bg-brand-gradient text-white shadow-glow' : 'bg-white border border-ink-200 text-ink-600 hover:bg-ink-50'"
                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition">{{ $c }}</button>
        @endforeach
    </div>

    @foreach($cropData as $cropName => $info)
        <div x-show="tab==='{{ $cropName }}'" x-cloak class="card p-6 mt-4">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold">Panduan Budidaya {{ $cropName }}</h2>
                <a href="{{ route('chat.index') }}" class="btn-outline text-sm">
                    <i data-lucide="bot" class="w-4 h-4"></i> Tanya AI
                </a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @foreach($info['steps'] as $i => [$stepTitle, $stepDesc])
                    <div class="p-4 rounded-2xl bg-ink-50">
                        <div class="w-8 h-8 rounded-full bg-brand-gradient text-white grid place-items-center font-bold">{{ $i+1 }}</div>
                        <div class="font-bold mt-3">{{ $stepTitle }}</div>
                        <p class="text-xs text-ink-600 mt-1 leading-relaxed">{{ $stepDesc }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                <div class="p-4 rounded-xl bg-brand-50">
                    <div class="font-bold flex items-center gap-1.5 mb-1.5">
                        <i data-lucide="sprout" class="w-4 h-4 text-brand-700"></i> Pemupukan
                    </div>
                    <div class="text-ink-600 text-xs leading-relaxed">{{ $info['fertilizer'] }}</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50">
                    <div class="font-bold flex items-center gap-1.5 mb-1.5">
                        <i data-lucide="bug" class="w-4 h-4 text-amber-700"></i> Penyakit Umum
                    </div>
                    <div class="text-ink-600 text-xs leading-relaxed">{{ $info['diseases'] }}</div>
                </div>
                <div class="p-4 rounded-xl bg-blue-50">
                    <div class="font-bold flex items-center gap-1.5 mb-1.5">
                        <i data-lucide="droplets" class="w-4 h-4 text-blue-700"></i> Pengairan
                    </div>
                    <div class="text-ink-600 text-xs leading-relaxed">{{ $info['water'] }}</div>
                </div>
                <div class="p-4 rounded-xl bg-green-50">
                    <div class="font-bold flex items-center gap-1.5 mb-1.5">
                        <i data-lucide="scissors" class="w-4 h-4 text-green-700"></i> Panen
                    </div>
                    <div class="text-ink-600 text-xs leading-relaxed">{{ $info['harvest'] }}</div>
                </div>
            </div>

            <div class="mt-4 p-4 rounded-xl bg-ink-50 border-l-4 border-brand-500">
                <div class="font-semibold text-sm flex items-center gap-2 mb-1">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-brand-600"></i> Tips Penting
                </div>
                <p class="text-xs text-ink-600">{{ $info['tips'] }}</p>
            </div>

            <div class="mt-4 p-4 rounded-xl bg-brand-50 flex items-center gap-3">
                <i data-lucide="bot" class="w-8 h-8 text-brand-600 shrink-0"></i>
                <div>
                    <div class="font-semibold text-sm">Perlu panduan lebih spesifik?</div>
                    <p class="text-xs text-ink-600 mt-0.5">Tanya AI Penyuluh untuk rekomendasi disesuaikan kondisi lahan Anda.</p>
                </div>
                <a href="{{ route('chat.index') }}" class="btn-primary text-sm ml-auto shrink-0">Tanya AI</a>
            </div>
        </div>
    @endforeach
</div>
@endsection
