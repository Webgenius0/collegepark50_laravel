<?php

namespace Database\Seeders;

use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Models\CMS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CMSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old data
        CMS::truncate();


        $data = [
            // Home Page - Hero Section
            [
                'page'   => PageEnum::HOME_PAGE->value,
                'section'     => SectionEnum::HERO->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Discover & Connect with Events, DJs & Venues',
                'sub_title' => null,
                'description' => 'Find events follow top DJs, connect with venus, and sync your calendar - everything you need in one place to stay in the groove and never miss a beat.',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Home Page - upcoming event section
            [
                'page'   => PageEnum::HOME_PAGE->value,
                'section'     => SectionEnum::UPCOMING_EVENT->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Explore Upcoming Events & Festivals',
                'sub_title' => null,
                'description' => 'Musical show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pusher pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Home Page - popular-vanue section
            [
                'page'   => PageEnum::HOME_PAGE->value,
                'section'     => SectionEnum::POPULAR_VANUE->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Popular Venues in Your Area',
                'sub_title' => null,
                'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate asperiores delectus consequuntur voluptatem cupiditate aliquam cum qui. Provident sequi sapiente, veritatis magni cum assumenda voluptatem',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Home Page - app download section
            [
                'page'   => PageEnum::HOME_PAGE->value,
                'section'     => SectionEnum::APP_DOWNLOAD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Your Event Experience Starts Here - Download the App',
                'sub_title' => null,
                'description' => 'Find events follow top DJs, connect with venus, and sync your calendar - everything you need in one place to stay in the groove and never miss a beat.',
                'sub_description' => null,
                'bg' => null,
                'image' => "hello.png",
                'btn_text'    => "Download",
                'btn_link'    => '#',
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],



            // Event Page - hero section
            [
                'page'   => PageEnum::EVENT_PAGE->value,
                'section'     => SectionEnum::HERO->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Upcoming all events',
                'sub_title' => null,
                'description' => 'Muscal show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pursue pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Event Page - upcoming event section
            [
                'page'   => PageEnum::EVENT_PAGE->value,
                'section'     => SectionEnum::UPCOMING_EVENT->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Upcoming Events & Festivals',
                'sub_title' => null,
                'description' => 'Muscal show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pursue pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Event Details Page - upcoming event section
            [
                'page'   => PageEnum::EVENT_DETAILS_PAGE->value,
                'section'     => SectionEnum::HERO->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Event Details',
                'sub_title' => null,
                'description' => 'Muscal show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pursue pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],


            // Feature Page - hero section
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::HERO->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Features',
                'sub_title' => null,
                'description' => 'Muscal show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pursue pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],

            // Feature Page - item section - text
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::TEXT->value,
                'name' => null,
                'slug' => null,
                'title'       => 'All-in-one platform for everything you need',
                'sub_title' => null,
                'description' => 'Muscal show organized world wide, you can join this musical show very easily through his site and confirm your ticket with a click pursue pleasure',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],


            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Event discovery',
                'sub_title' => null,
                'description' => 'Find local enents tailored to your interests, your friends activity, and the artists you follow',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Smart Calender',
                'sub_title' => null,
                'description' => 'Keep track of your schedule and view events your interests in or planing to attend.',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Artist connections',
                'sub_title' => null,
                'description' => 'Stay in the loop by following your favorite DJs, astists, and promoters for the latest event updates',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Venue Exploration',
                'sub_title' => null,
                'description' => 'Discover the best venues and explore upcoming events-all in one convenient place.',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Social Networking',
                'sub_title' => null,
                'description' => 'Stay connected with friends and check out the evetns their planings to attends',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
            // Feature Page - item section - card
            [
                'page'   => PageEnum::FEATURES_PAGE->value,
                'section'     => SectionEnum::CARD->value,
                'name' => null,
                'slug' => null,
                'title'       => 'Analytics Dashboard',
                'sub_title' => null,
                'description' => 'Gain valuable insights into your events, audience behavior, and promotional impact',
                'sub_description' => null,
                'bg' => null,
                'image' => null,
                'btn_text'    => null,
                'btn_link'    => null,
                'btn_color'    => null,
                'metadata'    => null,
                'status'      => 'active',
            ],
        ];

        // Validate the number of columns in each row
        $expectedColumns = 15; // page, section, name, slug, sub_title, description, sub_description, bg, image, btn_text, btn_link, btn_color, metadata, status
        foreach ($data as $index => $row) {
            if (count($row) !== $expectedColumns) {
                throw new \Exception("Row " . ($index + 1) . " has " . count($row) . " values, expected $expectedColumns.");
            }
        }

        CMS::insert($data);
    }
}
