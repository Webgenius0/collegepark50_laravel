<?php

namespace App\Http\Controllers\Api\React\Event;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\EventRequest;
use App\Http\Resources\Event\EventResource;

class EventManageController extends Controller
{
    use ApiResponse;

    //get all events
    public function index()
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

    //store event
    public function store(EventRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth('api')->user();

            // Attach the authenticated user's ID
            $validated['user_id'] = $user->id;

            // Handle image upload
            if ($request->hasFile('banner')) {
                $validated['banner'] = Helper::uploadImage($request->file('banner'), 'event/banners');
            }

            $event = Event::create($validated);

            return $this->success([
                'event' => new EventResource($event)
            ], 'Event created successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to create event.', 500, $e);
        }
    }


    //get single event by id
    public function show($id)
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $event = Event::with('venue')->find($id);

            if (!$event) {
                return $this->error([], 'Event not found', 404);
            }

            return $this->success([
                'event' => new EventResource($event)
            ], 'Event fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch events. ' . $e->getMessage(), 500);
        }
    }

    //update event
    public function update(EventRequest $request, $id)
    {
        $user = auth('api')->user();
        $event = Event::where('id', $id)->where('user_id', $user->id)->first();

        if (!$event) {
            return $this->error([], 'Event not found or unauthorized', 404);
        }

        $validated = $request->validated();

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($event->banner) {
                Helper::deleteImage($event->banner);
            }
            $validated['banner'] = Helper::uploadImage($request->file('banner'), 'event/banners');
        }

        $event->update($validated);

        return $this->success(new EventResource($event->fresh()), 'Event updated successfully');
    }

    //delete event
    public function destroy($id)
    {
        $user = auth('api')->user();
        $event = Event::where('id', $id)->where('user_id', $user->id)->first();

        if (!$event) {
            return $this->error([], 'Event not found or unauthorized', 404);
        }

        if ($event->banner) {
            Helper::deleteImage($event->banner);
        }

        $event->delete();

        return $this->success([], 'Event deleted successfully');
    }

    //change event status
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:going_live,pending,postponed,cancelled,completed'
        ]);

        $user = auth('api')->user();
        $event = Event::where('id', $id)->where('user_id', $user->id)->first();

        if (!$event) {
            return $this->error([], 'Event not found or unauthorized', 404);
        }

        $event->status = $request->status;
        $event->save();

        return $this->success(new EventResource($event), 'Event status updated successfully');
    }

    //upcoming events
    public function upcoming()
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
}
