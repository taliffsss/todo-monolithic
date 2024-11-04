<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HTTP_RESPONSE;
use Symfony\Component\HttpFoundation\Response;

class PreventGuestActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_guest) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Guest users cannot perform this action.',
                ], HTTP_RESPONSE::HTTP_UNAUTHORIZED);
            }

            return redirect()->back()->with('error', 'Guest users cannot perform this action.');
        }

        return $next($request);
    }
}
