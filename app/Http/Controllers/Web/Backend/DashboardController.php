<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\BmwComponentList;
use App\Models\BmwModelList;
use App\Models\BmwSeriesList;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Event;
use App\Models\JobCategory;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $totalVenues = Event::count();
        $totalPosts = Venue::count();
        return view('backend.layouts.dashboard', compact(
            'totalUsers',
            'totalEvents',
            'totalVenues',
            'totalPosts'
        ));
    }

    //dashboard data for charts
    public function getDashboardData()
    {
        //user role pie chart data
        $userRoles = User::selectRaw('role, count(*) as count')
            ->where('role', '!=', 'admin')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->role => $item->count];
            });

        //new user registeration data
        $newUserRegistrations = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        //return JSON response with user role data
        return response()->json([
            //user roles
            'user_roles' => [
                'user' => $userRoles['user'] ?? 0,
                'dj' => $userRoles['dj'] ?? 0,
                'venue' => $userRoles['venue'] ?? 0,
                'promoter' => $userRoles['promoter'] ?? 0,
                'artist' => $userRoles['artist'] ?? 0,
            ],

            //new user registrations
            'new_user_registrations' => $newUserRegistrations,
        ]);
    }

    /**
     * Display appointment statistics.
     */
    // public function paymentStats()
    // {
    //     // Pie Chart Data - Payment Status Distribution
    //     $paymentStatus = Payment::selectRaw('payment_status, count(*) as count')
    //         ->groupBy('payment_status')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return [$item->payment_status => $item->count];
    //         });

    //     // Line Chart Data - Payment Progress Over Time
    //     $paymentProgress = Payment::selectRaw('
    //         DATE(created_at) as date,
    //         SUM(CASE WHEN payment_status = "completed" THEN amount ELSE 0 END) as completed,
    //         SUM(CASE WHEN payment_status = "pending" THEN amount ELSE 0 END) as pending,
    //         SUM(CASE WHEN payment_status = "failed" THEN amount ELSE 0 END) as failed
    //     ')
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->get();

    //     // Order Pie Chart Data
    //     $orderStatus = Order::selectRaw('status, count(*) as count')
    //         ->groupBy('status')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return [$item->status => $item->count];
    //         });

    //     // Order Line Chart Data
    //     $orderProgress = Order::selectRaw('
    //         DATE(created_at) as date,
    //         SUM(price) as total_amount,
    //         COUNT(*) as order_count
    //     ')
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->get();

    //     // Appointment Pie Chart Data - Schedule Distribution
    //     $appointmentSchedule = Appointment::selectRaw('schedul, count(*) as count')
    //         ->groupBy('schedul')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return [$item->schedul => $item->count];
    //         });

    //     // Appointment Line Chart Data - Daily Appointments
    //     $appointmentTrends = Appointment::selectRaw('
    //         DATE(date) as date,
    //         COUNT(*) as appointment_count,
    //         GROUP_CONCAT(DISTINCT schedul) as schedules
    //     ')
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->get();


    //     // Return JSON response with both payment and order statistics
    //     return response()->json([
    //         //payment statistics
    //         'payment_pie_chart' => [
    //             'completed' => $paymentStatus['completed'] ?? 0,
    //             'pending' => $paymentStatus['pending'] ?? 0,
    //             'failed' => $paymentStatus['failed'] ?? 0,
    //         ],
    //         'payment_line_chart' => $paymentProgress,

    //         // appointment statistics
    //         'order_pie_chart' => [
    //             'pending' => $orderStatus['pending'] ?? 0,
    //             'delivered' => $orderStatus['delivered'] ?? 0,
    //             'accepted' => $orderStatus['accepted'] ?? 0,
    //             'rejected' => $orderStatus['rejected'] ?? 0,
    //         ],
    //         'order_line_chart' => $orderProgress,

    //         //appointment statistics
    //         'appointment_pie_chart' => [
    //             'morning' => $appointmentSchedule['morning'] ?? 0,
    //             'afternoon' => $appointmentSchedule['afternoon'] ?? 0,
    //             'evening' => $appointmentSchedule['evening'] ?? 0,
    //         ],
    //         'appointment_line_chart' => $appointmentTrends
    //     ]);
    // }
}
