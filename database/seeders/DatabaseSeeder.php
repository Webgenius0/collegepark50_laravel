<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            HashtagSeeder::class,
            PostSeeder::class,
            EventSeeder::class,
            TicketSeeder::class,
            LikeSeeder::class,
            CommentSeeder::class,
            VenueSeeder::class,
            VenueDetailSeeder::class,
            VenueReviewSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
