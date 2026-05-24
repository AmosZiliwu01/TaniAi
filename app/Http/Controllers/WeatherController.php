<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $location = $user->location ?: 'Jakarta';
        $apiKey   = env('OPENWEATHER_API_KEY');

        $forecast = [];
        $current  = null;
        $alert    = null;
        $usingReal = false;

        if ($apiKey) {
            $cacheKey = 'weather_' . md5($location);
            $data     = Cache::remember($cacheKey, 1800, function () use ($location, $apiKey) {
                try {
                    $res = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/forecast', [
                        'q'     => $location . ',ID',
                        'appid' => $apiKey,
                        'units' => 'metric',
                        'lang'  => 'id',
                        'cnt'   => 40,
                    ]);
                    if ($res->successful()) return $res->json();
                } catch (\Throwable $e) {}
                return null;
            });

            if ($data && isset($data['list'])) {
                $usingReal = true;
                // Group by day
                $byDay = [];
                foreach ($data['list'] as $item) {
                    $day = date('Y-m-d', $item['dt']);
                    if (!isset($byDay[$day])) {
                        $byDay[$day] = $item;
                    }
                }
                foreach (array_slice($byDay, 0, 7, true) as $day => $item) {
                    $desc = $item['weather'][0]['description'] ?? 'berawan';
                    $forecast[] = (object)[
                        'forecast_date' => $day,
                        'temperature'   => round($item['main']['temp']),
                        'humidity'      => $item['main']['humidity'],
                        'rainfall'      => round($item['rain']['3h'] ?? 0, 1),
                        'condition'     => ucfirst($desc),
                        'location'      => $data['city']['name'] ?? $location,
                        'wind_speed'    => round($item['wind']['speed'] * 3.6), // m/s to km/h
                    ];
                }
                $current = $forecast[0] ?? null;

                // Alert: check if rain > 20mm expected
                $maxRain = max(array_column(array_map(fn($f) => ['r' => $f->rainfall], $forecast), 'r'));
                if ($maxRain > 10) {
                    $alert = "Waspada: Diprediksi curah hujan tinggi ({$maxRain}mm) dalam 7 hari ke depan. Siapkan drainase dan lindungi tanaman muda.";
                }
            }
        }

        // Fallback to mock data if no API key or API failed
        if (!$usingReal) {
            $conditions = ['Cerah', 'Berawan', 'Hujan Ringan', 'Cerah Berawan', 'Hujan', 'Cerah', 'Berawan'];
            for ($i = 0; $i < 7; $i++) {
                $date       = date('Y-m-d', strtotime("+{$i} days"));
                $temp       = rand(26, 33);
                $cond       = $conditions[$i];
                $rain       = str_contains($cond, 'Hujan') ? rand(5, 25) : rand(0, 3);
                $forecast[] = (object)[
                    'forecast_date' => $date,
                    'temperature'   => $temp,
                    'humidity'      => rand(65, 85),
                    'rainfall'      => $rain,
                    'condition'     => $cond,
                    'location'      => $location,
                    'wind_speed'    => rand(8, 20),
                ];
            }
            $current = $forecast[0];
            $alert   = null;
        }

        return view('weather.index', compact('forecast', 'current', 'alert', 'location', 'usingReal'));
    }
}
