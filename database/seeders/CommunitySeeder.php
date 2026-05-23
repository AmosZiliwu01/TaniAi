<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role','user')->get();
        $topics = [
            ['Cara mengatasi wereng pada padi','Saya sudah coba beberapa cara namun masih ada serangan ringan. Ada yang punya tips?'],
            ['Pupuk organik terbaik untuk cabai','Mencari rekomendasi pupuk organik yang hasilnya bagus untuk cabai keriting.'],
            ['Pengalaman tanam jagung musim hujan','Berbagi pengalaman menanam jagung di musim hujan yang panjang.'],
            ['Diskusi: Harga gabah turun','Apakah teman-teman juga mengalami penurunan harga gabah minggu ini?'],
        ];
        foreach ($topics as $i => $t) {
            $user = $users[$i % count($users)];
            $post = CommunityPost::create([
                'user_id' => $user->id,
                'title' => $t[0],
                'content' => $t[1],
                'likes' => rand(5,42),
                'category' => 'Diskusi',
            ]);
            for ($j=0; $j<rand(1,3); $j++) {
                Comment::create([
                    'user_id' => $users->random()->id,
                    'community_post_id' => $post->id,
                    'content' => 'Terima kasih informasinya, sangat membantu!',
                ]);
            }
        }
    }
}
