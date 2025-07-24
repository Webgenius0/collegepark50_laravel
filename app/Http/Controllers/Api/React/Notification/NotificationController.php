<?php

namespace App\Http\Controllers\Api\React\Notification;

use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use ApiResponse;

    public function allNotifications()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Fetch all database notifications for the authenticated user
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->whereNull('read_at')->get();

            if ($notifications->isEmpty()) {
                return $this->success([], 'No notifications found.', 200);
            }

            $notifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans()
                ];
            });

            return $this->success($notifications, 'Notifications retrieved successfully.', 200);
        } catch (\Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function readNotification($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();

            if (!$notification) {
                return $this->error([], 'Notification not found.', 404);
            }

            $notification->markAsRead();

            return $this->success([], 'Notification marked as read successfully.', 200);
        } catch (\Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
