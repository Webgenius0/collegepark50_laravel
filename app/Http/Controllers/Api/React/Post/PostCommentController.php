<?php

namespace App\Http\Controllers\Api\React\Post;

use App\Models\Post;
use App\Models\PostComment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PostCommentController extends Controller
{
    use ApiResponse;
    // Add comment
    public function store(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error(['Validation failed'], $validator->errors()->first(), 422);
        }

        $post = Post::find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $user = auth('api')->user();

        // Save comment
        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        // Load user relation for response
        $comment->load('user');

        $response = [
            'id'      => $comment->id,
            'user_id' => $comment->user->id,
            'comment' => $comment->comment,
            'comment_count' => $post->comment_count,
            'user'    => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                'avatar' => $comment->user->avatar,
            ],
        ];

        // Increment comment count
        $post->increment('comment_count');

        return $this->success($response, 'Comment added successfully.', 201);
    }

    // Get all comments
    public function index($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $comments = PostComment::with('user')
            ->where('post_id', $postId)
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->f_name . ' ' . $comment->user->l_name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'created_at' => $comment->created_at->diffForHumans(),
                ];
            });

        return $this->success($comments, 'Post comments retrieved successfully.', 200);
    }

    // Update comment
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error(['Validation failed'], $validator->errors()->first(), 422);
        }

        $comment = PostComment::with('user')->find($id); // eager load user

        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth('api')->id()) {
            return $this->error([], 'Unauthorized to update this comment.', 403);
        }

        $comment->update(['comment' => $request->comment]);

        // Return consistent response structure with user
        $response = [
            'id'      => $comment->id,
            'user_id' => $comment->user->id,
            'comment' => $comment->comment,
            'user'    => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                'avatar' => $comment->user->avatar,
            ],
        ];

        return $this->success($response, 'Comment updated successfully.', 200);
    }

    // Delete comment
    public function destroy($id)
    {
        $comment = PostComment::find($id);

        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth('api')->id()) {
            return $this->error([], 'Unauthorized to delete this comment.', 403);
        }

        $post = $comment->post;

        $comment->delete();

        if ($post) {
            $post->decrement('comment_count');
        }

        return $this->success([], 'Comment deleted successfully.', 200);
    }
}
