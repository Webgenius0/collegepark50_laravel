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

        'name',

        'email',
        'password',
        'phone_number',
        'date_of_birth',
        'age',

        'avatar',
        'role',

        'otp',
        'is_otp_verified',
        'otp_expires_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'status',

        'provider',
        'provider_id',
        'is_agree_termsconditions',

        'is_social_logged',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'               => 'datetime',
            'otp_expires_at'                  => 'datetime',
            'is_otp_verified'                 => 'boolean',
            'reset_password_token_expires_at' => 'datetime',
            'password'                        => 'hashed',
        ];
    }

    public function getAvatarAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && ! empty($value)) {

            return url($value);
        }
        return $value;
    }

    // company
    public function company()
    {
        return $this->hasOne(Company::class, 'user_id', 'id');
    }

    // employee
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    //chat model relation
    public function senders()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function roomsAsUserOne()
    {
        return $this->hasMany(Room::class, 'first_user_id');
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class, 'second_user_id');
    }

    public function allRooms()
    {
        return Room::where('first_user_id', $this->id)->orWhere('second_user_id', $this->id);
    }

}
