<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $events = Event::all();

        if ($events->isEmpty()) {
            $this->command->warn('Please seed events first.');
            return;
        }

        $capacityTypes = ['shared', 'individual', 'unlimited'];
        $attendeeOptions = ['none', 'optional', 'required'];

        foreach ($events as $event) {
            $ticketCount = rand(1, 3);

            for ($i = 0; $i < $ticketCount; $i++) {
                $startDate = fake()->dateTimeBetween('-1 week', '+1 week');
                $endDate   = (clone $startDate)->modify('+' . rand(1, 7) . ' days');

                $capacityType = collect($capacityTypes)->random();

                Ticket::create([
                    'event_id'             => $event->id,
                    'ticket_name'          => fake()->words(2, true), // e.g., "VIP Access"
                    'description'          => fake()->sentence(),
                    'show_description'     => rand(0, 1),
                    'start_date'           => $startDate->format('Y-m-d'),
                    'start_time'           => fake()->time('H:i'),
                    'end_date'             => $endDate->format('Y-m-d'),
                    'end_time'             => fake()->time('H:i'),
                    'price'                => fake()->randomFloat(2, 0, 200),

                    'capacity_type'        => $capacityType,
                    'shared_capacity'      => $capacityType === 'shared'     ? rand(50, 500) : null,
                    'independent_capacity' => $capacityType === 'individual' ? rand(10, 100)  : null,

                    'external_ticket_url'  => rand(0, 1) ? fake()->url() : null,
                    'sku'                  => 'TCKT-' . strtoupper(fake()->bothify('??##??##')),
                    'attendee_collection'  => collect($attendeeOptions)->random(),
                ]);
            }
        }
    }
}
