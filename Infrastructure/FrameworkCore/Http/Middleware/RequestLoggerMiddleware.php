<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerMiddleware
{
    protected string $channel;
    protected bool $enabled;
    protected array $excludePaths;

    public function __construct()
    {
        $config = config('boundly.logging.request_logger', []);
        $this->enabled = $config['enabled'] ?? true;
        $this->channel = $config['channel'] ?? 'single';
        $this->excludePaths = $config['exclude_paths'] ?? ['health', 'up'];
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->enabled || $this->shouldExclude($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $requestId = $request->header('X-Request-ID', uniqid('req_'));
        
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        $this->logRequest($request, $response, $requestId, $duration);

        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');

        return $response;
    }

    protected function shouldExclude(Request $request): bool
    {
        foreach ($this->excludePaths as $path) {
            if ($request->is($path) || $request->is("api/{$path}")) {
                return true;
            }
        }

        return false;
    }

    protected function logRequest(Request $request, Response $response, string $requestId, float $duration): void
    {
        $logData = [
            'request_id' => $requestId,
            'app' => config('app.name', 'BOUNDLY'),
            'timestamp' => now()->toIso8601String(),
            'request' => [
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'content_type' => $request->getContentTypeFormat(),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'duration_ms' => round($duration, 2),
            ],
        ];

        $user = $request->user();
        if ($user) {
            $logData['user'] = [
                'id' => $user->getAuthIdentifier(),
                'type' => get_class($user),
            ];
        }

        $level = $this->getLogLevel($response->getStatusCode());
        Log::channel($this->channel)->log($level, "HTTP {$request->method()} {$request->path()}", $logData);
    }

    protected function getLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info',
        };
    }
}
