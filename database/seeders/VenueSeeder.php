<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userIds = User::pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            Venue::create([
                'user_id'            => $userIds->random(),
                'title'              => fake()->company(),
                'capacity'           => rand(50, 1000),
                'location'           => fake()->address(),
                'latitude'           => fake()->latitude(),
                'longitude'          => fake()->longitude(),
                'service_start_time' => fake()->time('H:i:s'),
                'service_end_time'   => fake()->time('H:i:s'),
                'ticket_price'       => fake()->randomFloat(2, 5, 200),
                'phone'              => fake()->phoneNumber(),
                'email'              => fake()->safeEmail(),
                'status'             => rand(0, 1),
            ]);
        }
    }
}
