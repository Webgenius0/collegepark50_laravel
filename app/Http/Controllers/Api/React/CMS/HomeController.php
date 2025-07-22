<?php

namespace App\Http\Controllers\Api\React\CMS;

use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    use ApiResponse;
    //get home-page data
    public function index()
    {
        $hero = CMS::where('page', 'home-page')->where('section', 'hero')->first();
        $event = CMS::where('page', 'home-page')->where('section', 'upcoming-event')->first();
        $venues = CMS::where('page', 'home-page')->where('section', 'popular-vanue')->first();
        $appDownload = CMS::where('page', 'home-page')->where('section', 'app-download')->first();

        $data = [
            'hero' => $hero,
            'upcoming_event' => $event,
            'popular_venues' => $venues,
            'app_download' => $appDownload,
        ];

        return $this->success($data, 'Home page data retrieved successfully.', 200);
    }
}
