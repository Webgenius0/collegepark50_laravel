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
        'remember_token'
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
        return trim("{$this->f_name} {$this->l_name}");
    }

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
