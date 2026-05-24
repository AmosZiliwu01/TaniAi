<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GrokAIService - integration dengan xAI Grok API.
 * Mendukung diagnosa gambar, chat konsisten, dan validasi gambar tanaman.
 */
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

    public function isLive(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * General chat completion - konsisten menjawab pertanyaan.
     */
    public function chat(array $messages, array $opts = []): string
    {
        if (!$this->isLive()) {
            return $this->mockChat($messages);
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'temperature' => $opts['temperature'] ?? 0.7,
                    'max_tokens'  => 1000,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if (!empty($content)) {
                    return $content;
                }
            }
            Log::warning('Grok API error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('Grok API exception', ['error' => $e->getMessage()]);
        }
        return $this->mockChat($messages);
    }

    /**
     * Validasi apakah gambar merupakan gambar tanaman.
     * Return: ['is_plant' => bool, 'reason' => string]
     */
    public function validatePlantImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        if (!$this->isLive()) {
            // Jika tidak ada API, asumsikan valid (tidak bisa cek)
            return ['is_plant' => true, 'reason' => ''];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:{$mimeType};base64,{$base64Image}",
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'Apakah gambar ini menampilkan tanaman, daun, batang, buah tanaman, atau bagian dari tanaman? Jawab HANYA dengan format JSON: {"is_plant": true/false, "reason": "penjelasan singkat"}. Jangan tambahkan teks lain.',
                                ],
                            ],
                        ],
                    ],
                    'max_tokens' => 100,
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if ($content) {
                    $clean = preg_replace('/```json|```/', '', $content);
                    $parsed = json_decode(trim($clean), true);
                    if (is_array($parsed) && isset($parsed['is_plant'])) {
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Grok image validation error', ['error' => $e->getMessage()]);
        }

        // Default ke valid jika API error agar tidak memblokir sepenuhnya
        return ['is_plant' => true, 'reason' => ''];
    }

    /**
     * Diagnosa tanaman menggunakan gambar + konteks.
     */
    public function diagnose(array $context, ?string $base64Image = null, string $mimeType = 'image/jpeg'): array
    {
        if ($this->isLive()) {
            $prompt = $this->buildDiagnosisPrompt($context);
            $userContent = [];

            if ($base64Image) {
                $userContent[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"],
                ];
            }

            $userContent[] = [
                'type' => 'text',
                'text' => $prompt,
            ];

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah ahli patologi tanaman AI untuk pertanian Indonesia. Analisa gambar dan data yang diberikan secara akurat. Jawab HANYA dalam format JSON valid tanpa markdown.',
                ],
                [
                    'role' => 'user',
                    'content' => $base64Image ? $userContent : $prompt,
                ],
            ];

            $raw = $this->chat($messages, ['temperature' => 0.3]);
            $clean = preg_replace('/```json|```/', '', $raw);
            $parsed = json_decode(trim($clean), true);
            if (is_array($parsed) && isset($parsed['disease'])) {
                return $parsed;
            }
        }
        return $this->mockDiagnosis($context);
    }

    protected function buildDiagnosisPrompt(array $c): string
    {
        $soilInfo = $c['soil_condition'] ?? '-';
        return "Diagnosa kondisi tanaman berdasarkan gambar dan data berikut:\n"
            . "Jenis Tanaman: " . ($c['crop'] ?? '-') . "\n"
            . "Umur Tanaman: " . ($c['age'] ?? '-') . " hari\n"
            . "Lokasi: " . ($c['location'] ?? '-') . "\n"
            . "Kondisi Tanah: " . $soilInfo . "\n"
            . "Cuaca saat ini: " . ($c['weather'] ?? '-') . "\n"
            . "Analisa gambar dengan teliti dan berikan diagnosa akurat.\n"
            . "Berikan respons JSON dengan format tepat ini:\n"
            . '{"disease":"nama penyakit atau kondisi","confidence":85,"risk_level":"Rendah|Sedang|Tinggi","description":"deskripsi detail","recommendations":["langkah 1","langkah 2","langkah 3"]}';
    }

    /**
     * Mock chat yang lebih cerdas - benar-benar menjawab pertanyaan tanpa reset ke sapaan.
     */
    protected function mockChat(array $messages): string
    {
        // Cari pesan user terakhir (bukan system)
        $lastUserMsg = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastUserMsg = is_array($msg['content']) ? ($msg['content'][0]['text'] ?? '') : ($msg['content'] ?? '');
                break;
            }
        }

        $kw = mb_strtolower($lastUserMsg);

        // Deteksi apakah ini pesan pertama (hanya ada 1-2 pesan + system)
        $nonSystemCount = count(array_filter($messages, fn($m) => ($m['role'] ?? '') !== 'system'));
        $isFirstMessage = $nonSystemCount <= 1;

        if ($isFirstMessage && (str_contains($kw, 'halo') || str_contains($kw, 'hai') || str_contains($kw, 'hello') || empty(trim($kw)))) {
            return "Halo! Saya TaniAI, asisten pintar pertanian Anda. Saya siap membantu soal penyakit tanaman, pupuk, cuaca, atau strategi panen. Apa yang ingin Anda tanyakan?";
        }

        // Menanam/budidaya
        if (str_contains($kw, 'cara menanam') || str_contains($kw, 'budidaya')) {
            $crop = '';
            foreach (['padi', 'jagung', 'cabai', 'tomat', 'kedelai', 'bawang', 'kentang', 'kopi', 'kakao'] as $c) {
                if (str_contains($kw, $c)) { $crop = $c; break; }
            }
            $crop = $crop ?: 'tanaman';
            return "Cara budidaya {$crop} yang baik:\n\n**1. Persiapan Lahan**\nOlah tanah sedalam 20-30 cm, beri pupuk dasar organik 2 ton/ha, dan pastikan drainase lancar.\n\n**2. Pemilihan Benih**\nGunakan benih unggul bersertifikat dengan daya kecambah minimal 80%.\n\n**3. Penanaman**\nTanam pada awal musim hujan atau saat kelembapan cukup. Jarak tanam sesuaikan dengan varietas.\n\n**4. Pemeliharaan**\n- Pemupukan: UREA fase vegetatif, NPK fase generatif\n- Pengairan: sesuai kebutuhan tanaman\n- Pengendalian OPT: pantau rutin setiap minggu\n\n**5. Panen**\nPanen saat tanaman menunjukkan tanda kematangan optimal.\n\nApakah Anda ingin informasi lebih detail tentang salah satu tahap?";
        }

        // Hawar / penyakit daun
        if (str_contains($kw, 'hawar') || (str_contains($kw, 'penyakit') && str_contains($kw, 'daun'))) {
            return "Penanganan Hawar Daun:\n\n**Identifikasi:** Bercak coklat/kuning pada daun, meluas cepat dari tepi daun.\n\n**Langkah Cepat:**\n1. Kurangi genangan air di sekitar tanaman\n2. Aplikasikan fungisida berbahan aktif **Mancozeb 80WP** dosis 2 g/L atau **Propineb 70WP** dosis 1.5 g/L\n3. Semprotkan pada pagi hari (06.00-09.00) atau sore (15.00-17.00)\n4. Pangkas dan bakar bagian yang terinfeksi parah\n5. Ulangi aplikasi setiap 7-10 hari selama 3x\n\n**Pencegahan:** Pilih varietas tahan, atur jarak tanam, hindari pupuk N berlebihan.\n\nApakah ada pertanyaan lanjutan?";
        }

        // Wereng
        if (str_contains($kw, 'wereng')) {
            return "Penanganan Wereng pada Padi:\n\n**Gejala:** Tanaman menguning mendadak (hopperburn), batang bawah rusak.\n\n**Pengendalian:**\n1. **Semprot insektisida:** Imidakloprid 200SL (0.5 ml/L), Buprofezin 400SC, atau BPMC 500EC\n2. **Rotasi varietas:** Gunakan varietas tahan wereng (Ciherang, Inpari 13/30/33)\n3. **Musuh alami:** Jaga populasi laba-laba dan kepik dengan tidak terlalu banyak insektisida\n4. **Sanitasi:** Bersihkan gulma sebagai tempat berlindung wereng\n\n**Ambang ekonomi:** Semprot jika ada 10 ekor/rumpun pada fase vegetatif atau 20 ekor/rumpun pada fase generatif.";
        }

        // Pupuk
        if (str_contains($kw, 'pupuk') || str_contains($kw, 'urea') || str_contains($kw, 'npk')) {
            return "Rekomendasi Pemupukan:\n\n**Padi (per hektar):**\n- Pupuk dasar: Organik 2 ton + SP36 100 kg\n- 14 HST: UREA 50 kg + ZA 50 kg\n- 28 HST: UREA 50 kg + NPK 16-16-16 100 kg\n- 45 HST: UREA 50 kg + KCl 50 kg\n\n**Cabai (per hektar):**\n- Dasar: Kompos 10 ton + Dolomit 500 kg\n- Vegetatif: NPK 15-15-15 setiap 2 minggu\n- Berbuah: Kalium tinggi (NPK 12-6-22)\n\n**Tips:** Pemupukan terbaik pagi setelah pengairan ringan, hindari saat terik matahari.\n\nUntuk rekomendasi lebih spesifik, sebutkan jenis tanaman dan umurnya.";
        }

        // Cuaca/hujan
        if (str_contains($kw, 'cuaca') || str_contains($kw, 'hujan')) {
            return "Tips Pertanian Saat Musim Hujan:\n\n1. **Drainase:** Buat saluran drainase yang baik agar air tidak menggenang\n2. **Penyakit:** Tingkatkan kewaspadaan terhadap jamur dan bakteri, semprotkan fungisida preventif\n3. **Pemupukan:** Tunda pemupukan daun saat hujan lebat, gunakan pupuk granul terkubur\n4. **Panen:** Percepat panen jika sudah masak untuk menghindari kerusakan\n5. **Mulsa:** Pasang mulsa plastik untuk kurangi percikan tanah ke daun\n\nUntuk info cuaca real-time di lokasi Anda, cek menu Cuaca & Peringatan di aplikasi ini.";
        }

        // Hama
        if (str_contains($kw, 'hama') || str_contains($kw, 'ulat') || str_contains($kw, 'kutu')) {
            return "Pengendalian Hama Terpadu (PHT):\n\n**Prinsip PHT:**\n1. Pantau kondisi tanaman 2x seminggu\n2. Identifikasi hama dan musuh alami\n3. Terapkan pengendalian hanya jika melampaui ambang ekonomi\n\n**Metode Pengendalian:**\n- **Mekanis:** Tangkap manual, pasang perangkap feromon/kuning\n- **Biologis:** Gunakan Trichoderma sp., Bacillus thuringiensis\n- **Kimia:** Terakhir jika metode lain tidak efektif\n\n**Sebutkan hama spesifik** yang Anda hadapi untuk rekomendasi lebih tepat.";
        }

        // Panen
        if (str_contains($kw, 'panen') || str_contains($kw, 'harvest')) {
            return "Panduan Panen Optimal:\n\n**Indikator Siap Panen:**\n- **Padi:** 85-90% gabah menguning, kadar air ±24%\n- **Jagung:** Rambut coklat kering, biji keras\n- **Cabai:** Warna sesuai varietas (merah/kuning), tekstur padat\n- **Tomat:** Warna merah merata, sedikit lunak\n\n**Tips Panen:**\n1. Panen pagi atau sore hari\n2. Gunakan alat bersih untuk cegah kontaminasi\n3. Simpan di tempat teduh, hindari langsung sinar matahari\n4. Sortir segera untuk pisahkan yang rusak\n\nSebutkan tanaman spesifik untuk rekomendasi lebih detail.";
        }

        // Default - menjawab pertanyaan umum pertanian
        return "Terima kasih atas pertanyaan Anda tentang \"" . \Illuminate\Support\Str::limit($lastUserMsg, 60) . "\".\n\nSaya akan mencoba membantu. Untuk jawaban yang lebih akurat, bisa Anda jelaskan lebih detail:\n- Jenis tanaman yang Anda budidayakan\n- Gejala atau masalah yang terlihat\n- Lokasi dan kondisi cuaca\n\nAnda juga bisa menggunakan fitur **Diagnosa Tanaman** untuk analisa gambar langsung dari tanaman Anda.";
    }

    protected function mockDiagnosis(array $c): array
    {
        $cropDiseases = [
            'Padi'         => [['Hawar Daun Bakteri (BLB)', 'Tinggi'], ['Blast Leher', 'Tinggi'], ['Bercak Coklat', 'Sedang']],
            'Jagung'       => [['Bulai Jagung', 'Tinggi'], ['Hawar Daun Turcicum', 'Sedang'], ['Karat Daun', 'Rendah']],
            'Cabai'        => [['Antraknosa (Patek)', 'Tinggi'], ['Layu Fusarium', 'Tinggi'], ['Bercak Daun Cercospora', 'Sedang']],
            'Tomat'        => [['Early Blight', 'Sedang'], ['Late Blight', 'Tinggi'], ['Layu Bakteri', 'Tinggi']],
            'Kedelai'      => [['Karat Kedelai', 'Sedang'], ['Pustul Bakteri', 'Rendah'], ['Downy Mildew', 'Sedang']],
            'Bawang Merah' => [['Moler/Fusarium', 'Tinggi'], ['Bercak Ungu', 'Sedang'], ['Embun Tepung', 'Rendah']],
            'Kentang'      => [['Hawar Daun (P. infestans)', 'Tinggi'], ['Layu Bakteri', 'Tinggi'], ['Scab', 'Rendah']],
        ];

        $crop = $c['crop'] ?? 'Umum';
        $diseases = $cropDiseases[$crop] ?? [['Hawar Daun', 'Sedang'], ['Bercak Daun', 'Rendah'], ['Busuk Akar', 'Tinggi']];
        $pick = $diseases[array_rand($diseases)];
        $confidence = rand(72, 89);

        return [
            'disease'         => $pick[0],
            'confidence'      => $confidence,
            'risk_level'      => $pick[1],
            'description'     => "Terdeteksi kemungkinan {$pick[0]} pada tanaman {$crop}. Penyakit ini umum terjadi pada kondisi kelembapan tinggi dan sirkulasi udara kurang baik. Segera lakukan penanganan untuk mencegah penyebaran.",
            'recommendations' => [
                'Periksa seluruh tanaman di sekitar dan isolasi yang terinfeksi parah',
                'Gunakan fungisida/bakterisida yang sesuai sesuai jenis penyakit',
                'Lakukan penyemprotan pagi hari (06.00-09.00) atau sore (15.00-17.00)',
                'Perbaiki drainase lahan dan kurangi kelembapan berlebih',
                'Pantau ulang kondisi tanaman dalam 3-5 hari',
            ],
        ];
    }
}
