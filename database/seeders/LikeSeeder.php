<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userIds = User::pluck('id');
        $posts = Post::all();
        $events = Event::all();

        if ($userIds->isEmpty() || ($posts->isEmpty() && $events->isEmpty())) {
            $this->command->warn('Users or posts/events not found.');
            return;
        }

        // Likes for Posts
        foreach ($posts as $post) {
            $likers = $userIds->random(rand(1, 5));

            foreach ($likers as $userId) {
                Like::create([
                    'user_id'       => $userId,
                    'likeable_id'   => $post->id,
                    'likeable_type' => Post::class,
                ]);
                $post->increment('like_count');
            }
        }

        // Likes for Events
        foreach ($events as $event) {
            $likers = $userIds->random(rand(1, 5));

            foreach ($likers as $userId) {
                Like::create([
                    'user_id'       => $userId,
                    'likeable_id'   => $event->id,
                    'likeable_type' => Event::class,
                ]);
                $event->increment('like_count');
            }
        }
    }
}
