<?php

namespace App\Http\Controllers\Api\React\Event;

use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;

class EventCommentController extends Controller
{
    use ApiResponse;
    // Add comment
    public function store(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'comment'    => 'required|string|max:1000',
            'parent_id'  => 'nullable|exists:comments,id' // For replies
        ]);

        if ($validator->fails()) {
            return $this->error(['Validation failed'], $validator->errors()->first(), 422);
        }

        $event = Event::find($eventId);

        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $user = auth('api')->user();

        // Create comment with morphable relation
        $comment = new Comment([
            'comment'   => $request->comment,
            'user_id'   => $user->id,
            'parent_id' => $request->parent_id,
        ]);

        $event->comments()->save($comment); // attaches commentable_type & commentable_id

        // increment comment count only for root comments
        if (!$request->parent_id) {
            $event->increment('comment_count');
        }

        // Load user for response
        $comment->load('user');

        $response = [
            'id'            => $comment->id,
            'user_id'       => $comment->user->id,
            'comment'       => $comment->comment,
            'parent_id'     => $comment->parent_id,
            'comment_count' => $event->comment_count,
            'user'          => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                'avatar' => $comment->user->avatar,
            ],
        ];

        return $this->success($response, 'Comment added successfully.', 201);
    }

    // Get all comments
    public function index($eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $comments = Comment::with(['user', 'replies'])
            ->where('commentable_type', Event::class)
            ->where('commentable_id', $eventId)
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

        return $this->success($comments, 'Event comments with replies retrieved successfully.', 200);
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

        $event = null;
        if ($comment->commentable_type === Event::class) {
            $event = Event::find($comment->commentable_id);
        }

        $isRootComment = is_null($comment->parent_id);

        $comment->delete();

        // Only decrement comment_count if root comment deleted
        if ($event && $isRootComment && $event->comment_count > 0) {
            $event->decrement('comment_count');
        }

        return $this->success([], 'Comment deleted successfully.', 200);
    }
}
