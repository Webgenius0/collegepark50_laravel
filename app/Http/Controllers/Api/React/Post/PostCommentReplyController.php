<?php

namespace App\Http\Controllers\Api\React\Post;

use App\Models\PostComment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CommentReplay;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PostCommentReplyController extends Controller
{
    use ApiResponse;

    // Create Reply
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_comment_id' => 'required|exists:post_comments,id',
            'reply'           => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        $reply = CommentReplay::create([
            'post_comment_id' => $request->post_comment_id,
            'user_id'         => $user->id,
            'reply'           => $request->reply,
        ]);

        $data = [
            'id'               => $reply->id,
            'post_comment_id' => $reply->post_comment_id,
            'user_id'          => $user->id,
            'reply'            => $reply->reply,
            'created_at'       => $reply->created_at->toDateTimeString(),
            'user' => [
                'id'     => $user->id,
                'name'   => $user->f_name . ' ' . $user->l_name,
                'avatar' => $user->avatar,
            ],
        ];

        return $this->success($data, 'Reply added successfully.', 201);
    }

    // Update Reply
    public function update(Request $request, $id)
    {
        $reply = CommentReplay::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$reply) {
            return $this->error([], 'Reply not found or unauthorized.', 404);
        }

        $request->validate([
            'reply' => 'required|string',
        ]);

        $reply->update(['reply' => $request->reply]);

        $user = $reply->user; // load user relation

        $data = [
            'id'               => $reply->id,
            'post_comment_id' => $reply->post_comment_id,
            'user_id'          => $user->id,
            'reply'            => $reply->reply,
            'updated_at'       => $reply->updated_at->toDateTimeString(),
            'user' => [
                'id'     => $user->id,
                'name'   => $user->f_name . ' ' . $user->l_name,
                'avatar' => $user->avatar,
            ],
        ];

        return $this->success($data, 'Reply updated successfully.', 200);
    }

    // 3. Delete Reply
    public function destroy($id)
    {
        $reply = CommentReplay::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$reply) {
            return $this->error([], 'Reply not found or unauthorized.', 404);
        }

        $reply->delete();

        return $this->success([], 'Reply deleted successfully.');
    }

    // 4. List Replies of a Comment
    public function index($commentId)
    {
        $comment = PostComment::find($commentId);

        if (!$comment) {
            return $this->error([], 'Comment not found.', 404);
        }

        $replies = CommentReplay::with('user')
            ->where('post_comment_id', $commentId)
            ->latest()
            ->get()
            ->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'reply' => $reply->reply,
                    'user' => [
                        'id' => $reply->user->id,
                        'name' => $reply->user->f_name . ' ' . $reply->user->l_name,
                        'avatar' => $reply->user->avatar,
                    ],
                    'created_at' => $reply->created_at->diffForHumans(),
                ];
            });

        return $this->success($replies, 'Replies fetched successfully.');
    }
}
