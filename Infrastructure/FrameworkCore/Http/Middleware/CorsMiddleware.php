<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $corsConfig = config('boundly.cors', []);

        if (! ($corsConfig['enabled'] ?? false)) {
            return $next($request);
        }

        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflight($request, $corsConfig);
        }

        $response = $next($request);

        return $this->addCorsHeaders($response, $request, $corsConfig);
    }

    protected function handlePreflight(Request $request, array $config): Response
    {
        $response = response('', 200);

        return $this->addCorsHeaders($response, $request, $config);
    }

    protected function addCorsHeaders(Response $response, Request $request, array $config): Response
    {
        $origin = $request->header('Origin');

        if ($this->isOriginAllowed($origin, $config)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', $config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])
        );

        $response->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', $config['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With'])
        );

        $response->headers->set(
            'Access-Control-Max-Age',
            (string) ($config['max_age'] ?? 3600)
        );

        if ($config['supports_credentials'] ?? false) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $exposedHeaders = $config['exposed_headers'] ?? [];
        if (! empty($exposedHeaders)) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
        }

        return $response;
    }

    protected function isOriginAllowed(?string $origin, array $config): bool
    {
        if (empty($origin)) {
            return false;
        }

        $allowedOrigins = $config['allowed_origins'] ?? ['*'];

        if (in_array('*', $allowedOrigins)) {
            return true;
        }

        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        $patterns = $config['allowed_origins_patterns'] ?? [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}
