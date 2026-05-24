<?php

namespace App\Http\Controllers;

use App\Models\MarketPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $q = MarketPrice::query();

            if ($request->filled('region')) {
                $q->where('region', $request->region);
            }

            if ($request->filled('search')) {
                $q->where('commodity', 'like', '%' . $request->search . '%');
            }

            $prices  = $q->latest('recorded_at')->limit(50)->get();
            $regions = MarketPrice::select('region')->distinct()->orderBy('region')->pluck('region');

            // Jika belum ada data sama sekali, seed dengan data demo
            if ($prices->isEmpty()) {
                $this->seedDemoData();
                $prices  = MarketPrice::latest('recorded_at')->limit(50)->get();
                $regions = MarketPrice::select('region')->distinct()->orderBy('region')->pluck('region');
            }

            return view('market.index', compact('prices', 'regions'));
        } catch (\Throwable $e) {
            // Fallback jika tabel belum ada / error DB
            $prices  = collect($this->getFallbackData());
            $regions = $prices->pluck('region')->unique()->values();
            return view('market.index', compact('prices', 'regions'));
        }
    }

    protected function seedDemoData(): void
    {
        $data = $this->getFallbackData();
        foreach ($data as $row) {
            MarketPrice::updateOrCreate(
                ['commodity' => $row['commodity'], 'region' => $row['region']],
                $row
            );
        }
    }

    protected function getFallbackData(): array
    {
        $now = now();
        return [
            ['commodity' => 'Gabah Kering Panen', 'unit' => 'kg', 'price' => 5500, 'region' => 'Jawa Tengah', 'change_percent' => 2.3, 'recorded_at' => $now],
            ['commodity' => 'Beras Medium', 'unit' => 'kg', 'price' => 12500, 'region' => 'Jawa Tengah', 'change_percent' => 1.5, 'recorded_at' => $now],
            ['commodity' => 'Jagung Pipil', 'unit' => 'kg', 'price' => 4800, 'region' => 'Jawa Tengah', 'change_percent' => -0.5, 'recorded_at' => $now],
            ['commodity' => 'Cabai Merah Keriting', 'unit' => 'kg', 'price' => 32000, 'region' => 'Jawa Tengah', 'change_percent' => 8.2, 'recorded_at' => $now],
            ['commodity' => 'Cabai Rawit Merah', 'unit' => 'kg', 'price' => 45000, 'region' => 'Jawa Tengah', 'change_percent' => 12.5, 'recorded_at' => $now],
            ['commodity' => 'Tomat', 'unit' => 'kg', 'price' => 8500, 'region' => 'Jawa Tengah', 'change_percent' => -3.2, 'recorded_at' => $now],
            ['commodity' => 'Bawang Merah', 'unit' => 'kg', 'price' => 28000, 'region' => 'Jawa Tengah', 'change_percent' => 5.1, 'recorded_at' => $now],
            ['commodity' => 'Bawang Putih', 'unit' => 'kg', 'price' => 35000, 'region' => 'Jawa Tengah', 'change_percent' => 1.8, 'recorded_at' => $now],
            ['commodity' => 'Kedelai Lokal', 'unit' => 'kg', 'price' => 9500, 'region' => 'Jawa Tengah', 'change_percent' => 0.8, 'recorded_at' => $now],
            ['commodity' => 'Kentang', 'unit' => 'kg', 'price' => 14000, 'region' => 'Jawa Tengah', 'change_percent' => -1.5, 'recorded_at' => $now],
            ['commodity' => 'Gabah Kering Panen', 'unit' => 'kg', 'price' => 5600, 'region' => 'Jawa Barat', 'change_percent' => 2.5, 'recorded_at' => $now],
            ['commodity' => 'Beras Medium', 'unit' => 'kg', 'price' => 12800, 'region' => 'Jawa Barat', 'change_percent' => 1.8, 'recorded_at' => $now],
            ['commodity' => 'Cabai Merah Keriting', 'unit' => 'kg', 'price' => 33000, 'region' => 'Jawa Barat', 'change_percent' => 9.0, 'recorded_at' => $now],
            ['commodity' => 'Bawang Merah', 'unit' => 'kg', 'price' => 27500, 'region' => 'Jawa Barat', 'change_percent' => 4.8, 'recorded_at' => $now],
            ['commodity' => 'Gabah Kering Panen', 'unit' => 'kg', 'price' => 5400, 'region' => 'Jawa Timur', 'change_percent' => 1.9, 'recorded_at' => $now],
            ['commodity' => 'Jagung Pipil', 'unit' => 'kg', 'price' => 4700, 'region' => 'Jawa Timur', 'change_percent' => -0.8, 'recorded_at' => $now],
            ['commodity' => 'Cabai Rawit Merah', 'unit' => 'kg', 'price' => 43000, 'region' => 'Jawa Timur', 'change_percent' => 10.2, 'recorded_at' => $now],
            ['commodity' => 'Kopi Arabika', 'unit' => 'kg', 'price' => 95000, 'region' => 'Sulawesi', 'change_percent' => 3.5, 'recorded_at' => $now],
            ['commodity' => 'Kakao Fermentasi', 'unit' => 'kg', 'price' => 85000, 'region' => 'Sulawesi', 'change_percent' => 2.2, 'recorded_at' => $now],
        ];
    }
}
