<?php

namespace App\Http\Controllers\Api\React\Venue;

use Exception;
use App\Models\Venue;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Venue\VenueResource;
use App\Http\Resources\Venue\VenueCollection;

class VenuePageController extends Controller
{
    use ApiResponse;

    //get all venues of auth user
    // public function allVenue(Request $request)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user) {
    //             return $this->error([], 'Unauthorized user.', 401);
    //         }

    //         $perPage = $request->input('per_page', 10);

    //         $venues = Venue::with(['detail', 'media', 'reviews'])
    //             ->latest()
    //             ->paginate($perPage);

    //         return $this->success(
    //             new VenueCollection($venues),
    //             'Venues retrieved successfully.',
    //             200
    //         );
    //     } catch (Exception $e) {
    //         return $this->error([], 'Failed to fetch venues. ' . $e->getMessage(), 500);
    //     }
    // }

    public function allVenue(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $perPage = $request->input('per_page', 10);

            // Fetch venues with relationships and average rating
            $venues = Venue::with(['detail', 'media', 'reviews'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->latest()
                ->paginate($perPage);

                // return $venues;exit();

            return $this->success(
                new VenueCollection($venues),
                'Venues retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch venues. ' . $e->getMessage(), 500);
        }
    }


    //venue list for flutter
    public function list()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venues = Venue::select('id', 'title', 'location', 'latitude', 'longitude')->get();

            return $this->success(
                $venues,
                'Venues retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch venues. ' . $e->getMessage(), 500);
        }
    }

    //venue details
    public function venueDetails($id)
    {
        try {
            $venue = Venue::with(['detail', 'media', 'reviews'])
                ->find($id);

            if (!$venue) {
                return $this->error([], 'Venue not found.', 404);
            }

            return $this->success(
                new VenueResource($venue),
                'Venue fetched successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch venue. ' . $e->getMessage(), 500);
        }
    }
}
