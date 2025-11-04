<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware.
 *
 * Adds security headers to all HTTP responses to protect against common web vulnerabilities.
 *
 * Headers Added:
 * - X-Frame-Options: Prevents clickjacking attacks
 * - X-Content-Type-Options: Prevents MIME-sniffing
 * - X-XSS-Protection: Enables browser XSS protection
 * - Referrer-Policy: Controls referrer information
 * - Content-Security-Policy: Restricts resource loading
 * - Permissions-Policy: Controls browser features
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking by disallowing the page to be framed
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable browser XSS protection (legacy, but still good to have)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information sent to other sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy - restrictive but allows inline scripts for now
        // TODO: Move inline scripts to external files and remove 'unsafe-inline'
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",  // TODO: Remove unsafe-inline/eval
            "style-src 'self' 'unsafe-inline'",  // TODO: Remove unsafe-inline
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy - disable potentially dangerous features
        $permissionsPolicy = implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]);
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Force HTTPS in production (only if not in local development)
        if ( ! app()->environment('local')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
