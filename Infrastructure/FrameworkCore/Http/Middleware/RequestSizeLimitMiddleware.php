<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestSizeLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $securityConfig = config('boundly.security', []);
        $maxSize = $securityConfig['max_request_size'] ?? '1M';

        $contentLengthHeader = $request->header('Content-Length', '0');
        $contentLength = is_numeric($contentLengthHeader) ? (int) $contentLengthHeader : 0;
        $maxBytes = $this->parseSize($maxSize);

        if ($contentLength > $maxBytes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request payload too large.',
                'error' => [
                    'max_size' => $maxSize,
                    'received_size' => $this->formatBytes($contentLength),
                ],
            ], 413);
        }

        return $next($request);
    }

    protected function parseSize(string $size): int
    {
        $units = [
            'B' => 1,
            'K' => 1024,
            'KB' => 1024,
            'M' => 1024 * 1024,
            'MB' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
        ];

        $size = trim($size);
        $lastChar = strtoupper(substr($size, -2));
        $number = (int) $size;

        if (isset($units[$lastChar])) {
            return (int) ($number * $units[$lastChar]);
        }

        $lastChar = strtoupper(substr($size, -1));
        if (isset($units[$lastChar])) {
            return (int) ($number * $units[$lastChar]);
        }

        return (int) $size;
    }

    protected function formatBytes(int|float $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
