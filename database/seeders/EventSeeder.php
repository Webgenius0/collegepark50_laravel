<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userIds  = User::pluck('id');
        $venueIds = Venue::pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('Seed users and venues first.');
            return;
        }

        $statuses = ['going_live', 'pending', 'postponed', 'cancelled', 'completed'];
        $timeZones = ['UTC', 'Asia/Dhaka', 'America/New_York', 'Europe/London'];

        for ($i = 0; $i < 20; $i++) {
            $startDate = fake()->dateTimeBetween('now', '+1 month');
            $endDate   = (clone $startDate)->modify('+1 day');

            Event::create([
                'venue_id'         => $venueIds->isNotEmpty() && rand(0, 1) ? $venueIds->random() : null,
                'user_id'          => $userIds->random(),
                'title'            => fake()->sentence(3),
                'description'      => fake()->paragraphs(rand(1, 3), true),
                'start_date'       => $startDate->format('Y-m-d'),
                'start_time'       => fake()->time('H:i:s'),
                'end_date'         => $endDate->format('Y-m-d'),
                'end_time'         => fake()->time('H:i:s'),
                'time_zone'        => collect($timeZones)->random(),
                'all_day_event'    => rand(0, 1),
                'banner'           => null, // or use a placeholder image if needed
                'tags'             => json_encode(fake()->words(rand(2, 5))),
                'status'           => collect($statuses)->random(),
                'like_count'       => rand(0, 100),
                'comment_count'    => rand(0, 50),
                'share_count'      => rand(0, 30),
            ]);
        }
    }
}
