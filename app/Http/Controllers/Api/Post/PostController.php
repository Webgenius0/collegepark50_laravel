<?php

namespace App\Http\Controllers\Api\Post;

use Exception;
use App\Models\Post;
use App\Helper\Helper;
use App\Models\Hashtag;
use App\Models\PostImage;
use App\Models\PostVideo;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Post\PostStoreRequest;

class PostController extends Controller
{
    use ApiResponse;

    //store post
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'content'     => ['nullable', 'string'],
                'images.*'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],    // 5MB max per image
                'videos.*'    => ['nullable', 'mimes:mp4,mov,avi', 'max:51200'],                // 50MB max per video
            ]);

            if ($validator->fails()) {
                return $this->error(['Validation failed'], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            // Create post
            $post = Post::create([
                'user_id' => $user->id,
                'content' => $request->input('content'),
            ]);

            // Upload Images (if any)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'posts/images');
                    PostImage::create([
                        'post_id'    => $post->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }

            // Upload Videos (if any)
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'posts/videos', 'post-video-' . Str::random(8));
                    PostVideo::create([
                        'post_id'    => $post->id,
                        'video_path' => $videoPath,
                    ]);
                }
            }

            // Hashtag Extraction & Sync
            if ($request->filled('content')) {
                preg_match_all('/#(\w+)/', $request->input('content'), $matches);

                if (!empty($matches[1])) {
                    $tagIds = [];

                    foreach ($matches[1] as $tag) {
                        $hashtag = Hashtag::firstOrCreate(['tag' => '#' . $tag]);
                        $tagIds[] = $hashtag->id;
                    }

                    $post->hashtags()->sync($tagIds);
                }
            }

            DB::commit();

            return $this->success(
                new PostResource($post->load(['images', 'videos', 'hashtags'])),
                'Post created successfully.',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create post. ' . $e->getMessage(), 500);
        }
    }

    //get all posts
    public function index()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $posts = $user->posts()
                ->with(['images', 'videos', 'hashtags'])
                ->latest()
                ->get();

            return $this->success(
                PostResource::collection($posts),
                'User posts retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch posts. ' . $e->getMessage(), 500);
        }
    }

    //get single post
    public function show($id)
    {
        try {
            $post = Post::with(['images', 'videos', 'hashtags'])
                ->find($id);

            if (!$post) {
                return $this->error([], 'Post not found.', 404);
            }

            return $this->success(
                new PostResource($post),
                'Post retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to retrieve post. ' . $e->getMessage(), 500);
        }
    }

    //delete post
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $post = Post::with(['images', 'videos', 'hashtags'])->find($id);

            if (!$post) {
                return $this->error([], 'Post not found.', 404);
            }

            // Check if post belongs to current user
            if ($post->user_id !== auth('api')->id()) {
                return $this->error([], 'Unauthorized to delete this post.', 403);
            }

            // Collect image paths and delete files
            $imagePaths = $post->images->pluck('image_path')->map(function ($path) {
                return asset($path);
            })->toArray();

            if (!empty($imagePaths)) {
                Helper::deleteImages($imagePaths);
            }

            // Delete video files manually (no helper yet)
            foreach ($post->videos as $video) {
                $fullPath = public_path($video->video_path);
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            // Delete related records from DB
            $post->images()->delete();
            $post->videos()->delete();
            $post->hashtags()->detach();

            // Finally delete the post
            $post->delete();

            DB::commit();

            return $this->success([], 'Post deleted successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to delete post. ' . $e->getMessage(), 500);
        }
    }
}
