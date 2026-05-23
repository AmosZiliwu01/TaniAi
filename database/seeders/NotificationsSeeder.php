<?php

namespace Database\Seeders;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $u) {
            NotificationLog::create(['user_id'=>$u->id,'title'=>'Peringatan Dini','message'=>'Curah hujan tinggi 2 hari ke depan di wilayah Anda.','type'=>'warning']);
            NotificationLog::create(['user_id'=>$u->id,'title'=>'Rekomendasi AI','message'=>'TaniAI menyarankan pemupukan UREA 50kg/ha dalam 3 hari.','type'=>'info']);
            NotificationLog::create(['user_id'=>$u->id,'title'=>'Diagnosa Selesai','message'=>'Hasil diagnosa terbaru telah tersedia.','type'=>'success','read_at'=>now()->subDay()]);
        }
    }
}
