<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifiedEmailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (is_null(request()->user()->email_verified_at)) {
            return response()->json([
                'message' => 'The email has not been verified yet.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
