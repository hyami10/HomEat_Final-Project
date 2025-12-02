<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => config('security.frame_options'),
            'X-XSS-Protection' => config('security.xss_protection'),
            'X-Content-Type-Options' => config('security.content_type_options'),
            'Referrer-Policy' => config('security.referrer_policy'),
            'Permissions-Policy' => config('security.permissions_policy'),
            // CSP is now handled by CspNonceMiddleware with dynamic nonce
            // 'Content-Security-Policy' => config('security.content_security_policy'),
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains', // HSTS
            // Cache control headers to prevent cache poisoning and cache deception
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        foreach ($headers as $header => $value) {
            if (filled($value)) {
                if (!$response->headers->has($header)) {
                    $response->headers->set($header, $value);
                }
            }
        }

        return $response;
    }
}
