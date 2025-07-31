<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class EventFilterController extends Controller
{
    use ApiResponse;

    //serce event by title
    public function searchByTitle(Request $request)
    {
        try {
            $query = $request->query('q');

            if (!$query) {
                return $this->error([], 'Search query is required.', 400);
            }

            $events = Event::with('venue')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%') // match event title
                        ->orWhereHas('venue', function ($q2) use ($query) {
                            $q2->where('title', 'like', '%' . $query . '%'); // match venue title
                        });
                })
                ->orderBy('start_date', 'asc')
                ->get();

            if ($events->isEmpty()) {
                return $this->success([], 'No events matched your search.', 200);
            }

            $events = $events->map(function ($event) {
                return [
                    'id'         => $event->id,
                    'title'      => $event->title,
                    'start_date' => $event->start_date,
                    'start_time' => $event->start_time,
                    'banner'     => $event->banner,
                    'venue_id'   => $event->venue_id ?? '',
                    'venue' => [
                        'id'       => $event->venue->id ?? '',
                        'title'    => $event->venue->title ?? '',
                        'location' => $event->venue->location ?? '',
                    ]
                ];
            });

            return $this->success($events, 'Events retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong.', 500);
        }
    }

    //filter event by date
    public function filterByNearbyDates(Request $request)
    {
        try {
            $date = $request->query('date');

            if (!$date) {
                return $this->error([], 'Date is required.', 400);
            }

            $baseDate = Carbon::parse($date);
            $start = $baseDate->copy()->subDays(2);
            $end = $baseDate->copy()->addDays(5);

            $events = Event::with('venue')
                ->whereDate('start_date', '<=', $end)
                ->whereDate('end_date', '>=', $start)
                ->get();

            if ($events->isEmpty()) {
                return $this->success([], 'No events found in the date range.', 200);
            }

            // Sort: exact match first, then by date closeness
            $events = $events->sortBy(function ($event) use ($baseDate) {
                $eventStart = Carbon::parse($event->start_date);
                if ($eventStart->isSameDay($baseDate)) {
                    return -1; // push exact match to top
                }
                return abs($eventStart->diffInDays($baseDate));
            })->values(); // reset keys

            // Format response
            $formatted = $events->map(function ($event) {
                return [
                    'id'         => $event->id,
                    'title'      => $event->title,
                    'start_date' => $event->start_date->format('F d, Y'),
                    'start_time' => $event->start_time->format('h:i A'),
                    'end_date'   => $event->end_date->format('F d, Y '),
                    'end_time' => $event->end_time->format('h:i A'),
                    'banner'     => $event->banner,
                    'venue'      => [
                        'id'       => $event->venue->id ?? '',
                        'title'    => $event->venue->title ?? '',
                        'location' => $event->venue->location ?? '',
                    ],
                ];
            });

            return $this->success($formatted, 'Nearby events retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::error('Date window event search error: ' . $e->getMessage());
            return $this->error([], 'Something went wrong.' . $e->getMessage(), 500);
        }
    }
}
