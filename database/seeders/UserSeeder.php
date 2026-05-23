<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin TaniAI',
            'email' => 'admin@taniai.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'location' => 'Jakarta',
            'farmer_type' => 'Administrator',
        ]);

        $names = [
            ['Budi Santoso','Sleman, DIY','Padi'],
            ['Siti Aminah','Garut, Jabar','Cabai'],
            ['Ahmad Fauzi','Malang, Jatim','Jagung'],
            ['Dewi Lestari','Brebes, Jateng','Bawang Merah'],
            ['Joko Susanto','Lampung','Kopi'],
        ];
        foreach ($names as [$n,$loc,$type]) {
            User::create([
                'name' => $n,
                'email' => strtolower(str_replace(' ','.',$n)).'@taniai.local',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location' => $loc,
                'farmer_type' => 'Petani '.$type,
            ]);
        }
    }
}
