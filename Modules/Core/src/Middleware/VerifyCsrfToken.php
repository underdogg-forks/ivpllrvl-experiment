<?php

namespace Modules\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to protect against CSRF attacks on state-changing operations.
 *
 * Verifies CSRF token for POST, PUT, PATCH, DELETE requests.
 * Implements early return pattern and DRY principle.
 */
class VerifyCsrfToken
{
    /**
     * URIs that should be excluded from CSRF verification.
     *
     * @var array<string>
     */
    protected array $except = [
        // Add any API endpoints or webhooks that need to be excluded
    ];

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
        // Early return for GET, HEAD, OPTIONS requests (safe methods)
        if ($this->isSafeMethod($request)) {
            return $next($request);
        }

        // Early return for excluded URIs
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        // Verify CSRF token
        if ( ! $this->tokensMatch($request)) {
            return $this->handleTokenMismatch($request);
        }

        return $next($request);
    }

    /**
     * Determine if the HTTP request uses a "safe" method.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isSafeMethod(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Determine if the request has a URI that should be excluded.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function tokensMatch(Request $request): bool
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        return is_string(session()->get('_token'))
            && is_string($token)
            && hash_equals(session()->get('_token'), $token);
    }

    /**
     * Handle token mismatch error.
     *
     * @param Request $request
     *
     * @return Response
     */
    protected function handleTokenMismatch(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'CSRF token mismatch.',
            ], 419);
        }

        return redirect()->back()
            ->with('alert_error', trans('auth.csrf_token_mismatch'))
            ->withInput();
    }
}
