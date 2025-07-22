<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'venue_id'        => 'nullable|exists:venues,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',

            'start_date'      => 'required|date',
            'start_time'      => 'required|date_format:H:i',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'end_time'        => 'required|date_format:H:i',

            'time_zone'       => 'required|string',
            'all_day_event'   => 'boolean',

            'banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tags'            => 'nullable|array',
            'tags.*'          => 'string',

            'status'          => 'in:going_live,pending,postponed,cancelled,completed',
        ];
    }
}
