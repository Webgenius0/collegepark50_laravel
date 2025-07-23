<?php

namespace Database\Seeders;

use App\Models\Hashtag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HashtagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tags = [
            'music',
            'party',
            'live',
            'djlife',
            'edm',
            'dancefloor',
            'vibes',
            'festival',
            'hiphop',
            'techno',
            'housemusic',
            'trending',
            'weekend',
            'nightlife',
            'event',
            'goodvibes',
            'fun',
            'crowd',
            'beats',
            'club',
            'performance',
            'artist',
            'band',
            'concert',
            'sound',
            'stage',
            'lights',
            'turnup',
            'mix',
            'bass',
            'drop',
            'chill',
            'playlist',
            'nowplaying',
            'instamusic',
            'energy',
            'banger',
            'throwback',
            'underground',
            'jam',
            'fire',
            'trap',
            'vibecheck',
            'latenight',
            'flow',
            'hype',
            'mood',
            'groove'
        ];

        $uniqueTags = collect($tags)->shuffle()->take(rand(30, 50));

        foreach ($uniqueTags as $tag) {
            Hashtag::create([
                'tag' => '#' . $tag,
            ]);
        }
    }
}
