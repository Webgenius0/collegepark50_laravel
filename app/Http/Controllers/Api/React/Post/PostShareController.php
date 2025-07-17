<?php

namespace App\Http\Controllers\Api\React\Post;

use App\Models\Post;
use App\Models\PostShare;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostShareController extends Controller
{
    use ApiResponse;

    //store post share and inchiment share count
    public function store(Request $request, $postId)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $post = Post::find($postId);
        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $userId = auth('api')->id();

        // Check if this user already shared the post
        $alreadyShared = PostShare::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($alreadyShared) {
            return $this->error([], 'You have already shared this post.', 409);
        }

        $share = PostShare::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'message' => $request->message,
        ]);

        $post->increment('share_count');

        $share->load('user');

        $response = [
            'id'          => $share->id,
            'user_id'     => $share->user->id,
            'share_count' => $post->share_count,
            'user'        => [
                'id'     => $share->user->id,
                'name'   => $share->user->f_name . ' ' . $share->user->l_name,
                'avatar' => $share->user->avatar,
            ],
        ];

        return $this->success($response, 'Post shared successfully.', 201);
    }

    //index share
    public function index($postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $shares = PostShare::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->get();

        $response = $shares->map(function ($share) {
            return [
                'id'      => $share->id,
                'user_id' => $share->user_id,
                'user'    => [
                    'id'     => $share->user->id,
                    'name'   => $share->user->f_name . ' ' . $share->user->l_name,
                    'avatar' => $share->user->avatar,
                ],
            ];
        });

        return $this->success($response, 'Post shares retrieved successfully.');
    }


    //update share
    public function update(Request $request, $id)
    {
        $share = PostShare::with('user') // eager load user for response
            ->where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$share) {
            return $this->error([], 'Share not found or unauthorized.', 404);
        }

        $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $share->update([
            'message' => $request->message,
        ]);

        $response = [
            'id'      => $share->id,
            'user_id' => $share->user->id,
            'user'    => [
                'id'     => $share->user->id,
                'name'   => $share->user->f_name . ' ' . $share->user->l_name,
                'avatar' => $share->user->avatar,
            ],
        ];

        return $this->success($response, 'Share updated successfully.');
    }


    // delete share
    public function destroy($id)
    {
        $share = PostShare::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$share) {
            return $this->error([], 'Share not found or unauthorized.', 404);
        }

        $share->post->decrement('share_count');
        $share->delete();

        return $this->success([], 'Share deleted successfully.');
    }
}
