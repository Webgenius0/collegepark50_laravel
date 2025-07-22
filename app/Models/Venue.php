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

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasOne(VenueDetail::class);
    }

    public function media()
    {
        return $this->hasMany(VenueMedia::class);
    }
}
