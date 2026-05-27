<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GrokAIService
{
    protected ?string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('GROK_API_KEY');
        $this->apiUrl = env('GROK_API_URL', 'https://api.x.ai/v1/chat/completions');
        $this->model  = env('GROK_MODEL', 'grok-beta');
    }

    public function isLive(): bool { return !empty($this->apiKey); }

    // ── CHAT ──────────────────────────────────────────────────────────────────
    public function chat(array $messages, array $opts = []): string
    {
        if (!$this->isLive()) return $this->mockChat($messages);

        try {
            $res = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'temperature' => $opts['temperature'] ?? 0.4,
                    'max_tokens'  => 1024,
                ]);

            if ($res->successful()) {
                $content = $res->json('choices.0.message.content');
                if (!empty($content)) return trim($content);
            }
            Log::warning('Grok chat error', ['status' => $res->status()]);
        } catch (\Throwable $e) {
            Log::error('Grok chat exception', ['msg' => $e->getMessage()]);
        }
        return $this->mockChat($messages);
    }

    // ── IMAGE VALIDATION ──────────────────────────────────────────────────────
    public function validatePlantImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        if (!$this->isLive()) return ['is_plant' => true, 'reason' => ''];

        // Cache result by image hash (avoid duplicate API calls)
        $hash   = md5(substr($base64Image, 0, 500));
        $cached = Cache::get("img_val_{$hash}");
        if ($cached !== null) return $cached;

        try {
            $res = Http::withToken($this->apiKey)->timeout(20)->post($this->apiUrl, [
                'model'       => $this->model,
                'max_tokens'  => 80,
                'temperature' => 0.1,
                'messages'    => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"]],
                        ['type' => 'text', 'text' => 'Is this a plant, leaf, stem, root, fruit, or any plant part? Reply ONLY JSON: {"is_plant":true,"reason":"..."} or {"is_plant":false,"reason":"..."}'],
                    ],
                ]],
            ]);

            if ($res->successful()) {
                $raw    = $res->json('choices.0.message.content', '');
                $clean  = trim(preg_replace('/```json|```/', '', $raw));
                $parsed = json_decode($clean, true);
                if (is_array($parsed) && array_key_exists('is_plant', $parsed)) {
                    Cache::put("img_val_{$hash}", $parsed, 3600);
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Plant validate error', ['msg' => $e->getMessage()]);
        }

        return ['is_plant' => true, 'reason' => 'validation_skipped'];
    }

    // ── DIAGNOSIS ─────────────────────────────────────────────────────────────
    public function diagnose(array $context, ?string $base64Image = null, string $mimeType = 'image/jpeg'): array
    {
        // Cache diagnosis by image+crop hash (same image = same result)
        $hash = $base64Image ? md5(substr($base64Image, 0, 500) . ($context['crop'] ?? '')) : null;
        if ($hash && $cached = Cache::get("diag_{$hash}")) return $cached;

        $result = null;
        if ($this->isLive()) {
            $prompt  = $this->buildDiagnosisPrompt($context);
            $content = [];
            if ($base64Image) {
                $content[] = ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"]];
            }
            $content[] = ['type' => 'text', 'text' => $prompt];

            $messages = [
                ['role' => 'system', 'content' => 'Anda pakar patologi tanaman Indonesia. Analisa gambar secara cermat. Jangan mengarang jika tidak ada bukti visual. Jawab HANYA JSON valid tanpa markdown.'],
                ['role' => 'user',   'content' => $base64Image ? $content : $prompt],
            ];

            $raw    = $this->chat($messages, ['temperature' => 0.2]);
            $clean  = trim(preg_replace('/```json|```/', '', $raw));
            $parsed = json_decode($clean, true);

            if (is_array($parsed) && isset($parsed['disease'], $parsed['confidence'])) {
                $result = $parsed;
            }
        }

        if (!$result) $result = $this->mockDiagnosis($context);
        if ($hash) Cache::put("diag_{$hash}", $result, 7200);

        return $result;
    }

    protected function buildDiagnosisPrompt(array $c): string
    {
        return "Analisa gambar tanaman dan diagnosa penyakit/kondisinya.\n"
            . "Konteks: Tanaman=" . ($c['crop'] ?? '?') . ", Umur=" . ($c['age'] ?? '?') . " hari, Lokasi=" . ($c['location'] ?? '?') . ", Tanah=" . ($c['soil_condition'] ?? '?') . ", Cuaca=" . ($c['weather'] ?? '?') . "\n"
            . "PENTING: Dasarkan diagnosis pada gejala visual yang benar-benar terlihat di gambar.\n"
            . 'Format JSON: {"disease":"nama spesifik","confidence":75,"risk_level":"Rendah|Sedang|Tinggi","description":"deskripsi gejala visual","recommendations":["langkah1","langkah2","langkah3","langkah4"]}';
    }

    // ── MOCK CHAT (deterministic, domain-guarded) ──────────────────────────────
    protected function mockChat(array $messages): string
    {
        $lastUser = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user') {
                $lastUser = is_array($m['content']) ? ($m['content'][0]['text'] ?? '') : ($m['content'] ?? '');
                break;
            }
        }

        $kw      = mb_strtolower(trim($lastUser));
        $userMsgs = array_filter($messages, fn($m) => ($m['role'] ?? '') === 'user');
        $isFirst = count($userMsgs) <= 1;

        // Only greet on very first message if it's a greeting or empty
        if ($isFirst && ($kw === '' || preg_match('/^(halo|hai|hello|hi|assalam|selamat|pagi|siang|sore|malam)/i', $kw))) {
            return "Halo! Saya **TaniAI** 🌱, asisten pertanian AI Anda.\n\nSaya siap membantu seputar:\n- 🌿 Diagnosa penyakit & hama tanaman\n- 💊 Rekomendasi pupuk dan pestisida\n- 🌾 Teknik budidaya dan panen\n- 🌦️ Tips pertanian berdasarkan cuaca\n\nSilakan tanyakan apa saja seputar pertanian!";
        }

        // Non-agriculture domain guard
        $nonAgriPatterns = ['/\b(html|css|javascript|python|java\b|php|kode program|coding|film|sinema|musik|lagu|olahraga|sepakbola|basket|politik|pemilu|game|video game|resep masak|kuliner)\b/i'];
        foreach ($nonAgriPatterns as $p) {
            if (preg_match($p, $kw)) {
                return "Maaf, saya hanya dapat membantu seputar **pertanian dan agrikultur** 🌱.\n\nUntuk topik yang Anda tanyakan, saya tidak memiliki keahlian yang tepat.\n\nApakah ada pertanyaan tentang tanaman, pupuk, hama, cuaca pertanian, atau teknik budidaya yang bisa saya bantu?";
            }
        }

        // ── Pertanyaan spesifik ──
        if (preg_match('/hawar|blb|bakteri.*daun|xanthomonas/i', $kw)) {
            return "**Hawar Daun Bakteri (BLB) — Xanthomonas oryzae**\n\n**Gejala:** Bercak coklat-kelabu dari tepi daun, meluas ke tengah, daun akhirnya kering.\n\n**Pengendalian:**\n1. Semprot **Streptomisin Sulfat** atau **Tembaga Hidroksida** sesuai dosis\n2. Hindari genangan air di petakan sawah\n3. Gunakan varietas tahan: Inpari 13, Code, Ciherang\n4. Jangan berlebihan pupuk Nitrogen — memperparah infeksi\n5. Musnahkan sisa tanaman terinfeksi setelah panen\n\n**Pencegahan:** Benih sehat bersertifikat, rotasi varietas, jaga drainase.";
        }

        if (preg_match('/blast|pyricularia/i', $kw)) {
            return "**Penyakit Blast — Pyricularia oryzae**\n\n**Gejala:** Bercak belah ketupat coklat (tepi abu) pada daun; leher malai busuk = blast leher.\n\n**Pengendalian:**\n1. Fungisida **Trisiklazol 75WP** (0.5 g/L) atau **Isoprotiolan 40EC**\n2. Semprot 2x: awal anakan + menjelang berbunga\n3. Kurangi pupuk N menjelang fase generatif\n4. Varietas tahan: Inpari 32, Memberamo, Situbagendit\n\n**Kondisi berisiko:** Suhu 24–28°C, kelembapan >80%, embun pagi tinggi.";
        }

        if (preg_match('/wereng|nilaparvata/i', $kw)) {
            return "**Wereng Batang Padi**\n\n**Ambang ekonomi semprot:**\n- Vegetatif: ≥10 ekor/rumpun\n- Generatif: ≥20 ekor/rumpun\n\n**Pengendalian:**\n1. **Imidakloprid 200SL** 0.5 ml/L — semprot ke pangkal batang\n2. **Buprofezin 25WP** 2 g/L untuk wereng stadium nimfa\n3. Jaga populasi laba-laba & kepik sebagai musuh alami\n4. Varietas tahan: Inpari 13, 30, 33, 38\n\n⚠️ **Hindari piretroid** — menyebabkan resurjensi wereng!";
        }

        if (preg_match('/antraknosa|patek|cabai busuk|colletotrichum/i', $kw)) {
            return "**Antraknosa / Patek Cabai — Colletotrichum sp.**\n\n**Gejala:** Bercak cekung hitam pada buah, meluas, buah rontok.\n\n**Pengendalian:**\n1. Fungisida **Mankozeb 80WP** 2 g/L atau **Propineb 70WP** 2 g/L\n2. Semprot 7–10 hari sekali, terutama saat buah mulai terbentuk\n3. Panen buah sebelum terlalu masak (80% merah)\n4. Bersihkan buah gugur dari lahan\n5. Gunakan mulsa untuk cegah percik tanah ke buah\n\n**Musim hujan:** Frekuensi semprot lebih sering (5–7 hari).";
        }

        if (preg_match('/cara menanam|budidaya|teknik tanam/i', $kw)) {
            $t = '';
            foreach (['padi','jagung','cabai','tomat','kedelai','bawang merah','kentang','kopi','kakao','singkong','ubi jalar'] as $crop) {
                if (str_contains($kw, $crop)) { $t = $crop; break; }
            }
            $t = $t ?: 'tanaman';
            return "**Panduan Budidaya " . ucfirst($t) . "**\n\n**1. Persiapan Lahan**\nOlah tanah 20–30 cm, pH optimal 5.5–6.8, pupuk dasar organik 2 ton/ha.\n\n**2. Benih/Bibit**\nGunakan benih bersertifikat, daya kecambah ≥80%, bebas penyakit.\n\n**3. Penanaman**\nAwal musim hujan. Jarak tanam sesuai varietas.\n\n**4. Pemeliharaan**\n- Pupuk: Urea (vegetatif) → NPK (generatif)\n- Pengairan: sesuaikan fase tumbuh\n- PHT: pantau hama/penyakit 2x/minggu\n\n**5. Panen**\nSaat indikator kematangan optimal tercapai.\n\n📖 Detail lengkap tersedia di menu **Panduan Budidaya**.";
        }

        if (preg_match('/pupuk|urea|npk|sp-?36|kcl|za\b|dolomit|phonska/i', $kw)) {
            return "**Rekomendasi Pemupukan Padi Sawah (per hektar)**\n\n| Waktu | Pupuk | Dosis |\n|-------|-------|-------|\n| Dasar | Organik + SP36 + KCl | 2 ton + 100 kg + 50 kg |\n| 10–14 HST | Urea + ZA | 50 + 50 kg |\n| 28 HST | Urea + NPK Phonska | 50 + 150 kg |\n| 42 HST | Urea | 50 kg |\n\n**Cabai (per hektar):**\n- Dasar: Kompos 10 ton + Dolomit 500 kg\n- Vegetatif: NPK 16-16-16 tiap 2 minggu\n- Berbuah: NPK 12-6-22 atau KNO₃\n\n**Tips:** Pupuk pagi hari, setelah tanah lembap. pH <5.5 → kapur dulu.";
        }

        if (preg_match('/panen|harvest|siap panen|kapan panen/i', $kw)) {
            return "**Panduan Panen**\n\n| Komoditas | Umur | Indikator |\n|-----------|------|-----------|\n| Padi | 100–120 HST | 85–90% gabah kuning, kadar air ~24% |\n| Jagung | 90–105 HST | Rambut coklat kering, biji keras |\n| Cabai | 70–90 HST | 70–80% merah, tekstur padat |\n| Tomat | 70–80 HST | Merah merata, sedikit lunak |\n| Kedelai | 85–95 HST | 90% polong kuning-coklat |\n\n**Pascapanen:** Panen pagi/sore → sortasi → simpan kadar air aman.";
        }

        if (preg_match('/hama|ulat|kutu|thrips|lalat|penggerek/i', $kw)) {
            return "**Pengendalian Hama Terpadu (PHT)**\n\n**Urutan pengendalian:**\n1. **Mekanis** — ambil manual, perangkap feromon/kuning\n2. **Biologis** — Trichoderma, Bacillus thuringiensis, musuh alami\n3. **Kimia** — pilihan terakhir saat melebihi ambang ekonomi\n\n**Ambang ekonomi umum:**\n- Ulat grayak padi: 25% rumpun terserang\n- Penggerek batang: 5% anakan mati (sundep)\n- Thrips cabai: 1–2 ekor/daun muda\n\nSebutkan **hama dan tanaman spesifik** untuk rekomendasi lebih tepat!";
        }

        if (preg_match('/cuaca|hujan|kering|musim|iklim/i', $kw)) {
            return "**Tips Pertanian Berdasarkan Cuaca**\n\n**Musim Hujan:**\n- Perbaiki drainase, cegah genangan\n- Waspada penyakit jamur & bakteri — semprot fungisida preventif\n- Tunda pemupukan daun saat hujan lebat\n- Percepat panen komoditas yang sudah matang\n\n**Musim Kemarau:**\n- Irigasi berselang untuk padi (hemat air)\n- Mulsa organik jaga kelembapan sayuran\n- Waspada wereng & thrips (berkembang di kondisi kering)\n- Tanam varietas tahan kering\n\n📍 Cek **Cuaca & Peringatan** untuk data real-time lokasi Anda.";
        }

        // Generic fallback — always relevant, never empty
        return "Terima kasih atas pertanyaan tentang **\"" . \Illuminate\Support\Str::limit($lastUser, 60) . "\"**.\n\nUntuk menjawab lebih akurat, mohon tambahkan:\n- 🌿 **Jenis tanaman** yang dibudidayakan\n- 📍 **Lokasi/daerah** Anda\n- 🔍 **Gejala spesifik** yang terlihat (warna, tekstur, bagian yang terkena)\n- 🗓️ **Umur tanaman** (berapa hari/bulan)\n\nAlternatif: gunakan fitur **Diagnosa Tanaman** untuk analisa langsung dari foto tanaman Anda.";
    }

    // ── MOCK DIAGNOSIS (deterministic per crop) ────────────────────────────────
    protected function mockDiagnosis(array $c): array
    {
        $map = [
            'Padi'         => ['Hawar Daun Bakteri (BLB)', 'Tinggi', 78, 'Gejala khas BLB: bercak coklat-kelabu dari tepi daun meluas ke tengah. Umum pada kondisi lembap dan genangan air.'],
            'Jagung'       => ['Hawar Daun Turcicum', 'Sedang', 74, 'Bercak memanjang abu-coklat pada daun, khas Exserohilum turcicum. Berkembang saat cuaca lembap.'],
            'Cabai'        => ['Antraknosa (Patek)', 'Tinggi', 82, 'Bercak cekung hitam pada buah cabai, khas Colletotrichum sp. Intensif di musim hujan.'],
            'Tomat'        => ['Early Blight (Alternaria)', 'Sedang', 73, 'Bercak coklat konsentris pada daun tua — khas Alternaria solani. Menyebar dari bawah ke atas.'],
            'Kedelai'      => ['Karat Kedelai', 'Sedang', 72, 'Pustula coklat-oranye pada permukaan bawah daun, khas Phakopsora pachyrhizi.'],
            'Bawang Merah' => ['Bercak Ungu (Alternaria porri)', 'Sedang', 76, 'Bercak ungu-coklat memanjang pada daun, khas Alternaria porri. Aktif di musim hujan.'],
            'Kentang'      => ['Late Blight (Phytophthora)', 'Tinggi', 84, 'Bercak basah tepi putih pada daun — khas Phytophthora infestans. Sangat destruktif, segera tangani.'],
            'Kopi'         => ['Karat Daun Kopi (HV)', 'Sedang', 75, 'Pustula oranye pada permukaan bawah daun — khas Hemileia vastatrix. Umum pada kopi Arabika.'],
            'Kakao'        => ['Busuk Buah (Phytophthora)', 'Tinggi', 80, 'Bercak hitam pada buah kakao, berkembang cepat di musim hujan. Khas Phytophthora palmivora.'],
        ];

        $crop   = $c['crop'] ?? 'Padi';
        $data   = $map[$crop] ?? ['Hawar Daun', 'Sedang', 70, 'Gejala hawar daun terdeteksi. Lakukan identifikasi lebih lanjut untuk penanganan spesifik.'];

        return [
            'disease'         => $data[0],
            'risk_level'      => $data[1],
            'confidence'      => $data[2],
            'description'     => $data[3],
            'recommendations' => [
                'Isolasi dan singkirkan bagian tanaman yang terinfeksi berat',
                'Gunakan fungisida/bakterisida sesuai jenis penyakit yang teridentifikasi',
                'Semprot pagi (06:00–09:00) atau sore (15:00–17:00) — hindari tengah hari',
                'Perbaiki drainase lahan dan kurangi kelembapan berlebih',
                'Pantau ulang 5–7 hari setelah penanganan — ulangi jika diperlukan',
            ],
        ];
    }
}
