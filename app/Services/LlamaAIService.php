<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * LlamaAIService - Groq API with meta-llama/llama-4-scout-17b-16e-instruct
 * Unlimited usage, used for all AI features in TaniAI.
 */
class LlamaAIService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    protected string $model  = 'meta-llama/llama-4-scout-17b-16e-instruct';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', env('LLAMA_API_KEY', ''));
    }

    public function isLive(): bool
    {
        return !empty($this->apiKey);
    }

    // ── CHAT (farming domain only) ──────────────────────────────────────────
    public function chat(array $messages, float $temperature = 0.5): string
    {
        if (!$this->isLive()) return $this->mockChat($messages);

        try {
            $res = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'temperature' => $temperature,
                    'max_tokens'  => 1024,
                ]);

            if ($res->successful()) {
                $content = $res->json('choices.0.message.content');
                if (!empty($content)) return trim($content);
            }

            Log::warning('Llama chat error', ['status' => $res->status(), 'body' => $res->body()]);
        } catch (\Throwable $e) {
            Log::error('Llama chat exception', ['msg' => $e->getMessage()]);
        }

        return $this->mockChat($messages);
    }

    // ── IMAGE VALIDATION (is it a plant?) ───────────────────────────────────
    public function validatePlantImage(string $base64, string $mime = 'image/jpeg'): array
    {
        // Llama Scout supports vision
        if (!$this->isLive()) return ['is_plant' => true, 'reason' => ''];

        $hash   = md5(substr($base64, 0, 600));
        $cached = Cache::get("plant_val_{$hash}");
        if ($cached !== null) return $cached;

        try {
            $res = Http::withToken($this->apiKey)->timeout(20)->post($this->apiUrl, [
                'model'       => $this->model,
                'max_tokens'  => 100,
                'temperature' => 0.1,
                'messages'    => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'image_url', 'image_url' => ['url' => "data:{$mime};base64,{$base64}"]],
                        ['type' => 'text', 'text' => 'Is this image showing a plant, leaf, stem, root, fruit, or any plant part? Reply ONLY with valid JSON no markdown: {"is_plant":true,"reason":"brief reason"} or {"is_plant":false,"reason":"what it is instead"}'],
                    ],
                ]],
            ]);

            if ($res->successful()) {
                $raw    = $res->json('choices.0.message.content', '');
                $clean  = trim(preg_replace('/```json|```/', '', $raw));
                $parsed = json_decode($clean, true);
                if (is_array($parsed) && array_key_exists('is_plant', $parsed)) {
                    Cache::put("plant_val_{$hash}", $parsed, 3600);
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Plant validate error', ['msg' => $e->getMessage()]);
        }

        return ['is_plant' => true, 'reason' => 'validation_skipped'];
    }

    // ── DIAGNOSIS ────────────────────────────────────────────────────────────
    public function diagnose(array $ctx, ?string $base64 = null, string $mime = 'image/jpeg'): array
    {
        $hash = $base64 ? md5(substr($base64, 0, 600) . ($ctx['crop'] ?? '')) : null;
        if ($hash && $cached = Cache::get("diag_{$hash}")) return $cached;

        $result = null;

        if ($this->isLive()) {
            $prompt  = $this->buildDiagnosisPrompt($ctx);
            $content = [];
            if ($base64) {
                $content[] = ['type' => 'image_url', 'image_url' => ['url' => "data:{$mime};base64,{$base64}"]];
            }
            $content[] = ['type' => 'text', 'text' => $prompt];

            $messages = [
                [
                    'role'    => 'system',
                    'content' => 'Anda adalah pakar patologi tanaman Indonesia. Analisa gambar secara cermat berdasarkan gejala visual yang terlihat. Jangan mengarang jika gambar tidak jelas. Jawab HANYA JSON valid tanpa markdown atau teks tambahan.',
                ],
                ['role' => 'user', 'content' => $base64 ? $content : $prompt],
            ];

            $raw    = $this->chat($messages, 0.2);
            $clean  = trim(preg_replace('/```json|```/', '', $raw));
            $parsed = json_decode($clean, true);

            if (is_array($parsed) && isset($parsed['disease'], $parsed['confidence'])) {
                $result = $parsed;
            }
        }

        if (!$result) $result = $this->mockDiagnosis($ctx);
        if ($hash) Cache::put("diag_{$hash}", $result, 7200);

        return $result;
    }

    protected function buildDiagnosisPrompt(array $c): string
    {
        return "Analisa gambar tanaman dan diagnosa kondisinya.\n"
            . "Konteks: Tanaman=" . ($c['crop'] ?? '?')
            . ", Umur=" . ($c['age'] ?? '?') . " hari"
            . ", Kondisi tanah=" . ($c['soil_condition'] ?? 'tidak diketahui')
            . ", Cuaca=" . ($c['weather'] ?? 'tidak diketahui') . "\n"
            . "Identifikasi bagian tanaman pada gambar (daun/batang/buah/akar/dll).\n"
            . "Dasarkan diagnosis hanya pada gejala visual yang BENAR-BENAR terlihat.\n"
            . 'Format JSON wajib: {"disease":"nama penyakit spesifik","confidence":75,"risk_level":"Rendah|Sedang|Tinggi","plant_part":"daun|batang|buah|akar|keseluruhan","description":"deskripsi gejala visual","causes":["penyebab 1","penyebab 2"],"solutions":["solusi 1","solusi 2","solusi 3"],"prevention":["pencegahan 1","pencegahan 2"],"health_status":"Sehat|Terancam|Kritis","recommendations":["tindakan 1","tindakan 2","tindakan 3"]}';
    }

    // ── FARMING ASSISTANT CHAT ───────────────────────────────────────────────
    protected function mockChat(array $messages): string
    {
        $lastUser = '';
        foreach (array_reverse($messages) as $m) {
            if (($m['role'] ?? '') === 'user') {
                $lastUser = is_array($m['content']) ? ($m['content'][0]['text'] ?? '') : ($m['content'] ?? '');
                break;
            }
        }

        $kw       = mb_strtolower(trim($lastUser));
        $userMsgs = array_filter($messages, fn($m) => ($m['role'] ?? '') === 'user');
        $isFirst  = count($userMsgs) <= 1;

        if ($isFirst && ($kw === '' || preg_match('/^(halo|hai|hello|hi|assalam|selamat|pagi|siang|sore|malam)/i', $kw))) {
            return "Halo! Saya **Asisten Tani AI** 🌱\n\nSaya siap membantu pertanyaan seputar:\n- 🌿 Penyakit & hama tanaman\n- 💊 Pupuk dan pestisida\n- 🌾 Teknik budidaya & panen\n- 🌦️ Cuaca dan strategi pertanian\n\nApa yang ingin Anda tanyakan?";
        }

        // Non-farming domain guard
        if (preg_match('/\b(html|css|javascript|python|java\b|coding|film|musik|olahraga|politik|game|resep)\b/i', $kw)) {
            return "Maaf, saya hanya bisa membantu seputar **pertanian** 🌱\n\nSilakan tanyakan tentang:\n- Penyakit atau hama tanaman\n- Cara budidaya dan perawatan\n- Pupuk dan pestisida\n- Cuaca dan musim tanam\n- Harga hasil tani";
        }

        // Spesifik pertanian
        if (preg_match('/hawar|blb|bakteri.*daun/i', $kw)) {
            return "**Hawar Daun Bakteri (BLB)**\n\n**Gejala:** Bercak coklat-kelabu dari tepi daun, meluas ke tengah.\n\n**Penanganan:**\n1. Semprot **Streptomisin Sulfat** atau **Tembaga Hidroksida** sesuai dosis\n2. Hindari genangan air di petakan\n3. Gunakan varietas tahan: Inpari 13, Ciherang, Code\n4. Kurangi pupuk Nitrogen berlebih\n\n**Pencegahan:** Benih bersertifikat, rotasi varietas, drainase baik.";
        }

        if (preg_match('/wereng/i', $kw)) {
            return "**Wereng Batang Padi**\n\n**Ambang semprot:**\n- Vegetatif: ≥10 ekor/rumpun\n- Generatif: ≥20 ekor/rumpun\n\n**Pengendalian:**\n1. **Imidakloprid 200SL** — 0.5 ml/L, semprot ke pangkal batang\n2. **Buprofezin 25WP** — 2 g/L untuk nimfa\n3. Varietas tahan: Inpari 13, 30, 33\n\n⚠️ **Hindari piretroid** — menyebabkan resurjensi!";
        }

        if (preg_match('/cara menanam|budidaya|tanam/i', $kw)) {
            $t = 'tanaman';
            foreach (['padi','jagung','cabai','tomat','kedelai','bawang','kentang','kopi','kakao'] as $crop) {
                if (str_contains($kw, $crop)) { $t = $crop; break; }
            }
            return "**Panduan Budidaya " . ucfirst($t) . "**\n\n**1. Persiapan Lahan**\nOlah tanah 20–30 cm, pH 5.5–6.8, pupuk organik 2 ton/ha.\n\n**2. Benih/Bibit**\nGunakan benih bersertifikat, daya kecambah ≥80%.\n\n**3. Penanaman**\nAwal musim hujan, jarak tanam sesuai varietas.\n\n**4. Pemeliharaan**\n- Pemupukan: Urea (vegetatif) → NPK (generatif)\n- Pengairan: sesuai fase\n- PHT: pantau 2x/minggu\n\n📖 Detail di menu **Panduan Budidaya**.";
        }

        if (preg_match('/pupuk|urea|npk/i', $kw)) {
            return "**Rekomendasi Pemupukan Padi (per hektar)**\n\n| Waktu | Pupuk | Dosis |\n|-------|-------|-------|\n| Dasar | Organik + SP36 + KCl | 2 ton + 100 kg + 50 kg |\n| 14 HST | Urea + ZA | 50 + 50 kg |\n| 28 HST | Urea + NPK Phonska | 50 + 150 kg |\n| 42 HST | Urea | 50 kg |\n\n**Tips:** Pupuk pagi hari setelah tanah lembap. pH <5.5 → kapur dolomit terlebih dahulu.";
        }

        if (preg_match('/hama|ulat|thrips/i', $kw)) {
            return "**Pengendalian Hama Terpadu (PHT)**\n\n**Urutan prioritas:**\n1. **Mekanis** — tangkap manual, perangkap feromon\n2. **Biologis** — Trichoderma, Bacillus thuringiensis\n3. **Kimia** — hanya jika melewati ambang ekonomi\n\nSebutkan **hama spesifik** dan tanaman Anda untuk rekomendasi lebih tepat.";
        }

        return "Terima kasih atas pertanyaan tentang **\"" . \Illuminate\Support\Str::limit($lastUser, 60) . "\"**.\n\nUntuk jawaban lebih akurat, mohon tambahkan:\n- 🌿 **Jenis tanaman**\n- 🔍 **Gejala yang terlihat**\n- 🗓️ **Umur tanaman**\n\nAtau gunakan **Diagnosa Tanaman** untuk analisa foto langsung.";
    }

    // ── MOCK DIAGNOSIS (deterministic) ──────────────────────────────────────
    protected function mockDiagnosis(array $c): array
    {
        $map = [
            'Padi'         => ['Hawar Daun Bakteri (BLB)', 'Tinggi', 78, 'daun',       'Gejala BLB: bercak coklat-kelabu dari tepi daun meluas ke tengah. Khas Xanthomonas oryzae pv. oryzae.',
                ['Bakteri Xanthomonas oryzae', 'Kelembapan tinggi', 'Luka mekanis pada daun'],
                ['Streptomisin Sulfat 20WP (0.5 g/L)', 'Tembaga Hidroksida 77WP (2 g/L)', 'Kurangi pemupukan N berlebih'],
                ['Gunakan benih bersertifikat', 'Varietas tahan BLB (Inpari 13, Ciherang)', 'Jaga drainase lahan']],
            'Jagung'       => ['Hawar Daun Turcicum', 'Sedang', 74, 'daun',       'Bercak memanjang abu-coklat pada daun, khas Exserohilum turcicum.',
                ['Jamur Exserohilum turcicum', 'Cuaca lembap berulang', 'Sirkulasi udara buruk'],
                ['Fungisida Mancozeb 80WP (2 g/L)', 'Perbaiki jarak tanam', 'Semprot preventif saat musim hujan'],
                ['Rotasi tanaman', 'Varietas tahan hawar', 'Sanitasi sisa tanaman']],
            'Cabai'        => ['Antraknosa (Patek)', 'Tinggi', 82, 'buah',       'Bercak cekung hitam pada buah cabai, berkembang cepat di musim hujan. Khas Colletotrichum sp.',
                ['Jamur Colletotrichum sp.', 'Kelembapan >80%', 'Luka pada buah'],
                ['Mankozeb 80WP (2 g/L) tiap 7 hari', 'Panen lebih awal (80% merah)', 'Buang buah terinfeksi'],
                ['Mulsa plastik mencegah percik tanah', 'Sirkulasi udara baik', 'Fungisida preventif saat berbuah']],
            'Tomat'        => ['Early Blight (Alternaria)', 'Sedang', 73, 'daun',       'Bercak konsentris coklat pada daun tua, menyebar dari bawah ke atas. Khas Alternaria solani.',
                ['Jamur Alternaria solani', 'Kelembapan fluktuatif', 'Tanaman stres air'],
                ['Mankozeb 80WP (2 g/L)', 'Chlorothalonil 75WP', 'Pemangkasan daun bawah yang terinfeksi'],
                ['Rotasi tanaman 2-3 musim', 'Hindari irigasi overhead', 'Mulsa untuk kurangi percikan']],
            'Kedelai'      => ['Karat Kedelai', 'Sedang', 72, 'daun',       'Pustula coklat-oranye pada permukaan bawah daun. Khas Phakopsora pachyrhizi.',
                ['Jamur Phakopsora pachyrhizi', 'Suhu 15-28°C', 'Angin membawa spora'],
                ['Triazol (Tebukonazol 250EC)', 'Flusilazol + Karbendazim', 'Semprot saat 30-40 HST'],
                ['Tanam varietas tahan', 'Tanam serempak', 'Pantau daun bagian bawah']],
        ];

        $crop   = $c['crop'] ?? 'Padi';
        $d      = $map[$crop] ?? $map['Padi'];

        return [
            'disease'         => $d[0],
            'risk_level'      => $d[1],
            'confidence'      => $d[2],
            'plant_part'      => $d[3],
            'description'     => $d[4],
            'causes'          => $d[5],
            'solutions'       => $d[6],
            'prevention'      => $d[7],
            'health_status'   => $d[1] === 'Tinggi' ? 'Kritis' : ($d[1] === 'Sedang' ? 'Terancam' : 'Sehat'),
            'recommendations' => [
                'Isolasi dan singkirkan bagian tanaman yang terinfeksi berat',
                'Aplikasikan penanganan sesuai solusi di atas segera',
                'Semprot pagi (06:00-09:00) atau sore (15:00-17:00)',
                'Pantau ulang 5-7 hari setelah penanganan',
            ],
        ];
    }
}
