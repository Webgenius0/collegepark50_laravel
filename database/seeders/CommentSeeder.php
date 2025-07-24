<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommentSeeder extends Seeder
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

        // Helper function to seed comments for a given model collection and type
        $seedCommentsForModel = function ($models, $type) use ($userIds) {
            foreach ($models as $model) {
                $commentCount = rand(1, 3);

                for ($i = 0; $i < $commentCount; $i++) {
                    $comment = Comment::create([
                        'user_id'         => $userIds->random(),
                        'commentable_id'  => $model->id,
                        'commentable_type' => $type,
                        'comment'         => fake()->sentence(),
                    ]);

                    // Randomly add 0-2 replies to this comment
                    $replyCount = rand(0, 2);

                    for ($j = 0; $j < $replyCount; $j++) {
                        Comment::create([
                            'user_id'         => $userIds->random(),
                            'commentable_id'  => $model->id,
                            'commentable_type' => $type,
                            'parent_id'       => $comment->id,
                            'comment'         => fake()->sentence(),
                        ]);
                    }
                }

                // Update comment_count for the model
                $total = Comment::where('commentable_type', $type)
                    ->where('commentable_id', $model->id)
                    ->count();

                $model->update(['comment_count' => $total]);
            }
        };

        // Seed comments for posts
        $seedCommentsForModel($posts, Post::class);

        // Seed comments for events
        $seedCommentsForModel($events, Event::class);
    }
}
