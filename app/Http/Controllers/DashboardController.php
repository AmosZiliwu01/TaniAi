<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\MarketPrice;
use App\Models\CommunityPost;
use App\Models\CropRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── Real diagnosa stats ─────────────────────────────────────────────
        $allDiagnoses = Diagnosis::where('user_id', $user->id)->latest()->limit(50)->get();
        $diagCount    = $allDiagnoses->count();

        // Real health rate from diagnoses (no fake %)
        $healthData = $this->calcHealthRate($allDiagnoses);

        // Real active alerts
        $alerts = $this->buildAlerts($user, $allDiagnoses);

        // Real harvest prediction from crop records
        $harvestPrediction = $this->calcHarvestPrediction($user);

        $stats = [
            'diagnoses'    => $diagCount,
            'health'       => $healthData,
            'alerts'       => $alerts,
            'harvest'      => $harvestPrediction,
        ];

        $recent_diagnoses = $allDiagnoses->take(5);

        // Market data
        try {
            $market = MarketPrice::latest('recorded_at')->limit(6)->get();
            if ($market->isEmpty()) {
                $market = collect($this->marketFallback());
            }
        } catch (\Throwable $e) {
            $market = collect($this->marketFallback());
        }

        // Weather - real via OpenWeather or fallback
        $weather    = $this->fetchWeather($user);
        $weatherCurrent = $weather['current'] ?? null;
        $weatherForecast = collect($weather['forecast'] ?? []);

        $posts = CommunityPost::with('user')->latest()->limit(4)->get();

        return view('dashboard.index', compact(
            'stats', 'recent_diagnoses', 'market',
            'weatherCurrent', 'weatherForecast', 'posts', 'user'
        ));
    }

    private function calcHealthRate($diagnoses): array
    {
        if ($diagnoses->isEmpty()) {
            return ['has_data' => false, 'rate' => null, 'label' => null, 'last_date' => null];
        }

        // Health rate: Rendah=100%, Sedang=60%, Tinggi=0%
        $scores = $diagnoses->take(10)->map(function ($d) {
            return match($d->risk_level) {
                'Rendah' => 100,
                'Sedang' => 55,
                'Tinggi' => 10,
                default  => 70,
            };
        });

        $avg   = round($scores->average());
        $label = $avg >= 75 ? 'Baik' : ($avg >= 50 ? 'Perlu Perhatian' : 'Kritis');

        return [
            'has_data'  => true,
            'rate'      => $avg,
            'label'     => $label,
            'last_date' => $diagnoses->first()->created_at->isoFormat('D MMM Y'),
        ];
    }

    private function buildAlerts($user, $diagnoses): array
    {
        $alerts = [];

        // Alert: penyakit risiko tinggi
        $highRisk = $diagnoses->where('risk_level', 'Tinggi')->first();
        if ($highRisk) {
            $alerts[] = [
                'type'   => 'danger',
                'icon'   => 'alert-triangle',
                'title'  => 'Penyakit Risiko Tinggi',
                'desc'   => $highRisk->disease . ' pada ' . $highRisk->crop,
                'time'   => $highRisk->created_at->diffForHumans(),
                'action' => route('diagnosis.show', $highRisk),
            ];
        }

        // Alert: belum diagnosa 14+ hari
        $lastDiag = $diagnoses->first();
        if (!$lastDiag || $lastDiag->created_at->diffInDays(now()) > 14) {
            $alerts[] = [
                'type'   => 'warning',
                'icon'   => 'scan-line',
                'title'  => 'Tanaman Belum Dipantau',
                'desc'   => 'Sudah ' . ($lastDiag ? $lastDiag->created_at->diffInDays(now()) : '14') . ' hari sejak diagnosa terakhir.',
                'time'   => 'Sekarang',
                'action' => route('diagnosis.index'),
            ];
        }

        // Alert: cuaca ekstrem dari cache jika ada
        $weatherAlert = Cache::get('weather_alert_' . ($user->location ?? 'default'));
        if ($weatherAlert) {
            $alerts[] = [
                'type'   => 'info',
                'icon'   => 'cloud-rain',
                'title'  => 'Peringatan Cuaca',
                'desc'   => $weatherAlert,
                'time'   => now()->isoFormat('HH:mm'),
                'action' => route('weather.index'),
            ];
        }

        // Alert: harga pasar naik signifikan
        try {
            $hotMarket = MarketPrice::where('change_percent', '>', 10)->latest()->first();
            if ($hotMarket) {
                $alerts[] = [
                    'type'   => 'success',
                    'icon'   => 'trending-up',
                    'title'  => 'Harga ' . $hotMarket->commodity . ' Naik',
                    'desc'   => 'Naik ' . number_format($hotMarket->change_percent, 1) . '% — pertimbangkan jual sekarang.',
                    'time'   => $hotMarket->recorded_at ? \Carbon\Carbon::parse($hotMarket->recorded_at)->diffForHumans() : 'Baru',
                    'action' => route('market.index'),
                ];
            }
        } catch (\Throwable $e) {}

        return $alerts;
    }

    private function calcHarvestPrediction($user): array
    {
        try {
            $records = CropRecord::where('user_id', $user->id)
                ->whereNotNull('planting_date')
                ->whereNotIn('status', ['Sudah Panen'])
                ->get();

            if ($records->isEmpty()) {
                return ['has_data' => false];
            }

            // Harvest days per crop type
            $harvestDays = [
                'Padi' => 110, 'Jagung' => 95, 'Cabai' => 80,
                'Tomat' => 75, 'Kedelai' => 90, 'Bawang Merah' => 65,
                'Kentang' => 100, 'Kopi' => 1095, 'Kakao' => 1095,
            ];

            $predictions = [];
            foreach ($records as $r) {
                $planted = \Carbon\Carbon::parse($r->planting_date);
                $days    = $harvestDays[$r->crop] ?? 90;
                $harvestDate = $planted->addDays($days);
                if ($harvestDate->isFuture()) {
                    $predictions[] = [
                        'crop'         => $r->crop,
                        'field'        => $r->field_name,
                        'harvest_date' => $harvestDate->isoFormat('D MMM Y'),
                        'days_left'    => max(0, now()->diffInDays($harvestDate, false)),
                    ];
                }
            }

            if (empty($predictions)) return ['has_data' => false];

            $nearest = collect($predictions)->sortBy('days_left')->first();

            return [
                'has_data'     => true,
                'crop'         => $nearest['crop'],
                'field'        => $nearest['field'],
                'harvest_date' => $nearest['harvest_date'],
                'days_left'    => $nearest['days_left'],
                'all'          => $predictions,
            ];
        } catch (\Throwable $e) {
            return ['has_data' => false];
        }
    }

    private function fetchWeather($user): array
    {
        $location = $user->location ?? '';
        $apiKey   = env('OPENWEATHER_API_KEY');

        if ($apiKey && $location) {
            $cacheKey = 'dash_weather_' . md5($location);
            $data = Cache::remember($cacheKey, 1800, function () use ($location, $apiKey) {
                try {
                    $res = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/forecast', [
                        'q'     => $location . ',ID',
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang'  => 'id',
                        'cnt'   => 40,
                    ]);
                    return $res->successful() ? $res->json() : null;
                } catch (\Throwable $e) { return null; }
            });

            if ($data && isset($data['list'])) {
                $tzOffset = $data['city']['timezone'] ?? 25200;
                $todayLocal = gmdate('Y-m-d', time() + $tzOffset);
                $byDay = [];
                foreach ($data['list'] as $item) {
                    $day = gmdate('Y-m-d', $item['dt'] + $tzOffset);
                    if (!isset($byDay[$day]) && $day >= $todayLocal) {
                        $byDay[$day] = $item;
                    }
                }
                ksort($byDay);
                $forecast = [];
                foreach (array_slice($byDay, 0, 7, true) as $day => $item) {
                    $forecast[] = (object)[
                        'forecast_date' => $day,
                        'temperature'   => (int) round($item['main']['temp']),
                        'condition'     => ucwords($item['weather'][0]['description'] ?? 'berawan'),
                        'humidity'      => $item['main']['humidity'] ?? 70,
                        'wind_speed'    => (int) round(($item['wind']['speed'] ?? 3) * 3.6),
                        'rainfall'      => round($item['rain']['3h'] ?? 0, 1),
                        'icon'          => $item['weather'][0]['icon'] ?? '02d',
                        'location'      => $data['city']['name'] ?? $location,
                    ];
                }

                // Store weather alert
                $maxRain = max(array_map(fn($f) => $f->rainfall, $forecast));
                if ($maxRain >= 10) {
                    Cache::put('weather_alert_' . ($user->location ?? 'default'),
                        "Curah hujan tinggi ({$maxRain}mm) diprediksi. Periksa drainase lahan.", 7200);
                }

                return ['current' => $forecast[0] ?? null, 'forecast' => $forecast, 'real' => true];
            }
        }

        // Fallback
        $forecast = [];
        $conditions = [['Cerah','01d'],['Berawan','02d'],['Hujan Ringan','10d'],['Cerah','01d'],['Berawan','03d'],['Cerah','01d'],['Hujan','09d']];
        for ($i = 0; $i < 7; $i++) {
            [$cond, $icon] = $conditions[$i];
            $forecast[] = (object)[
                'forecast_date' => now()->addDays($i)->format('Y-m-d'),
                'temperature'   => 28 + ($i % 4),
                'condition'     => $cond,
                'humidity'      => 72,
                'wind_speed'    => 12,
                'rainfall'      => str_contains($cond, 'Hujan') ? 8 : 0,
                'icon'          => $icon,
                'location'      => $location ?: 'Indonesia',
            ];
        }
        return ['current' => $forecast[0], 'forecast' => $forecast, 'real' => false];
    }

    private function marketFallback(): array
    {
        $now = now();
        return [
            (object)['commodity'=>'Cabai Merah','price'=>48000,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>12.5,'recorded_at'=>$now],
            (object)['commodity'=>'Bawang Merah','price'=>32000,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>8.2,'recorded_at'=>$now],
            (object)['commodity'=>'Tomat','price'=>14000,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>5.4,'recorded_at'=>$now],
            (object)['commodity'=>'Cabai Rawit','price'=>60000,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>15.3,'recorded_at'=>$now],
            (object)['commodity'=>'Kentang','price'=>11000,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>-2.1,'recorded_at'=>$now],
            (object)['commodity'=>'Gabah Kering','price'=>5500,'unit'=>'kg','region'=>'Jawa Tengah','change_percent'=>2.3,'recorded_at'=>$now],
        ];
    }
}
