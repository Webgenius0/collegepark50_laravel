<?php

namespace App\Http\Controllers\Api\React\CMS;

use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FeatureController extends Controller
{
    use ApiResponse;

    //get feature page data
    public function index()
    {
        $features_hero = CMS::where('page', 'features-page')->where('section', 'hero')->first();
        $features_item_hero = CMS::where('page', 'features-page')->where('section', 'text')->first();
        $features_item = CMS::where('page', 'features-page')->where('section', 'card')->get();

        $data = [
            'features_hero' => $features_hero,
            'features_item_hero' => $features_item_hero,
            'features_item'=> $features_item
        ];

        return $this->success($data, 'Feature page data retrieved successfully.', 200);
    }
}


