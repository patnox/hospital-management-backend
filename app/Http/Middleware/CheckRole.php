<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        Log::debug('hospitalsystem: CheckRoles: ' . json_encode($roles));
        // if (!$request->user()->hasAnyRole($roles)) {
        if (!Auth::guard('sanctum')->user()->hasAnyRole($roles)) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}