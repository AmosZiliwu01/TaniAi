<?php

namespace App\Http\Controllers;

use App\Models\MarketPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $isRealtime  = false;
        $lastUpdated = null;

        try {
            $q = MarketPrice::query();
            if ($request->filled('region'))  $q->where('region', $request->region);
            if ($request->filled('search'))  $q->where('commodity', 'like', '%'.$request->search.'%');

            $prices  = $q->latest('recorded_at')->paginate(12)->withQueryString();
            $regions = MarketPrice::select('region')->distinct()->orderBy('region')->pluck('region');
            $trending = MarketPrice::orderByDesc('change_percent')->limit(6)->get();

            if ($prices->isEmpty() && !$request->filled('search') && !$request->filled('region')) {
                $this->seedDemo();
                $prices   = MarketPrice::latest('recorded_at')->paginate(12);
                $trending = MarketPrice::orderByDesc('change_percent')->limit(6)->get();
                $regions  = MarketPrice::select('region')->distinct()->pluck('region');
            }

            $latest = MarketPrice::latest('recorded_at')->first();
            if ($latest && $latest->recorded_at) {
                $isRealtime  = $latest->recorded_at->diffInHours(now()) < 6;
                $lastUpdated = $latest->recorded_at->isoFormat('D MMM, HH:mm') . ' WIB';
            }
        } catch (\Throwable $e) {
            $prices   = $this->staticPaginator();
            $regions  = collect(['Jawa Tengah','Jawa Barat','Jawa Timur','Sulawesi']);
            $trending = collect($this->demoData())->sortByDesc('change_percent')->take(6)->values();
        }

        $sellRecommendations = $this->buildSellRecs($trending ?? collect());

        return view('market.index', compact('prices', 'regions', 'trending', 'isRealtime', 'lastUpdated', 'sellRecommendations'));
    }

    private function buildSellRecs($trending): \Illuminate\Support\Collection
    {
        $recs = [];
        $emojis = ['Cabai'=>'🌶️','Bawang'=>'🧅','Tomat'=>'🍅','Jagung'=>'🌽','Kentang'=>'🥔','Padi'=>'🌾','Gabah'=>'🌾','Kol'=>'🥬','Kopi'=>'☕'];

        foreach ($trending->take(3) as $t) {
            $comm   = is_object($t) ? $t->commodity : ($t['commodity'] ?? '');
            $change = is_object($t) ? ($t->change_percent ?? 0) : ($t['change_percent'] ?? 0);
            $price  = is_object($t) ? ($t->price ?? 0) : ($t['price'] ?? 0);

            $em = '🌿';
            foreach ($emojis as $k => $v) { if (str_contains($comm, $k)) { $em = $v; break; } }

            if ($change >= 8) {
                $recs[] = ['commodity'=>$comm,'emoji'=>$em,'signal'=>'buy','reason'=>"Harga naik {$change}% — momentum tinggi, pertimbangkan jual sekarang sebelum harga turun.",'best_days'=>'3–5 hari ke depan'];
            } elseif ($change >= 3) {
                $recs[] = ['commodity'=>$comm,'emoji'=>$em,'signal'=>'buy','reason'=>"Tren positif +{$change}%. Harga stabil naik, waktu baik untuk menjual.",'best_days'=>'Minggu ini'];
            } elseif ($change < -5) {
                $recs[] = ['commodity'=>$comm,'emoji'=>$em,'signal'=>'wait','reason'=>"Harga turun {$change}%. Tahan dulu, tunggu pemulihan harga dalam 1–2 minggu.",'best_days'=>null];
            }
        }

        return collect($recs);
    }

    private function seedDemo(): void
    {
        foreach ($this->demoData() as $r) {
            MarketPrice::firstOrCreate(['commodity'=>$r['commodity'],'region'=>$r['region']], $r);
        }
    }

    private function demoData(): array
    {
        $now = now();
        return [
            ['commodity'=>'Cabai Merah','unit'=>'kg','price'=>48000,'region'=>'Jawa Tengah','change_percent'=>12.5,'recorded_at'=>$now],
            ['commodity'=>'Cabai Rawit','unit'=>'kg','price'=>60000,'region'=>'Jawa Tengah','change_percent'=>15.3,'recorded_at'=>$now],
            ['commodity'=>'Bawang Merah','unit'=>'kg','price'=>32000,'region'=>'Jawa Tengah','change_percent'=>8.2,'recorded_at'=>$now],
            ['commodity'=>'Tomat','unit'=>'kg','price'=>14000,'region'=>'Jawa Tengah','change_percent'=>5.4,'recorded_at'=>$now],
            ['commodity'=>'Kentang','unit'=>'kg','price'=>11000,'region'=>'Jawa Tengah','change_percent'=>-2.1,'recorded_at'=>$now],
            ['commodity'=>'Kol','unit'=>'kg','price'=>6000,'region'=>'Jawa Tengah','change_percent'=>-1.3,'recorded_at'=>$now],
            ['commodity'=>'Gabah Kering','unit'=>'kg','price'=>5500,'region'=>'Jawa Tengah','change_percent'=>2.3,'recorded_at'=>$now],
            ['commodity'=>'Beras Medium','unit'=>'kg','price'=>12500,'region'=>'Jawa Tengah','change_percent'=>1.5,'recorded_at'=>$now],
            ['commodity'=>'Jagung Pipil','unit'=>'kg','price'=>4800,'region'=>'Jawa Tengah','change_percent'=>-0.5,'recorded_at'=>$now],
            ['commodity'=>'Kedelai Lokal','unit'=>'kg','price'=>9500,'region'=>'Jawa Tengah','change_percent'=>0.8,'recorded_at'=>$now],
            ['commodity'=>'Cabai Merah','unit'=>'kg','price'=>49000,'region'=>'Jawa Barat','change_percent'=>13.1,'recorded_at'=>$now],
            ['commodity'=>'Bawang Merah','unit'=>'kg','price'=>30000,'region'=>'Jawa Barat','change_percent'=>6.5,'recorded_at'=>$now],
            ['commodity'=>'Tomat','unit'=>'kg','price'=>13500,'region'=>'Jawa Barat','change_percent'=>4.2,'recorded_at'=>$now],
            ['commodity'=>'Gabah Kering','unit'=>'kg','price'=>5600,'region'=>'Jawa Timur','change_percent'=>2.8,'recorded_at'=>$now],
            ['commodity'=>'Jagung Pipil','unit'=>'kg','price'=>4700,'region'=>'Jawa Timur','change_percent'=>-0.8,'recorded_at'=>$now],
            ['commodity'=>'Kopi Arabika','unit'=>'kg','price'=>95000,'region'=>'Sulawesi','change_percent'=>3.5,'recorded_at'=>$now],
            ['commodity'=>'Kakao Fermentasi','unit'=>'kg','price'=>85000,'region'=>'Sulawesi','change_percent'=>2.2,'recorded_at'=>$now],
            ['commodity'=>'Bawang Putih','unit'=>'kg','price'=>35000,'region'=>'Jawa Tengah','change_percent'=>1.8,'recorded_at'=>$now],
            ['commodity'=>'Wortel','unit'=>'kg','price'=>8500,'region'=>'Jawa Barat','change_percent'=>3.2,'recorded_at'=>$now],
        ];
    }

    private function staticPaginator()
    {
        $items = collect(array_map(fn($r) => (object)$r, $this->demoData()));
        $page  = request()->get('page', 1);
        $slice = $items->slice(($page-1)*12, 12)->values();
        return new \Illuminate\Pagination\LengthAwarePaginator($slice, $items->count(), 12, $page, ['path'=>route('market.index')]);
    }
}
