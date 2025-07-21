<?php

namespace App\Http\Controllers\Api\React\User;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\UserFollower;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        }

        return $this->success([], $message);
    }

    // Get followers of any user
    public function getFollowers($id)
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

    // Get followings of any user
    public function getFollowings($id)
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
}
