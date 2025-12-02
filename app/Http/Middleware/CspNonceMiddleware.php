<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class CspNonceMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        
        app()->instance('csp-nonce', $nonce);
        
        view()->share('cspNonce', $nonce);
        
        Vite::useCspNonce($nonce);
        
        $response = $next($request);
        
        if ($this->isHtmlResponse($response)) {
            $this->addCspHeader($response, $nonce);
        }
        
        return $response;
    }
    
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }
    
    protected function addCspHeader(Response $response, string $nonce): void
    {
        $isLocal = app()->environment('local');
        $isDebug = config('app.debug', false);
        $isHttps = request()->secure();
        
        $isDev = $isLocal || $isDebug;
        
        $extraHosts = array_filter(array_map('trim', explode(',', env('SECURITY_CSP_EXTRA_HOSTS', ''))));
        
        if (empty($extraHosts) && $isDev) {
            $extraHosts = ['http://localhost:5173', 'http://127.0.0.1:5173'];
        }
        
        $scriptSrc = ["'self'", "'nonce-{$nonce}'"];
        
        if ($isDev) {
            $scriptSrc[] = "'unsafe-inline'";
        }
        
        $styleSrc = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net', 'https://fonts.googleapis.com'];
        
        $connectSrc = ["'self'"];
        
        foreach ($extraHosts as $host) {
            $scriptSrc[] = $host;
            $styleSrc[] = $host;
            $connectSrc[] = $host;
    
            if (str_starts_with($host, 'http://')) {
                $connectSrc[] = preg_replace('/^http/', 'ws', $host, 1);
            } elseif (str_starts_with($host, 'https://')) {
                $connectSrc[] = preg_replace('/^https/', 'wss', $host, 1);
            }
        }
        
        $directives = [
            'default-src' => ["'self'"],
            'script-src' => $scriptSrc,
            'style-src' => $styleSrc,
            'img-src' => ["'self'", 'data:', 'blob:'],
            'font-src' => ["'self'", 'https://fonts.bunny.net', 'https://fonts.googleapis.com', 'https://fonts.gstatic.com', 'data:'],
            'connect-src' => $connectSrc,
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
        ];
        
        if ($isHttps && !$isDev) {
            $directives['upgrade-insecure-requests'] = [];
        }
        
        $parts = [];
        foreach ($directives as $directive => $sources) {
            $uniqueSources = array_values(array_unique(array_filter($sources)));
            if (empty($uniqueSources)) {
                $parts[] = $directive;
            } else {
                $parts[] = $directive . ' ' . implode(' ', $uniqueSources);
            }
        }
        
        $csp = implode('; ', $parts) . ';';
        
        $response->headers->set('Content-Security-Policy', $csp);
    }
}
