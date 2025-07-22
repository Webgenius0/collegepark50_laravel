<?php

namespace App\Http\Controllers\Api\React\CMS;

use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    use ApiResponse;
    //get home-page data
    public function index()
    {
        $event_hero = CMS::where('page', 'event-page')->where('section', 'hero')->first();
        $upcoming_event = CMS::where('page', 'event-page')->where('section', 'upcoming-event')->first();
        $event_details_hero = CMS::where('page', 'event-details-page')->where('section', 'hero')->first();

        $data = [
            'event_hero' => $event_hero,
            'upcoming_event' => $upcoming_event,
            'event_details_hero' => $event_details_hero,
        ];

        return $this->success($data, 'Event page data retrieved successfully.', 200);
    }
}
