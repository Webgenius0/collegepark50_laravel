<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VenueListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $venues = Venue::with('user')->get();

            return DataTables::of($venues)
                ->addIndexColumn()
                // ->addColumn('title', fn($item) => $item->title)
                ->addColumn('title', function ($item) {
                    return strlen($item->title) > 15 ? substr($item->title, 0, 15) . '...' : $item->title;
                })
                ->addColumn('capacity', fn($item) => $item->capacity)
                ->addColumn('location', function ($item) {
                    return strlen($item->location) > 20 ? substr($item->location, 0, 20) . '...' : $item->location;
                })

                ->addColumn('service_start_time', fn($item) => optional($item->service_start_time)->format('h:i A'))
                ->addColumn('service_end_time', fn($item) => optional($item->service_end_time)->format('h:i A'))


                ->addColumn('ticket_price', fn($item) => $item->ticket_price)
                ->addColumn('phone', fn($item) => $item->phone ?? '---')
                ->addColumn('email', fn($item) => $item->email ?? '---')

                ->addColumn('user', fn($item) => $item->user->f_name . ' ' . $item->user->l_name)

                ->addColumn('status', function ($item) {
                    $statusMap = [
                        0 => ['label' => 'Inactive', 'class' => 'secondary'],
                        1 => ['label' => 'Active', 'class' => 'success'],
                    ];

                    $statusInfo = $statusMap[$item->status] ?? ['label' => 'Unknown', 'class' => 'dark'];
                    return '<span class="badge bg-' . $statusInfo['class'] . '">' . $statusInfo['label'] . '</span>';
                })


                ->rawColumns(['title', 'status'])
                ->make();
        }

        return view("backend.layouts.venue.index");
    }
}
