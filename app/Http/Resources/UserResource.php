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
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),


            'role' => $this->role,
            'is_otp_verified' => $this->is_otp_verified,


            'profession' => $this->profession,
            'gender' => $this->gender,
            'age' => $this->age,
            'avater' => $this->avater ? asset($this->avater) : null,


            'address' => $this->address,
            'country' => $this->country,
            'city' => $this->city,
            'state' => $this->state,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],


            'get_notification' => $this->get_notification,

           
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
