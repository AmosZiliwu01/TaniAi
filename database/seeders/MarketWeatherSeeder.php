<?php

namespace Database\Seeders;

use App\Models\MarketPrice;
use App\Models\WeatherLog;
use Illuminate\Database\Seeder;

class MarketWeatherSeeder extends Seeder
{
    public function run(): void
    {
        $commodities = [
            ['Padi',6200,2.3],['Jagung',5100,-1.1],['Cabai Merah',48000,5.7],
            ['Bawang Merah',28000,-0.8],['Kedelai',11000,1.5],['Kopi',62000,3.2],
            ['Kakao',32000,0.4],['Tomat',8500,-2.1],['Kentang',12000,1.0],
            ['Bawang Putih',34000,0.6],
        ];
        $regions = ['Sleman, DIY','Garut, Jabar','Malang, Jatim','Brebes, Jateng','Lampung'];
        foreach ($commodities as [$c,$p,$ch]) {
            foreach ($regions as $r) {
                MarketPrice::create([
                    'commodity'=>$c,'region'=>$r,
                    'price'=>$p + rand(-300,300),
                    'unit'=>'kg',
                    'change_percent'=>$ch + (rand(-50,50)/100),
                    'recorded_at'=>now()->subHours(rand(1,24)),
                ]);
            }
        }

        $conds = ['Cerah','Berawan','Hujan','Hujan Lebat','Petir','Berawan','Cerah'];
        for ($i=0; $i<7; $i++) {
            WeatherLog::create([
                'location'=>'Sleman, DIY',
                'temperature'=>27 - ($i%3),
                'humidity'=>70 + rand(0,15),
                'rainfall'=>$i>=3 && $i<=5 ? rand(10,40) : rand(0,5),
                'condition'=>$conds[$i],
                'forecast_date'=>now()->addDays($i)->toDateString(),
            ]);
        }
    }
}
