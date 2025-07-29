<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, Billable;

    protected $guard_name = ['web' , 'api'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'password',
        'otp',
        'is_otp_verified',
        'otp_expires_at',
        'email_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'role',
        'profession',
        'gender',
        'age',
        'avatar',
        'address',
        'country',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'get_notification',
        'remember_token',
        'last_activity_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'otp',
        'reset_password_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'              => 'datetime',
            'otp_expires_at'                  => 'datetime',
            'reset_password_token_expire_at' => 'datetime',
            'is_otp_verified'                => 'boolean',
            'is_google_signin'               => 'boolean',
        ];
    }

    protected $appends = [
        'role' ,
        'is_online'
    ];


    public function getIsOnlineAttribute()
    {
        return $this->last_activity_at > now()->subMinutes(5);
    }



    public function senders()
    {
        return $this->hasMany(Chat::class , 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class , 'receiver_id');
    }

    public function roomsAsUserOne()
    {
        return $this->hasMany(Room::class , 'user_one_id');
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class , 'user_two_id');
    }

    public function allRooms()
    {
        return Room::where('user_one_id' , $this->id)->orWhere('user_two_id' , $this->id);
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->f_name} {$this->l_name}");
    }



    //posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    //likes
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    //comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //follower
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'following_id', 'follower_id');
    }

    //following
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'follower_id', 'following_id');
    }


    //venuse table relation
    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    //events table relation
    public function events()
    {
        return $this->hasMany(Venue::class);
    }
}
