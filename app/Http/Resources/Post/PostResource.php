<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            // Core content with hashtags removed
            'content' => trim(preg_replace('/#\w+/', '', $this->content)),

            // Counts
            'like_count' => $this->like_count,
            'comment_count' => $this->comment_count,
            'share_count' => $this->share_count,

            // Merged media list (images + videos)
            'media' => collect()
                ->merge($this->images->map(fn($img) => asset($img->image_path)))
                ->merge($this->videos->map(fn($vid) => asset($vid->video_path)))
                ->values(),

            // Hashtags
            'hashtags' => $this->hashtags->pluck('tag'),

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // user info
            'user' => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
            ],

        ];
    }
}
