<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'capacity',
        'location',
        'latitude',
        'longitude',
        'service_start_time',
        'service_end_time',
        'ticket_price',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'service_start_time' => 'datetime:H:i:s',
        'service_end_time' => 'datetime:H:i:s',
    ];


    // Relationships
    //user table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //venue-details table
    public function detail()
    {
        return $this->hasOne(VenueDetail::class);
    }

    // veune media table
    public function media()
    {
        return $this->hasMany(VenueMedia::class);
    }

    //venue review table
    public function reviews()
    {
        return $this->hasMany(VenueReview::class);
    }
}
