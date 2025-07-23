<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Hashtag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userIds = User::pluck('id');
        $hashtagIds = Hashtag::pluck('id');

        if ($userIds->isEmpty() || $hashtagIds->isEmpty()) {
            $this->command->warn('Seed users and hashtags first.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $post = Post::create([
                'user_id'       => $userIds->random(),
                'content'       => fake()->paragraphs(rand(1, 3), true),
                'like_count'    => 0,
                'comment_count' => 0,
                'share_count'   => 0,
            ]);

            // attach 1 to 5 random hashtags
            $post->hashtags()->attach(
                $hashtagIds->random(rand(1, 5))
            );
        }
    }
}
