<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VenueDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'description',
        'features', // stored as JSON
    ];

    protected $casts = [
        'features' => 'array',
    ];

    // Relationship

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
