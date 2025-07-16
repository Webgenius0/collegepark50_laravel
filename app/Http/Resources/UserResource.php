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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),

            // Authentication related fields
            'role' => $this->role,
            'is_otp_verified' => $this->is_otp_verified,

            // Personal information
            'profession' => $this->profession,
            'gender' => $this->gender,
            'age' => $this->age,
            'avater' => $this->avater ? asset($this->avater) : null,


            // Location information
            'address' => $this->address,
            'country' => $this->country,
            'city' => $this->city,
            'state' => $this->state,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],

            // Preferences
            'get_notification' => $this->get_notification,

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
