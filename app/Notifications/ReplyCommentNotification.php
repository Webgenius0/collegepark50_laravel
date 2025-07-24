<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReplyCommentNotification extends Notification
{
    use Queueable;

    protected $replyUser;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($replyUser, $comment)
    {


        $this->replyUser = $replyUser;
        $this->comment = $comment;
    }


    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'reply_user_id' => $this->replyUser->id,
            'reply_user_name' => $this->replyUser->name,
            'reply_user_avatar' => $this->replyUser->avatar,
            'message' => "{$this->replyUser->name} answered to your comment on the minimul",
            'comment_id' => $this->comment->id,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
