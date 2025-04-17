<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class HandleExpiredTokens
{
    public function handle(Request $request, Closure $next)
    {
        Log::debug('hospitalsystem: handle.expired.tokens middleware entered');
        Log::debug('hospitalsystem: Token: ' . ($request->bearerToken() ?? 'No token'));
        Log::debug('hospitalsystem: Auth check: ' . (Auth::guard('sanctum')->check() ? 'authenticated' : 'not authenticated'));
        
        $error_response = response()->json([
                                'message' => 'Token is empty or has expired',
                                'error' => 'token_null_or_expired'
                            ], 401);

        if ($request->bearerToken()) {
            if (!Auth::guard('sanctum')->check()) {
                Log::debug('hospitalsystem: ERROR: found no token or expired token');
                return $error_response;
            }
        } else {
            Log::debug('hospitalsystem: ERROR: found no token or expired token');
            return $error_response;
        }

        Log::debug('hospitalsystem: Token is valid, proceeding with authentication');
        Log::debug('hospitalsystem: User role: ' . Auth::guard('sanctum')->user()->role);

        return $next($request);
    }
}