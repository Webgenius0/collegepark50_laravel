<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\User;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::select('id', 'f_name', 'l_name', 'email', 'role', 'profession', 'address', 'country', 'city', 'created_at')
                ->where('role', '!=', 'admin');

            // Filter by role if selected
            if ($request->has('role') && $request->role !== 'all') {
                $query->where('role', $request->role);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', fn($row) => $row->f_name . ' ' . $row->l_name)
                ->addColumn('email', fn($row) => $row->email ?? '---')
                ->addColumn('role', function ($row) {
                    return '<span class=" text-capitalize">' . $row->role . '</span>';
                })
                ->addColumn('profession', fn($row) => $row->profession ?? '---')
                ->addColumn('address', fn($row) => $row->address ?? '---')
                ->addColumn('country', fn($row) => $row->country ?? '---')
                ->addColumn('city', fn($row) => $row->city ?? '---')
                ->addColumn('created_at', fn($row) => optional($row->created_at)->format('Y-m-d'))
                ->rawColumns(['role'])
                ->make(true);
        }

        return view("backend.layouts.user.index");
    }
}
