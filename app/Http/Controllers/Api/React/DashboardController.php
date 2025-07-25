<?php

namespace App\Http\Controllers\Api\React;

use Exception;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Venue;
use App\Models\VenueReview;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    use ApiResponse;

    //user events stats
    public function userEventStats()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Total events created by the user
            $totalEvents = Event::where('user_id', $user->id)->count();

            // Last month date range
            $now = Carbon::now();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            $lastMonthCount = Event::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            // Upcoming events (start_date in future)
            $upcomingEvents = Event::where('user_id', $user->id)
                ->whereDate('start_date', '>', $now->toDateString())
                ->orderBy('start_date')
                ->get();

            $upcomingEventsCount = $upcomingEvents->count();

            // Next upcoming event
            $nextEvent = $upcomingEvents->first();

            $nextEventInDays = null;
            if ($nextEvent) {
                $nextEventInDays = (int) $now->diffInDays(Carbon::parse($nextEvent->start_date));
            }

            return $this->success([
                'total_events' => $totalEvents,
                'last_month_events' => $lastMonthCount,
                'upcoming_events_count' => $upcomingEventsCount,
                'next_event_in_days' => $nextEventInDays,
            ], 'User event stats fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch stats. ' . $e->getMessage(), 500);
        }
    }

    //venue rating stats
    public function venueReviewStats()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // Get all venue IDs owned by this user
            $venueIds = Venue::where('user_id', $user->id)->pluck('id');

            if ($venueIds->isEmpty()) {
                return $this->success([
                    'average_rating' => null,
                    'last_month_review_count' => 0,
                ], 'No venues found.');
            }

            $now = Carbon::now();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            // Calculate combined average rating
            $averageRating = VenueReview::whereIn('venue_id', $venueIds)->avg('rating');

            // Count last month reviews
            $lastMonthCount = VenueReview::whereIn('venue_id', $venueIds)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            return $this->success([
                'average_rating' => round($averageRating, 1),
                'last_month_review_count' => $lastMonthCount,
            ], 'Overall rating stats fetched successfully.');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch overall review stats. ' . $e->getMessage(), 500);
        }
    }

    // public function eventDurationStats()
    // {
    //     try {
    //         $user = auth('api')->user();
    //         if (!$user) {
    //             return $this->error([], 'Unauthorized user.', 401);
    //         }

    //         $now = Carbon::now();

    //         // This month range
    //         $startOfThisMonth = $now->copy()->startOfMonth();
    //         $endOfThisMonth = $now->copy()->endOfMonth();

    //         // Last month range
    //         $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
    //         $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

    //         // Helper function to get duration in hours
    //         $getDurations = function ($startDate, $endDate) use ($user) {
    //             return Event::where('user_id', $user->id)
    //                 ->whereNotNull(['start_date', 'start_time', 'end_date', 'end_time'])
    //                 ->whereBetween('start_date', [$startDate, $endDate])
    //                 ->get()
    //                 ->map(function ($event) {
    //                     $start = Carbon::parse($event->start_date . ' ' . $event->start_time);
    //                     $end = Carbon::parse($event->end_date . ' ' . $event->end_time);
    //                     return $start->diffInMinutes($end) / 60; // duration in hours
    //                 });
    //         };

    //         // return $getDurations;exit();

    //         $currentDurations = $getDurations($startOfThisMonth, $endOfThisMonth);
    //         $lastMonthDurations = $getDurations($startOfLastMonth, $endOfLastMonth);

    //         $currentAverage = $currentDurations->avg();
    //         $lastMonthAverage = $lastMonthDurations->avg();

    //         // Calculate % change
    //         $percentChange = null;
    //         if ($lastMonthAverage && $lastMonthAverage > 0) {
    //             $percentChange = (($currentAverage - $lastMonthAverage) / $lastMonthAverage) * 100;
    //             $percentChange = round($percentChange, 1);
    //         }

    //         return $this->success([
    //             'average_event_duration' => round($currentAverage, 1), // e.g. 3.2
    //             'duration_percent_change' => $percentChange // e.g. +8
    //         ], 'Average event duration stats fetched successfully.');
    //     } catch (Exception $e) {
    //         return $this->error([], 'Failed to fetch duration stats. ' . $e->getMessage(), 500);
    //     }
    // }


    
    // public function eventDurationStats()
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user) {
    //             return $this->error([], 'Unauthorized user.', 401);
    //         }

    //         // Get current date and setup month boundaries
    //         $now = Carbon::now();
    //         $currentMonthStart = $now->copy()->startOfMonth();
    //         $currentMonthEnd = $now->copy()->endOfMonth();
    //         $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
    //         $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

    //         // Get all completed events
    //         $completedEvents = Event::where('user_id', $user->id)
    //             ->where('status', 'completed')
    //             ->whereNotNull('start_date')
    //             ->whereNotNull('end_date')
    //             ->get();

    //         // Initialize variables
    //         $currentMonthDurations = [];
    //         $lastMonthDurations = [];

    //         foreach ($completedEvents as $event) {
    //             try {
    //                 // Parse start and end datetimes
    //                 $start = Carbon::parse($event->start_date . ' ' . ($event->all_day_event ? '00:00:00' : $event->start_time), $event->time_zone);
    //                 $end = Carbon::parse($event->end_date . ' ' . ($event->all_day_event ? '23:59:59' : $event->end_time), $event->time_zone);

    //                 // Calculate duration in hours
    //                 $durationHours = $start->diffInSeconds($end) / 3600;

    //                 // Categorize by month
    //                 if ($end->between($currentMonthStart, $currentMonthEnd)) {
    //                     $currentMonthDurations[] = $durationHours;
    //                 } elseif ($end->between($lastMonthStart, $lastMonthEnd)) {
    //                     $lastMonthDurations[] = $durationHours;
    //                 }
    //             } catch (Exception $e) {
    //                 // Skip events with invalid date/time data
    //                 continue;
    //             }
    //         }

    //         // Calculate averages
    //         $currentMonthAvg = count($currentMonthDurations) > 0
    //             ? round(array_sum($currentMonthDurations) / count($currentMonthDurations), 1)
    //             : 0;

    //         $lastMonthAvg = count($lastMonthDurations) > 0
    //             ? round(array_sum($lastMonthDurations) / count($lastMonthDurations), 1)
    //             : 0;

    //         // Calculate percentage change
    //         $percentChange = 0;
    //         if ($lastMonthAvg > 0) {
    //             $percentChange = round((($currentMonthAvg - $lastMonthAvg) / $lastMonthAvg) * 100, 1);
    //         }

    //         return $this->success([
    //             'average_event_duration' => $currentMonthAvg,
    //             'duration_percent_change' => $percentChange
    //         ], 'Average event duration stats fetched successfully.');
    //     } catch (Exception $e) {
    //         return $this->error([], 'Failed to fetch duration stats. ' . $e->getMessage(), 500);
    //     }
    // }
}
