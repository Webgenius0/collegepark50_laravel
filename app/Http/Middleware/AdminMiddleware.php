<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

       if (Auth::check('web') && Auth::user('web')->role === 'admin') {
            return $next($request);
        }

       return response()->json(['status' => false, 'message' => 'Unauthorized access.Role should be admin.', 'code' => 403], 403);
    }
}
