<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueReview;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VenueReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $venues = Venue::all();
        $userIds = User::pluck('id');

        foreach ($venues as $venue) {
            // Each venue gets 3-5 reviews
            $reviewers = $userIds->random(rand(3, 5));

            foreach ($reviewers as $userId) {
                // Avoid duplicate review
                VenueReview::updateOrCreate(
                    ['venue_id' => $venue->id, 'user_id' => $userId],
                    [
                        'comment' => fake()->sentence(),
                        'rating'  => fake()->randomFloat(1, 3.0, 5.0),
                    ]
                );
            }
        }
    }
}
