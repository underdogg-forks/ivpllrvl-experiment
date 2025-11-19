<?php

namespace Modules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure user is authenticated before accessing protected routes.
 *
 * Implements early return pattern for better code readability and performance.
 */
class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Early return if user is not authenticated
        if (!session()->has('user_id')) {
            return redirect()->route('sessions.login')
                ->with('alert_error', trans('auth.unauthenticated'));
        }

        return $next($request);
    }
}
