<?php

namespace App\Http\Controllers\Api\React\Post;

use App\Models\Post;
use App\Models\Comment;
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
            'comment'    => 'required|string|max:1000',
            'parent_id'  => 'nullable|exists:comments,id' // For replies
        ]);

        if ($validator->fails()) {
            return $this->error(['Validation failed'], $validator->errors()->first(), 422);
        }

        $post = Post::find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $user = auth('api')->user();

        // Create comment with morphable relation
        $comment = new Comment([
            'comment'   => $request->comment,
            'user_id'   => $user->id,
            'parent_id' => $request->parent_id,
        ]);

        $post->comments()->save($comment); // attaches commentable_type & commentable_id

        // increment comment count only for root comments
        if (!$request->parent_id) {
            $post->increment('comment_count');
        }

        // Load user for response
        $comment->load('user');

        $response = [
            'id'            => $comment->id,
            'user_id'       => $comment->user->id,
            'comment'       => $comment->comment,
            'parent_id'     => $comment->parent_id,
            'comment_count' => $post->comment_count,
            'user'          => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                'avatar' => $comment->user->avatar,
            ],
        ];

        return $this->success($response, 'Comment added successfully.', 201);
    }

    // Get all comments
    public function index($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return $this->error([], 'Post not found.', 404);
        }

        $comments = Comment::with(['user', 'replies'])
            ->where('commentable_type', Post::class)
            ->where('commentable_id', $postId)
            ->whereNull('parent_id') // Only top-level comments
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id'         => $comment->id,
                    'comment'    => $comment->comment,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user'       => [
                        'id'     => $comment->user->id,
                        'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id'         => $reply->id,
                            'comment'    => $reply->comment,
                            'created_at' => $reply->created_at->diffForHumans(),
                            'user'       => [
                                'id'     => $reply->user->id,
                                'name'   => $reply->user->f_name . ' ' . $reply->user->l_name,
                                'avatar' => $reply->user->avatar,
                            ],
                        ];
                    }),
                ];
            });

        return $this->success($comments, 'Post comments with replies retrieved successfully.', 200);
    }

    // Update comment
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $comment = Comment::with('user')->find($id);

        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth('api')->id()) {
            return $this->error([], 'Unauthorized to update this comment.', 403);
        }

        $comment->comment = $request->comment;
        $comment->save();

        $response = [
            'id'        => $comment->id,
            'user_id'   => $comment->user->id,
            'comment'   => $comment->comment,
            'parent_id' => $comment->parent_id,
            'user'      => [
                'id'     => $comment->user->id,
                'name'   => trim($comment->user->f_name . ' ' . $comment->user->l_name),
                'avatar' => $comment->user->avatar,
            ],
        ];

        return $this->success($response, 'Comment updated successfully.', 200);
    }

    // Delete comment
    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        if ($comment->user_id !== auth('api')->id()) {
            return $this->error([], 'Unauthorized to delete this comment.', 403);
        }

        $post = null;
        if ($comment->commentable_type === Post::class) {
            $post = Post::find($comment->commentable_id);
        }

        $isRootComment = is_null($comment->parent_id);

        $comment->delete();

        // Only decrement comment_count if root comment deleted
        if ($post && $isRootComment && $post->comment_count > 0) {
            $post->decrement('comment_count');
        }

        return $this->success([], 'Comment deleted successfully.', 200);
    }
}
