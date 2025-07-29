<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventListController extends Controller
{
    public function index(Request $request){
        return Event::get();
    }
}
