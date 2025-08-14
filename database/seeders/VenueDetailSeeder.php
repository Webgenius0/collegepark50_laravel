<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\VenueDetail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VenueDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $venues = Venue::all();

        foreach ($venues as $venue) {
            VenueDetail::create([
                'venue_id'   => $venue->id,
                'description' => fake()->paragraphs(rand(2, 4), true),
                'features'   => "Parking, WiFi, Air Conditioning, Sound System, Lighting, Stage, Outdoor Area",
            ]);
        }
    }
}
