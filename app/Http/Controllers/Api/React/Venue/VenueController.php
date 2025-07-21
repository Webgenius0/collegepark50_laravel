<?php

namespace App\Http\Controllers\Api\React\Venue;

use Exception;
use App\Models\Venue;
use App\Helper\Helper;
use App\Models\VenueMedia;
use App\Models\VenueDetail;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Venue\VenueResource;
use App\Http\Requests\Venue\VenueRequest;

class VenueController extends Controller
{
    use ApiResponse;

    //get all venues of auth user
    public function index()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venues = $user->venues()
                ->with(['detail', 'media'])
                ->latest()
                ->get();

            return $this->success(
                VenueResource::collection($venues),
                'User venues retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch venues. ' . $e->getMessage(), 500);
        }
    }

    //store venue
    public function store(VenueRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            // 1. Create Venue (main table)
            $venue = Venue::create([
                'user_id'            => $user->id,
                'title'              => $request->title,
                'capacity'           => $request->capacity,
                'location'           => $request->location,
                'latitude'           => $request->latitude,
                'longitude'          => $request->longitude,
                'service_start_time' => $request->service_start_time,
                'service_end_time'   => $request->service_end_time,
                'ticket_price'       => $request->ticket_price,
                'phone'              => $request->phone,
                'email'              => $request->email,
                'status'             => 0,
            ]);

            // 2. Add Venue Details (description & features)
            VenueDetail::create([
                'venue_id'    => $venue->id,
                'description' => $request->description,
                'features'    => $request->features,
            ]);

            // 3. Add Venue Media (optional)
            // Upload Images (if any)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'venue/images');
                    VenueMedia::create([
                        'venue_id'  => $venue->id,
                        'image_url' => $imagePath
                    ]);
                }
            }

            // Upload Videos (if any)
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'venue/videos', 'venue-video-' . Str::random(8));
                    VenueMedia::create([
                        'venue_id'  => $venue->id,
                        'video_url' => $videoPath,
                    ]);
                }
            }


            DB::commit();

            return $this->success(
                new VenueResource(
                    $venue->load(['detail', 'media'])
                ),
                'Venue created successfully.',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create venue. ' . $e->getMessage(), 500);
        }
    }

    //edit venue
    public function edit($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venue = Venue::with(['detail', 'media'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$venue) {
                return $this->error([], 'Venue not found or unauthorized.', 404);
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

    //update venue
    public function update(VenueRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venue = Venue::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$venue) {
                return $this->error([], 'Venue not found or unauthorized.', 404);
            }

            // Update venue
            $venue->update([
                'title'              => $request->title,
                'capacity'           => $request->capacity,
                'location'           => $request->location,
                'latitude'           => $request->latitude,
                'longitude'          => $request->longitude,
                'service_start_time' => $request->service_start_time,
                'service_end_time'   => $request->service_end_time,
                'ticket_price'       => $request->ticket_price,
                'phone'              => $request->phone,
                'email'              => $request->email,
            ]);

            // Update venue details
            $venue->detail()->updateOrCreate(
                ['venue_id' => $venue->id],
                [
                    'description' => $request->description,
                    'features'    => $request->features,
                ]
            );

            // Replace Media if New Files Uploaded
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $venue->media()->whereNotNull('image_url')->get();
                foreach ($oldImages as $img) {
                    Helper::deleteImage($img->image_url);
                    $img->delete();
                }

                // Upload new images
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'venue/images');
                    VenueMedia::create([
                        'venue_id'   => $venue->id,
                        'image_url'  => $imagePath,
                    ]);
                }
            }

            if ($request->hasFile('videos')) {
                // Delete old videos
                $oldVideos = $venue->media()->whereNotNull('video_url')->get();
                foreach ($oldVideos as $vid) {
                    Helper::deleteFile($vid->video_url);
                    $vid->delete();
                }

                // Upload new videos
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'venue/videos', 'venue-video-' . Str::random(8));
                    VenueMedia::create([
                        'venue_id'   => $venue->id,
                        'video_url'  => $videoPath,
                    ]);
                }
            }

            DB::commit();

            return $this->success(
                new VenueResource($venue->load(['detail', 'media'])),
                'Venue updated successfully.',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to update venue. ' . $e->getMessage(), 500);
        }
    }

    //delete venue
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venue = Venue::with(['detail', 'media'])->where('id', $id)->where('user_id', $user->id)->first();

            if (!$venue) {
                return $this->error([], 'Venue not found or unauthorized.', 404);
            }

            // Delete media files from storage
            foreach ($venue->media as $media) {
                if ($media->image_url) {
                    Helper::deleteFile($media->image_url);
                }
                if ($media->video_url) {
                    Helper::deleteFile($media->video_url);
                }
            }

            // Delete related records
            $venue->media()->delete();
            $venue->detail()->delete();
            $venue->delete();

            DB::commit();

            return $this->success([], 'Venue deleted successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to delete venue. ' . $e->getMessage(), 500);
        }
    }
}
