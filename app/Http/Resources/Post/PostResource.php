<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            // Post author info
            'user' => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => asset($this->user->avatar),
            ],

            // Core content
            'content' => $this->content,

            // Media
            'images' => $this->images->map(fn($img) => asset($img->image_path)),
            'videos' => $this->videos->map(fn($vid) => asset($vid->video_path)),

            // Hashtags
            'hashtags' => $this->hashtags->pluck('tag'),

            // Counts
            'like_count'    => $this->like_count,
            'comment_count' => $this->comment_count,
            'share_count'   => $this->share_count,

            // Likes with user info
            'likes' => $this->likes->map(function ($like) {
                return [
                    'id'     => $like->user->id,
                    'name'   => $like->user->f_name . ' ' . $like->user->l_name,
                    'avatar' => asset($like->user->avatar),
                ];
            }),

            // Comments with user info + replies
            'comments' => $this->comments->map(function ($comment) {
                return [
                    'id'      => $comment->id,
                    'comment' => $comment->comment,
                    'user'    => [
                        'id'     => $comment->user->id,
                        'name'   => $comment->user->f_name . ' ' . $comment->user->l_name,
                        'avatar' => asset($comment->user->avatar),
                    ],
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id'    => $reply->id,
                            'reply' => $reply->reply,
                            'user'  => [
                                'id'     => $reply->user->id,
                                'name'   => $reply->user->f_name . ' ' . $reply->user->l_name,
                                'avatar' => asset($reply->user->avatar),
                            ],
                            'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
