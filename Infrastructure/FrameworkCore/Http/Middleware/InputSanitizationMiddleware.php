<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Services\InputSanitizer;
use Infrastructure\FrameworkCore\Services\SecurityLogger;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    protected InputSanitizer $sanitizer;
    protected SecurityLogger $logger;

    public function __construct(InputSanitizer $sanitizer, SecurityLogger $logger)
    {
        $this->sanitizer = $sanitizer;
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->sanitizer->isEnabled()) {
            return $next($request);
        }

        $this->sanitizeRequest($request);

        $response = $next($request);

        return $this->addSanitizationHeaders($response);
    }

    protected function sanitizeRequest(Request $request): void
    {
        $this->sanitizeQueryParameters($request);
        $this->sanitizeRequestPayload($request);
    }

    protected function sanitizeQueryParameters(Request $request): void
    {
        $query = $request->query();
        $sanitized = $this->sanitizer->sanitizeArray($query);

        foreach ($sanitized as $key => $value) {
            $request->query->set($key, $value);
        }
    }

    protected function sanitizeRequestPayload(Request $request): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        $input = $request->all();
        $sanitized = $this->sanitizeInputRecursive($input);

        $request->merge($sanitized);
        $request->replace($sanitized);
    }

    protected function sanitizeInputRecursive(array $input): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeInputRecursive($value);
            } elseif (is_string($value)) {
                $result[$key] = $this->sanitizeAndLog($key, $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function sanitizeAndLog(string $key, string $value): string
    {
        if ($this->sanitizer->detectSuspiciousInput($value)) {
            $details = $this->sanitizer->getSuspiciousDetails($value);

            $this->logger->logSuspiciousInput(null, null, [
                'field' => $key,
                'detected' => $details,
                'masked_value' => $this->maskValue($value),
            ]);
        }

        return $this->sanitizer->sanitize($value);
    }

    protected function maskValue(string $value): string
    {
        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . str_repeat('*', strlen($value) - 8) . substr($value, -4);
    }

    protected function addSanitizationHeaders(Response $response): Response
    {
        $response->headers->set('X-Content-Sanitized', 'true');

        return $response;
    }

    public function getSanitizer(): InputSanitizer
    {
        return $this->sanitizer;
    }
}
