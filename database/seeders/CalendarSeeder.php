<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\Calendar;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $userIds = User::pluck('id');
            $eventIds = Event::pluck('id');

            if ($userIds->isEmpty()) {
                $this->command->warn('No users found. Please seed users first.');
                return;
            }

            $startDate = Carbon::create(2025, 7, 27); // July 27, 2025
            $endDate = Carbon::create(2025, 9, 30);   // End of September
            $totalEvents = 0;

            while ($totalEvents < 25 && $startDate <= $endDate) {
                $eventCount = rand(2, 5);

                for ($i = 0; $i < $eventCount && $totalEvents < 25; $i++) {
                    $hour = rand(8, 20); // between 8AM–8PM
                    $start = $startDate->copy()->setTime($hour, 0);
                    $end = $start->copy()->addHours(rand(1, 3));

                    Calendar::create([
                        'user_id'     => $userIds->random(),
                        'event_id'    => $eventIds->random(),
                        'title'       => 'Practice Event ' . Str::random(5),
                        'description' => 'Random generated event.',
                        'all_day'     => false,
                        'start_date'  => $start,
                        'end_date'    => $end,
                        'color_code'  => ['#FF5733', '#33FF57', '#3357FF', '#F1C40F'][rand(0, 3)],
                    ]);

                    $totalEvents++;
                }

                $startDate->addDay();
            }
        });
    }
}
