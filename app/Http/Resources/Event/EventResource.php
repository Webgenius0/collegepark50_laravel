<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,

            // venue info
            'venue' => [
                'venue_id'       => $this->venue_id,
                'venue_name'     => $this->venue->title ?? null,
            ],

            // author info
            'user' => [
                'id'     => $this->user->id,
                'name'   => $this->user->f_name . ' ' . $this->user->l_name,
                'avatar' => $this->user->avatar,
            ],

            'title'          => $this->title,
            'description'    => $this->description,
            'start_date'     => $this->start_date,
            'start_time'     => $this->start_time,
            'end_date'       => $this->end_date,
            'end_time'       => $this->end_time,

            'time_zone'      => $this->time_zone,
            'all_day_event'  => $this->all_day_event,

            'banner_url'     => $this->banner,
            'tags'           => $this->tags,

            'status'         => $this->status,
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
