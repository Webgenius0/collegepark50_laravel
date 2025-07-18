<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReplay extends Model
{
    protected $fillable = ['post_comment_id', 'user_id', 'reply'];

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'post_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
