<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\CmsManageRequest;

class EventController extends Controller
{
    /**
     * show event page hero section
     */
    public function showEventHeroSection()
    {
        $data = CMS::where('page', 'event-page')->where('section', 'hero')->first();
        return view("backend.layouts.cms.event-page.event-hero", compact("data"));
    }

    /**
     * update event hero section
     **/
    public function updateEventHeroSection(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'event-page')
                ->where('section', 'hero')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'event-page',
                    'section' => 'hero',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Hero content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    /**
     * show event page hero section
     */
    public function showEventDetailsHeroSection()
    {
        $data = CMS::where('page', 'event-details-page')->where('section', 'hero')->first();
        return view("backend.layouts.cms.event-page.event-details-hero", compact("data"));
    }

    /**
     * update event detals hero section
     **/
    public function updateEventDetailsHeroSection(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'event-details-page')
                ->where('section', 'hero')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'event-details-page',
                    'section' => 'hero',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Hero content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
