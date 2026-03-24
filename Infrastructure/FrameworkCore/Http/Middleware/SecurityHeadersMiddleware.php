<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $securityConfig = config('boundly.security', []);
        $headers = $this->getSecurityHeaders($securityConfig);

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    protected function getSecurityHeaders(array $config): array
    {
        $enabled = $config['headers'] ?? [];

        $headers = [];

        if (($enabled['x_frame_options'] ?? true)) {
            $headers['X-Frame-Options'] = 'DENY';
        }

        if (($enabled['x_content_type_options'] ?? true)) {
            $headers['X-Content-Type-Options'] = 'nosniff';
        }

        if (($enabled['x_xss_protection'] ?? true)) {
            $headers['X-XSS-Protection'] = '1; mode=block';
        }

        if (($enabled['strict_transport_security'] ?? true) && $this->isHttps()) {
            $hstsConfig = $config['hsts'] ?? [];
            $maxAge = $hstsConfig['max_age'] ?? 31536000;
            $includeSubdomains = ($hstsConfig['include_subdomains'] ?? true) ? '; includeSubDomains' : '';
            $preload = ($hstsConfig['preload'] ?? false) ? '; preload' : '';

            $headers['Strict-Transport-Security'] = "max-age={$maxAge}{$includeSubdomains}{$preload}";
        }

        if (($enabled['referrer_policy'] ?? true)) {
            $headers['Referrer-Policy'] = $config['referrer_policy'] ?? 'strict-origin-when-cross-origin';
        }

        if (($enabled['permissions_policy'] ?? true)) {
            $headers['Permissions-Policy'] = 'geolocation=(), camera=(), microphone=(), payment=()';
        }

        if (($enabled['content_security_policy'] ?? false)) {
            $csp = $this->buildContentSecurityPolicy($config['csp'] ?? []);
            if ($csp) {
                $headers['Content-Security-Policy'] = $csp;
            }
        }

        return $headers;
    }

    protected function buildContentSecurityPolicy(array $cspConfig): string
    {
        $directives = [];

        $directives[] = "default-src 'self'";
        $directives[] = "script-src 'self'";
        if ($cspConfig['allow_inline_styles'] ?? false) {
            $directives[] = "style-src 'self' 'unsafe-inline'";
        }
        $directives[] = "img-src 'self' data:";
        $directives[] = "font-src 'self'";
        $directives[] = "connect-src 'self'";
        $directives[] = "frame-ancestors 'none'";
        $directives[] = "form-action 'self'";
        $directives[] = "base-uri 'self'";
        $directives[] = "object-src 'none'";

        if (! empty($cspConfig['allowed_domains'])) {
            foreach ($cspConfig['allowed_domains'] as $domain) {
                $directives[] = "script-src 'self' {$domain}";
                $directives[] = "img-src 'self' {$domain}";
            }
        }

        return implode('; ', $directives);
    }

    protected function isHttps(): bool
    {
        if (config('boundly.security.force_https', false)) {
            return true;
        }

        return request()->secure() || request()->header('X-Forwarded-Proto') === 'https';
    }
}
