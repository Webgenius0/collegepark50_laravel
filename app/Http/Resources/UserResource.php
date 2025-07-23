<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'f_name' => $this->f_name,
            'l_name' => $this->l_name,
            'email' => $this->email,

            'role' => $this->role,

            'profession' => $this->profession,
            'gender' => $this->gender,
            'age' => $this->age,
            'avatar' => $this->avatar ? asset($this->avatar) : null,


            'address' => $this->address,
            'country' => $this->country,
            'city' => $this->city,
            'state' => $this->state,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],


            'get_notification' => $this->get_notification,

            'created_at' => $this->created_at ? $this->created_at->diffForHumans() : null
        ];
    }
}
