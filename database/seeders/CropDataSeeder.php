<?php

namespace Database\Seeders;

use App\Models\Diagnosis;
use App\Models\CropRecord;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Seeder;

class CropDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role','user')->get();
        foreach ($users as $u) {
            CropRecord::create(['user_id'=>$u->id,'crop'=>'Padi','field_name'=>'Sawah Utara','area'=>0.5,'planting_date'=>now()->subDays(30),'status'=>'Tumbuh Baik','notes'=>'Pemupukan UREA tahap awal.']);
            CropRecord::create(['user_id'=>$u->id,'crop'=>'Jagung','field_name'=>'Lahan Belakang','area'=>0.3,'planting_date'=>now()->subDays(50),'status'=>'Perlu Perawatan','notes'=>'Cek serangan hama wereng.']);

            Diagnosis::create([
                'user_id'=>$u->id,'crop'=>'Padi','disease'=>'Hawar Daun (Blight)','confidence'=>92,'risk_level'=>'Tinggi',
                'description'=>'Penyakit disebabkan oleh jamur Pyricularia oryzae yang menyerang daun padi.',
                'recommendations'=>['Semprot fungisida Propineb 70WP','Jaga jarak tanam','Perbaiki sirkulasi udara'],
                'status'=>'done',
            ]);
            Diagnosis::create([
                'user_id'=>$u->id,'crop'=>'Cabai','disease'=>'Bercak Daun','confidence'=>85,'risk_level'=>'Sedang',
                'description'=>'Bercak coklat muncul akibat kelembapan tinggi.',
                'recommendations'=>['Aplikasi fungisida mancozeb','Pangkas daun yang terinfeksi'],
                'status'=>'done',
            ]);

            Recommendation::create([
                'user_id'=>$u->id,'crop'=>'Padi','title'=>'Pemupukan UREA Dianjurkan',
                'content'=>'Berdasarkan fase tanaman Anda, lakukan pemupukan UREA 50 kg/ha dalam 3 hari ke depan.',
                'priority'=>'high',
            ]);
            Recommendation::create([
                'user_id'=>$u->id,'crop'=>'Jagung','title'=>'Waspada Curah Hujan',
                'content'=>'Curah hujan tinggi diprediksi 2 hari ke depan. Siapkan drainase.',
                'priority'=>'medium',
            ]);
        }
    }
}
