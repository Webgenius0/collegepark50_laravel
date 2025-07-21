<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;

class VenueRequest extends FormRequest
{
    public function authorize()
    {
        return auth('api')->check();
    }

    public function rules()
    {
        return [
            'title'               => 'required|string|max:200',
            'capacity'            => 'required|integer|min:1',
            'location'            => 'required|string|max:255',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'service_start_time'  => 'nullable|date_format:H:i',
            'service_end_time'    => 'nullable|date_format:H:i|after_or_equal:service_start_time',
            'ticket_price'        => 'nullable|numeric|min:0',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:50',

            // Nested detail
            'description'         => 'nullable|string',
            'features'            => 'nullable|array',
            'features.*'          => 'string|max:100',

            // Media—uploading URLs for simplicity
            'images.*'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
            'videos.*'    => 'nullable|mimes:mp4,mov,avi|max:51200',
        ];
    }

    public function messages()
    {
        return [
            'title.required'  => 'Venue title is required.',
            'capacity.required' => 'Capacity is required and must be a positive number.',
            'service_end_time.after_or_equal' => 'End time must be after or equal to the start time.',
            // 'images.*' => 'Image can not ',
            // 'media.*.video_url.url' => 'Each video URL must be a valid URL.',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure empty strings become null
        $this->merge([
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'ticket_price' => $this->ticket_price ?: null,
        ]);
    }
}
