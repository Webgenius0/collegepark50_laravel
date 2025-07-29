<?php

namespace App\Http\Controllers\Api\React\User;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\FollowNotification;

class FollowerController extends Controller
{
    use ApiResponse;

    // Follow or Unfollow a user
    public function toggleFollow($id)
    {
        $authUser = auth('api')->user();

        if ($authUser->id == $id) {
            return $this->error([], "You can't follow yourself.", 403);
        }

        $userToFollow = User::find($id);
        if (!$userToFollow) {
            return $this->error([], "User not found.", 404);
        }

        $isFollowing = $authUser->followings()->where('following_id', $id)->exists();

        if ($isFollowing) {
            // Unfollow
            $authUser->followings()->detach($id);
            $message = 'Unfollowed successfully.';
        } else {
            // Follow
            $authUser->followings()->attach($id);
            $message = 'Followed successfully.';

            // Send notification to the user being followed
            try {
                if ($userToFollow->id !== $authUser->id) {
                    $userToFollow->notify(new FollowNotification($authUser));
                }
            } catch (Exception $e) {
                Log::error('Follow notification error: ' . $e->getMessage());
            }
        }

        return $this->success([], $message);
    }

    // Get followers of authenticated user
    public function getFollowers()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $followers = $user->followers()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->get()
            ->map(function ($follower) {
                return [
                    'id'     => $follower->id,
                    'name'   => $follower->f_name . ' ' . $follower->l_name,
                    'avatar' => $follower->avatar,
                ];
            });

        return $this->success([
            'count'     => $user->followers()->count(),
            'followers' => $followers,
        ], 'Followers retrieved successfully.');
    }

    // Get followers of any user by user id
    public function getUserFollowers($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $followers = $user->followers()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->get()
            ->map(function ($follower) {
                return [
                    'id'     => $follower->id,
                    'name'   => $follower->f_name . ' ' . $follower->l_name,
                    'avatar' => $follower->avatar,
                ];
            });

        return $this->success([
            'count'     => $user->followers()->count(),
            'followers' => $followers,
        ], 'Followers retrieved successfully.');
    }

    // Get followings of authenticated user
    public function getFollowings()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $followings = $user->followings()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->get()
            ->map(function ($following) {
                return [
                    'id'     => $following->id,
                    'name'   => $following->f_name . ' ' . $following->l_name,
                    'avatar' => $following->avatar,
                ];
            });

        return $this->success([
            'count'      => $user->followings()->count(),
            'followings' => $followings,
        ], 'Followings retrieved successfully.');
    }

    // Get followings of any user
    public function getUserFollowings($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $followings = $user->followings()
            ->select('users.id', 'f_name', 'l_name', 'avatar')
            ->get()
            ->map(function ($following) {
                return [
                    'id'     => $following->id,
                    'name'   => $following->f_name . ' ' . $following->l_name,
                    'avatar' => $following->avatar,
                ];
            });

        return $this->success([
            'count'      => $user->followings()->count(),
            'followings' => $followings,
        ], 'Followings retrieved successfully.');
    }

    //Get auth user friend
    public function getFriends()
    {
        $authUser = auth('api')->user();

        // Get all user IDs the auth user is following
        $followingIds = $authUser->followings()->pluck('users.id')->toArray();

        // Get all user IDs who follow the auth user
        $followerIds = $authUser->followers()->pluck('users.id')->toArray();

        // Find mutuals (intersection)
        $friendIds = array_intersect($followingIds, $followerIds);

        // Retrieve friend data
        $friends = User::whereIn('id', $friendIds)
            ->select('id', 'f_name', 'l_name', 'avatar')
            ->get()
            ->map(function ($friend) {
                return [
                    'id'     => $friend->id,
                    'name'   => $friend->f_name . ' ' . $friend->l_name,
                    'avatar' => $friend->avatar,
                ];
            });

        return $this->success([
            'count'   => count($friendIds),
            'friends' => $friends,
        ], 'Friends retrieved successfully.');
    }
}
