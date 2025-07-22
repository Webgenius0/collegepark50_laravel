<?php

namespace App\Http\Controllers\Api\React\CMS;

use App\Models\CMS;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsletterController extends Controller
{
    use ApiResponse;

    //get feature page data
    public function index()
    {
        $newsletter = CMS::where('page', 'newsletter-page')->where('section', 'newsletter')->first();

        $data = [
            'newsletter' => $newsletter,
        ];

        return $this->success($data, 'Newsletter page data retrieved successfully.', 200);
    }
}
