<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Event\EventResource;

class EventPageController extends Controller
{
    use ApiResponse;

    //get all events
    public function allEvents()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::with(['venue', 'user'])
                ->latest()
                ->get();

            return $this->success([
                'events' => EventResource::collection($events)
            ], 'Events fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //upcoming events
    public function upcomingEvents()
    {
        $user = auth('api')->user();
        $now = Carbon::now();

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
            ->get();

        return $this->success(EventResource::collection($events), 'Upcoming events fetched successfully');
    }

    //get past events
    public function pastEvents()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::with(['venue', 'user'])
                ->where('status', 'completed')
                ->latest()
                ->get();

            return $this->success([
                'events' => EventResource::collection($events)
            ], 'Past events fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //get auth user all events
    public function myEvents()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $events = Event::with(['venue', 'user'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return $this->success([
                'events' => EventResource::collection($events)
            ], 'Events fetched successfully.');
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
