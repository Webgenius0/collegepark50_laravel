<?php

namespace App\Http\Controllers\Api\React\Post;

use Exception;
use App\Models\Post;
use App\Models\PostLike;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostLikeController extends Controller
{
    use ApiResponse;

    //like and unlike post
    public function toggleLike($postId)
    {
        $user = auth('api')->user();

        // Check if the post exists
        $post = Post::find($postId);
        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        // Check if the user already liked the post
        $alreadyLiked = PostLike::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->first();

        if ($alreadyLiked) {
            // Unlike the post
            $alreadyLiked->delete();
            $post->decrement('like_count');

            return $this->success([
                'post_id'    => $post->id,
                'user_id'    => $user->id,
                'status'     => 'unliked',
                'like_count' => $post->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Post unliked successfully.', 200);
        } else {
            // Like the post
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);
            $post->increment('like_count');

            return $this->success([
                'post_id'    => $post->id,
                'user_id'    => $user->id,
                'status'     => 'liked',
                'like_count' => $post->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Post liked successfully.', 200);
        }
    }


    //get all likes
    public function index($postId)
    {
        $post = Post::with('likes.user')->find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $likeUsers = $post->likes->map(function ($like) {
            return [
                'id' => $like->user->id,
                'name' => $like->user->f_name . ' ' . $like->user->l_name,
                'avatar' => $like->user->avatar
            ];
        });

        return $this->success($likeUsers, 'Post like list retrieved successfully.', 200);
    }
}
