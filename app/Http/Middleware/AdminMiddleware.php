<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has admin role
        if (Auth::user()->role !== 'admin') {
            // For AJAX/API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Akses ditolak. Fitur ini hanya untuk Administrator.'
                ], 403);
            }

            // For regular web requests
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Fitur ini hanya untuk Administrator.');
        }

        return $next($request);
    }
}