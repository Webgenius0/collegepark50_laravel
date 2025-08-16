<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Event\EventResource;
use App\Http\Resources\Event\EventCollection;

class EventPageController extends Controller
{
    use ApiResponse;

    //get all events
    public function allEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Per page value (default 10)
            $perPage = $request->input('per_page', 10);

            $events = Event::with(['venue', 'user'])
                ->latest()
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Events fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //upcoming events
    public function upcomingEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $now = Carbon::now();
            $perPage = $request->input('per_page', 10);

            $events = Event::where('user_id', $user->id)
                ->where(function ($query) use ($now) {
                    $query->where('start_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('start_date', '=', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                })
                ->orderBy('start_date')
                ->orderBy('start_time')
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Upcoming events fetched successfully'
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch upcoming events. ' . $e->getMessage(), 500);
        }
    }

    //get past events
    public function pastEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Number of events per page (default 10)
            $perPage = $request->input('per_page', 10);

            $events = Event::with(['venue', 'user'])
                ->where('status', 'completed')
                ->latest()
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'Past events fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch past events. ' . $e->getMessage(), 500);
        }
    }

    //get auth user all events
    public function myEvents(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Number of events per page (default 10)
            $perPage = $request->input('per_page', 10);

            $events = Event::with(['venue', 'user'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate($perPage);

            return $this->success(
                new EventCollection($events),
                'User events fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //get events images
    public function eventGallery()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::select('id', 'banner')->inRandomOrder()->limit(12)->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'banner_url' => $event->banner ? asset('/' . $event->banner) : null,
                ];
            });

            return $this->success([
                'gallery' => $events
            ], "Event's images fetched successfully.");
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch images. ' . $e->getMessage(), 500);
        }
    }
}
