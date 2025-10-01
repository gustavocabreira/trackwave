<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedEmailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (is_null(request()->user()->email_verified_at)) {
            return response()->json([
                'message' => 'The email has not been verified yet.',
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        return $next($request);
    }
}
