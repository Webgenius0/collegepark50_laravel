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
        'avatar',

        'otp',
        'is_otp_verified',
        'otp_expires_at',
        'email_verified_at',

        'reset_password_token',
        'reset_password_token_expire_at',

        'role',
        'provider',
        'provider_id',
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

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    //chat model relation
    // public function senders()
    // {
    //     return $this->hasMany(Chat::class, 'sender_id');
    // }

    // public function receivers()
    // {
    //     return $this->hasMany(Chat::class, 'receiver_id');
    // }

    // public function roomsAsUserOne()
    // {
    //     return $this->hasMany(Room::class, 'first_user_id');
    // }

    // public function roomsAsUserTwo()
    // {
    //     return $this->hasMany(Room::class, 'second_user_id');
    // }

    // public function allRooms()
    // {
    //     return Room::where('first_user_id', $this->id)->orWhere('second_user_id', $this->id);
    // }


    // relation user with post
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function shares()
    {
        return $this->hasMany(PostShare::class);
    }
}
