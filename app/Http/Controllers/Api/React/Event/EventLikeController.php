<?php

namespace App\Http\Controllers\Api\React\Event;

use App\Models\Event;
use App\Models\eventLike;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PostLike;

class EventLikeController extends Controller
{
    use ApiResponse;

    //like and unlike event
    public function toggleLike($eventId)
    {
        $user = auth('api')->user();

        // Check if the event exists
        $event = Event::find($eventId);
        if (!$event) {
            return $this->error([], 'event not found.', 404);
        }

        // Check if the user already liked the event
        $alreadyLiked = PostLike::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->first();

        if ($alreadyLiked) {
            // Unlike the event
            $alreadyLiked->delete();
            $event->decrement('like_count');

            return $this->success([
                'event_id'    => $event->id,
                'user_id'    => $user->id,
                'status'     => 'unliked',
                'like_count' => $event->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Event unliked successfully.', 200);
        } else {
            // Like the event
            PostLike::create([
                'event_id' => $eventId,
                'user_id' => $user->id,
            ]);
            $event->increment('like_count');

            return $this->success([
                'event_id'    => $event->id,
                'user_id'    => $user->id,
                'status'     => 'liked',
                'like_count' => $event->like_count,
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->f_name . ' ' . $user->l_name,
                    'avatar' => $user->avatar,
                ],
            ], 'Event liked successfully.', 200);
        }
    }


    //get all likes
    public function index($eventId)
    {
        $event = Event::with('likes.user')->find($eventId);

        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $likeUsers = $event->likes->map(function ($like) {
            return [
                'id' => $like->user->id,
                'name' => $like->user->f_name . ' ' . $like->user->l_name,
                'avatar' => $like->user->avatar
            ];
        });

        return $this->success($likeUsers, 'Event like list retrieved successfully.', 200);
    }
}
