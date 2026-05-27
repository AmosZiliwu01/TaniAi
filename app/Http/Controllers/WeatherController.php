<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $location  = trim($user->location ?? '');
        $apiKey    = env('OPENWEATHER_API_KEY');
        $forecast  = [];
        $current   = null;
        $alert     = null;
        $usingReal = false;
        $errorMsg  = null;

        if ($apiKey && $location) {
            $cacheKey = 'weather_v3_' . md5($location);
            $data = Cache::remember($cacheKey, 1800, function () use ($location, $apiKey) {
                try {
                    $res = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/forecast', [
                        'q'     => $location . ',ID',
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang'  => 'id',
                        'cnt'   => 40,
                    ]);
                    if ($res->successful()) return $res->json();
                    Log::warning('OWM failed', ['status' => $res->status(), 'loc' => $location]);
                } catch (\Throwable $e) {
                    Log::error('OWM error', ['msg' => $e->getMessage()]);
                }
                return null;
            });

            if ($data && isset($data['list'])) {
                $usingReal  = true;
                $tzOffset   = $data['city']['timezone'] ?? 25200;
                $todayLocal = gmdate('Y-m-d', time() + $tzOffset);

                $byDay = [];
                foreach ($data['list'] as $item) {
                    $day = gmdate('Y-m-d', $item['dt'] + $tzOffset);
                    if (!isset($byDay[$day]) && $day >= $todayLocal) {
                        $byDay[$day] = $item;
                    }
                }
                ksort($byDay);

                foreach (array_slice($byDay, 0, 7, true) as $day => $item) {
                    $forecast[] = (object)[
                        'forecast_date' => $day,
                        'temperature'   => (int) round($item['main']['temp']),
                        'feels_like'    => (int) round($item['main']['feels_like'] ?? $item['main']['temp']),
                        'humidity'      => (int) ($item['main']['humidity'] ?? 70),
                        'condition'     => ucwords($item['weather'][0]['description'] ?? 'berawan'),
                        'rainfall'      => round($item['rain']['3h'] ?? 0, 1),
                        'wind_speed'    => (int) round(($item['wind']['speed'] ?? 3) * 3.6),
                        'location'      => $data['city']['name'] ?? $location,
                        'icon'          => $item['weather'][0]['icon'] ?? '02d',
                    ];
                }
                $current = $forecast[0] ?? null;

                $maxRain = $forecast ? max(array_map(fn($f) => $f->rainfall, $forecast)) : 0;
                if ($maxRain >= 10) {
                    $alert = "⚠️ Curah hujan tinggi ({$maxRain}mm) diperkirakan dalam 7 hari. Periksa drainase dan lindungi tanaman muda.";
                    Cache::put('weather_alert_'.($user->location??'default'), $alert, 7200);
                }
            } else {
                Cache::forget($cacheKey);
                $errorMsg = "Tidak dapat memuat cuaca untuk \"{$location}\". Pastikan nama lokasi benar.";
            }
        } elseif (!$apiKey) {
            // No API key — use fallback and indicate clearly
        }

        if (!$usingReal && !$errorMsg) {
            $conds = [['Cerah','01d'],['Cerah Berawan','02d'],['Berawan','03d'],['Hujan Ringan','10d'],['Cerah','01d'],['Berawan','03d'],['Hujan','09d']];
            for ($i = 0; $i < 7; $i++) {
                [$c,$ic] = $conds[$i];
                $forecast[] = (object)[
                    'forecast_date' => now()->addDays($i)->format('Y-m-d'),
                    'temperature'   => 28 + ($i % 4),
                    'feels_like'    => 30 + ($i % 3),
                    'humidity'      => 72,
                    'condition'     => $c,
                    'rainfall'      => str_contains($c,'Hujan') ? 8 : 0,
                    'wind_speed'    => 12,
                    'location'      => $location ?: 'Indonesia',
                    'icon'          => $ic,
                ];
            }
            $current = $forecast[0];
        }

        return view('weather.index', compact('forecast','current','alert','location','usingReal','errorMsg'));
    }
}
